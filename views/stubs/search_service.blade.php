@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $namespaceApp }}Services;

use {{ $namespaceApp }}Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SearchService
{
    public function __construct(
        protected Builder|Model $baseQuery,
        protected Model|BaseModel $model,
    ) {}

    /**
     * Full-text search across indexed columns
     */
    public function advancedSearch(Request $request, array $fieldsFullText): self
    {
        $this->applyActiveFilter($request);
        $this->baseQuery->whereFullText($fieldsFullText, $request->get('search'));

        return $this;
    }

    /**
     * Structured field-by-field search with fluent pipeline
     *
     * @param array<string> $fieldSearchable
     */
    public function findAllFieldsAnd(Request $request, array $fieldSearchable = []): self
    {
        $this->applyActiveFilter($request)
            ->applyFieldSelection($request)
            ->applyDateSelection($request)
            ->applyRelations($request)
            ->applyHiddenRelations($request);

        $inputs = $request->only($fieldSearchable);

        foreach ($inputs as $field => $value) {
            $this->applyFieldFilter($field, $value);
        }

        return $this;
    }

    /**
     * Applies soft-delete filtering based on is_active parameter
     */
    public function applyActiveFilter(Request $request): self
    {
        if (! in_array(SoftDeletes::class, class_uses_recursive($this->baseQuery->getModel()))) {
            return $this;
        }

        $this->baseQuery->when(
            $request->exists('is_active'),
            fn (Builder $query) => $request->boolean('is_active')
                ? $query
                : $query->onlyTrashed(),
            fn (Builder $query) => $query->withTrashed(),
        );

        return $this;
    }

    /**
     * Select specific fields from the model, filtering invalid columns
     */
    protected function applyFieldSelection(Request $request): self
    {
        $fields = $request->query('fields');

        if (! $fields) {
            return $this;
        }

        $table = $this->model->getTable();
        $modelId = $this->model->getKeyName();
        $fillableFields = $this->model->getFillable();
        $requestedFields = explode(',', str_replace(' ', '', $fields));
        $validFields = array_intersect($requestedFields, $fillableFields);

        if ($validFields) {
            $relationFields = array_filter(
                $fillableFields,
                fn (string $field) => str_ends_with($field, '_id'),
            );

            $fieldsToSelect = array_unique(
                array_merge([$table . '.' . $modelId], $validFields, $relationFields),
            );

            $this->baseQuery->select($fieldsToSelect);
        }

        return $this;
    }

    /**
     * Apply date filters for created_at and updated_at with Carbon parsing
     */
    protected function applyDateSelection(Request $request): self
    {
        foreach (['created_at', 'updated_at'] as $field) {
            $this->applyDateFilter($request, $field);
        }

        return $this;
    }

    /**
     * Apply a date filter using Carbon for proper date handling
     */
    protected function applyDateFilter(Request $request, string $field): void
    {
        $start = $request->input("start_{$field}");
        $end = $request->input("end_{$field}");

        $this->baseQuery->when(
            $start && $end,
            fn (Builder $query) => $query->whereBetween($field, [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay(),
            ]),
            fn (Builder $query) => $query->when(
                $start,
                fn (Builder $q) => $q->whereDate($field, $start),
            ),
        );
    }

    /**
     * Apply whereHas filters for related models based on request parameters
     */
    protected function applyRelations(Request $request): self
    {
        $relations = collect($this->model->getRelationsBySearch())
            ->mapWithKeys(fn (string $relation) => [Str::snake($relation) => $relation]);

        collect($request->all())
            ->only($relations->keys()->all())
            ->each(fn (array $fields, string $relationKey) => $this->applyRelationFilter(
                $relations[$relationKey],
                $fields,
            ));

        return $this;
    }

    /**
     * Apply whereHas filter for a specific relation
     */
    protected function applyRelationFilter(string $relation, array $fields): void
    {
        /** @var BaseModel $relatedModel */
        $relatedModel = $this->model->{$relation}()->getRelated();

        $this->baseQuery->whereHas(
            $relation,
            fn (Builder $query) => $this->filterRelationFields($query, $fields, $relatedModel),
        );
    }

    /**
     * Filter fields within a relation using match for type-based logic
     */
    protected function filterRelationFields(Builder $query, array $fields, BaseModel|Model $relatedModel): void
    {
        foreach ($fields as $field => $value) {
            $type = $relatedModel::getFieldType($field);

            if (! $type) {
                continue;
            }

            match (true) {
                is_array($value)   => $query->whereIn($field, $value),
                $type === 'string' => $query->where($field, 'like', "%{$value}%"),
                default            => $query->where($field, '=', $value),
            };
        }
    }

    /**
     * Hide specific eager-loaded relations from the query
     */
    protected function applyHiddenRelations(Request $request): self
    {
        $hideRelation = $request->get('hide_relation');

        if (! $hideRelation) {
            return $this;
        }

        match ($hideRelation) {
            '*'     => $this->baseQuery->setEagerLoads([]),
            default => $this->baseQuery->without(
                array_map(fn (string $r) => Str::camel(trim($r)), explode(',', $hideRelation)),
            ),
        };

        return $this;
    }

    /**
     * Apply filter for a single field using match expression for type dispatch
     */
    protected function applyFieldFilter(string $field, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        $type = $this->model::getFieldType($field);

        if (! $type) {
            return;
        }

        match ($type) {
            'string' => $this->baseQuery->where($field, 'like', "%{$value}%"),
            'integer',
            'int',
            'decimal' => $this->applyRangeFilter($field, $value),
            'datetime',
            'date',
            'custom_datetime' => $this->applyRangeFilter($field, $value, isDate: true),
            default => $this->baseQuery->where($field, '=', $value),
        };
    }

    /**
     * Apply range filter (between) or exact match for numeric/date fields
     */
    protected function applyRangeFilter(string $field, mixed $value, bool $isDate = false): void
    {
        $parts = explode(':', (string) $value, 2);

        if (count($parts) !== 2) {
            $this->baseQuery->where($field, '=', $value);
            return;
        }

        [$start, $end] = $isDate
            ? [Carbon::parse($parts[0])->startOfDay(), Carbon::parse($parts[1])->endOfDay()]
            : $parts;

        $this->baseQuery->whereBetween($field, [$start, $end]);
    }

    /**
     * Finalizes the query and returns the result collection
     */
    public function get(): Collection
    {
        return $this->baseQuery->get();
    }
}

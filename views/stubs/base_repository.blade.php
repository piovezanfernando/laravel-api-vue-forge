@php
    echo "<?php".PHP_EOL;
@endphp

namespace App\Repositories;

use App\Models\BaseModel;
use Closure;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class BaseRepository extends ModelCreate
{
    /** @var array<int, string> fields that can be used in the search */
    protected array $fieldSearchable = [];

    /** @var array<int, string> fields that are part of the table's FullText index */
    protected array $fieldsFullText = [];

    /**
     * Get searchable fields array
     *
     * @return array<int, string>
     */
    abstract public function getFieldsSearchable(): array;

    // ──────────────────────────────────────────────────────────────
    //  READ
    // ──────────────────────────────────────────────────────────────

    /**
     * Find a single record by its primary key
     */
    public function find(int|string $id): Builder|Collection|BaseModel|null
    {
        return $this->newBaseQuery()->find($id);
    }

    /**
     * Add a where clause to a fresh query
     */
    public function findBy(
        array|string|Closure $column,
        mixed $value,
        string $operator = '=',
        string $boolean = 'and',
    ): Builder {
        return $this->newBaseQuery()->where($column, $operator, $value, $boolean);
    }

    /**
     * Retrieve all records with optional filter, skip and limit
     *
     * @param array<string, mixed> $search
     * @param array<string>        $columns
     */
    public function all(
        array $search = [],
        ?int $skip = null,
        ?int $limit = null,
        array $columns = ['*'],
    ): Collection {
        return $this->allQuery($search, $skip, $limit)->get($columns);
    }

    /**
     * Build a filtered query using ->when() for cleaner conditionals
     *
     * @param array<string, mixed> $search
     */
    public function allQuery(array $search = [], ?int $skip = null, ?int $limit = null): Builder
    {
        $query = $this->newBaseQuery();

        collect($search)
            ->filter(fn (mixed $value, string $key) => in_array($key, $this->getFieldsSearchable()))
            ->each(fn (mixed $value, string $key) => $query->where($key, $value));

        return $query
            ->when($skip, fn (Builder $q) => $q->skip($skip))
            ->when($limit, fn (Builder $q) => $q->limit($limit));
    }

    /**
     * Execute the search pipeline and return paginated results
     */
    public function executeSearch(Request $request): LengthAwarePaginator
    {
        $this->applySearchConditions($request);
        $this->applyOrdering($request);

        $limit = $request->get('limit') ?? $this->defaultLimit;

        return $this->paginate($limit);
    }

    /**
     * Paginate the base query
     *
     * @param array<string> $columns
     */
    public function paginate(?int $perPage, array $columns = ['*']): LengthAwarePaginator
    {
        return match (true) {
            empty($perPage) => $this->baseQuery->paginate($this->baseQuery->count()),
            default         => $this->baseQuery->paginate($perPage, $columns),
        };
    }

    /**
     * Retrieves the model name from the table
     */
    public function getModelName(): string
    {
        return Str::singular(Str::studly($this->model->getTable()));
    }

    // ──────────────────────────────────────────────────────────────
    //  CREATE
    // ──────────────────────────────────────────────────────────────

    /**
     * Create a model record within a transaction, optionally syncing pivot relations
     */
    public function create(
        array $input,
        ?SupportCollection $relationToSync = null,
    ): Model|null {
        return DB::transaction(function () use ($input, $relationToSync) {
            $baseModel = $this->model->newInstance($input);
            $baseModel->save();
            $this->syncRelations($relationToSync, $baseModel);

            return $baseModel;
        });
    }

    // ──────────────────────────────────────────────────────────────
    //  UPDATE
    // ──────────────────────────────────────────────────────────────

    /**
     * Update an already-loaded model within a transaction
     */
    public function updateFromModel(
        array $values,
        BaseModel|Model $model,
        ?SupportCollection $relationToSync = null,
    ): BaseModel|Model {
        return DB::transaction(function () use ($values, $model, $relationToSync) {
            $model->update($values);
            $this->syncRelations($relationToSync, $model);

            return $model->refresh();
        });
    }

    // ──────────────────────────────────────────────────────────────
    //  DELETE
    // ──────────────────────────────────────────────────────────────

    /**
     * Delete a record within a transaction after validating dependencies
     */
    public function delete(int|string $id): bool|null
    {
        return DB::transaction(function () use ($id) {
            $baseModel = $this->newBaseQuery()->findOrFail($id);
            $this->validateExistRelationship($baseModel);

            return $baseModel->delete();
        });
    }

    /**
     * Toggle soft-delete state: deactivate if active, restore if trashed
     *
     * @return array{code: int, message: string}
     */
    public function deleteOrUndelete(BaseModel $model): array
    {
        if ($model->trashed()) {
            $model->restore();

            return [
                'code'    => 200,
                'message' => __($this->getModelName()) . ' successfully reactivated',
            ];
        }

        $model->delete();

        return [
            'code'    => 200,
            'message' => __($this->getModelName()) . ' successfully deactivated',
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  RELATIONS
    // ──────────────────────────────────────────────────────────────

    /**
     * Sync pivot (BelongsToMany) relations from a collection of relation definitions
     *
     * Each item in $relationsToSync should have: ['relation' => string, 'ids' => array]
     */
    public function syncRelations(
        ?SupportCollection $relationsToSync,
        BaseModel|Model $baseModel,
    ): void {
        if (! $relationsToSync?->isNotEmpty()) {
            return;
        }

        $relations = is_array($relationsToSync->first())
            ? $relationsToSync
            : collect([$relationsToSync]);

        $relations->each(
            fn (array $relation) => $baseModel->{$relation['relation']}()->sync($relation['ids']),
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  PROTECTED HELPERS
    // ──────────────────────────────────────────────────────────────

    /**
     * Delegates search logic to the SearchService
     */
    protected function applySearchConditions(Request $request): void
    {
        if ($request->exists('search')) {
            $this->searchService->advancedSearch($request, $this->fieldsFullText);
        } else {
            $this->searchService->findAllFieldsAnd($request, $this->getFieldsSearchable());
        }
    }

    /**
     * Apply ordering; defaults to 'id DESC' when no params are given
     */
    protected function applyOrdering(Request $request): void
    {
        $order = $request->get('order') ?? $request->get('order_by');
        $dir = $request->get('direction') ?? 'DESC';

        $sortBy = match (true) {
            ! empty($order)                => "{$order} {$dir}",
            ! empty($this->sortParameters) => $this->sortParameters,
            default                        => 'id DESC',
        };

        $this->baseQuery->orderByRaw($sortBy);
    }

    /**
     * Validate that the model has no dependent relationships before deletion
     *
     * @throws Exception
     */
    protected function validateExistRelationship(BaseModel|Model $model): void
    {
        $skipRelations = ['audits', 'attachments'];

        $dependentRelations = $model->getRelationShip()
            ->filter(fn (array $relation) => in_array($relation['type'], ['HasMany', 'HasOne', 'MorphMany'])
                && ! in_array($relation['name'], $skipRelations))
            ->filter(fn (array $relation) => $model->{$relation['name']}()->exists());

        if ($dependentRelations->isNotEmpty()) {
            $names = $dependentRelations->pluck('name')->implode(', ');
            throw new Exception("Cannot delete record because it has dependent relations: {$names}");
        }
    }
}

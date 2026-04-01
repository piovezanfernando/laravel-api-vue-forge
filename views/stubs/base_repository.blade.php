@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $namespaceApp }}Repositories;

use App\Models\BaseModel;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Class BaseRepository
 * Base class for all repositories in the application.
 */
abstract class BaseRepository
{
    /** @var Model $model */
    protected $model;

    protected Builder $baseQuery;

    /**
     * Standard constructor
     */
    public function __construct(protected Application $app)
    {
        $this->makeModel();
        $this->baseQuery = $this->app->make($this->model())->newQuery();
    }

    /**
     * Create model record
     */
    public function create()
    {
        $args = func_get_args();
        $req = $args[0] ?? request();
        $options = $args[1] ?? [];
        $relationToSync = $args[2] ?? [];

        return DB::transaction(function () use ($req, $options, $relationToSync) {
            $model = $this->model->newInstance($req->all(), $options);
            $model->save();

            foreach ($relationToSync as $relation => $ids) {
                $model->{$relation}()->sync($ids);
            }

            return $model;
        });
    }

    /**
     * Delete the model record (legacy signature)
     * We keep this method to satisfy parent::delete() calls in override classes.
     */
    public function delete(BaseModel|Model $model)
    {
        $model->delete();
        return ['code' => 200, 'message' => 'Record deleted successful.'];
    }

    /**
     * Delete the model record (modernization architecture)
     * We use "destroy" instead of "delete" in the base class for new services.
     */
    public function destroy(BaseModel|Model $model)
    {
        try {
            // We call delete() even if it might be an override.
            // Since we re-added delete() to the base, this is safe.
            return $this->delete($model);
        } catch (Throwable $e) {
            return ['code' => 400, 'message' => $e->getMessage()];
        }
    }

    /**
     * Find model record for given id
     */
    public function find($id = null, $columns = ['*'])
    {
        if (!$id) return null;
        $query = $this->model->newQuery();
        return $query->find($id, $columns);
    }

    /**
     * Paginate records for scaffold.
     */
    public function paginate(?int $perPage = null, array $columns = ['*']): LengthAwarePaginator
    {
        if (empty($perPage)) {
            return $this->baseQuery->paginate($this->baseQuery->count());
        }

        return $this->baseQuery->paginate($perPage, $columns);
    }

    /**
     * Return searchable fields
     */
    abstract public function getFieldsSearchable();

    /**
     * Configure the Model
     */
    abstract public function model();

    /**
     * Make Model instance
     *
     * @throws \Exception
     */
    public function makeModel(): Model
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Search records from database
     */
    public function search()
    {
        return $this->baseQuery->get();
    }

    /**
     * Update model record
     */
    public function update()
    {
        $args = func_get_args();
        $req = $args[0] ?? request();
        $idOrModel = $args[1] ?? null;

        if (!$idOrModel) {
            return null;
        }

        $model = ($idOrModel instanceof Model) ? $idOrModel : $this->find($idOrModel);
        $model->fill($req->all());
        $model->save();

        return $model;
    }

    /**
     * Update from Model instance (legacy support)
     */
    public function updateFromModel($request, BaseModel $model): array
    {
        $model->update($request->all());
        return $model->toArray();
    }
}

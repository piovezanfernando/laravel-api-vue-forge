@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $namespaceApp }}Repositories;

use {{ $namespaceApp }}Models\BaseModel;
use {{ $namespaceApp }}Services\SearchService;
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
        $this->resetBaseQuery();
    }

    /**
     * Cria um novo registro do modelo
     *
     * @param  array  $input
     * @return Model
     */
    public function create(array $input): Model
    {
        return DB::transaction(function () use ($input) {
            $model = $this->model->newInstance($input);
            $model->save();

            return $model;
        });
    }

    /**
     * Remove o registro do modelo
     *
     * @param  BaseModel|Model  $model
     * @return array{code: int, message: string}
     */
    public function delete(BaseModel|Model $model): array
    {
        try {
            $model->delete();
            return ['code' => 200, 'message' => 'Registro removido com sucesso.'];
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
     * Resets the base query to prevent stale state between operations
     */
    public function resetBaseQuery(): static
    {
        $this->baseQuery = $this->app->make($this->model())->newQuery();

        return $this;
    }

    /**
     * Realiza a busca/filtragem dos dados
     */
    public function search(?Request $request = null)
    {
        $this->resetBaseQuery();
        $searchService = new SearchService($this->baseQuery, $this->model);
        return $searchService->findAllFieldsAnd($request ?? request(), $this->getFieldsSearchable())->get();
    }

    /**
     * Atualiza um registro do modelo
     *
     * @param  array  $input
     * @param  int|Model  $idOrModel
     * @return Model|null
     */
    public function update(array $input, int|Model $idOrModel): ?Model
    {
        $model = ($idOrModel instanceof Model) ? $idOrModel : $this->find($idOrModel);

        if (!$model) {
            return null;
        }

        $model->fill($input);
        $model->save();

        return $model;
    }
}

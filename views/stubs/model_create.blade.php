@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->repository }};

use {{ $config->namespaces->model }}\BaseModel;
use {{ $config->namespaces->services }}\SearchService;
use Exception;
use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class ModelCreate
{
    protected Builder|BaseModel $baseQuery;

    protected Model|BaseModel $model;

    protected SearchService $searchService;

    /** Default limit for pagination */
    protected int $defaultLimit = 15;

    /** Default sort parameters */
    protected string $sortParameters = '';

    /**
     * @throws Exception
     */
    public function __construct(protected readonly Application $app)
    {
        $this->makeModel();
        $this->resetBaseQuery();
        $this->searchService = new SearchService($this->baseQuery, $this->model);
        $this->boot();
    }

    /**
     * Hook for child classes to add custom initialization logic
     * Called after model, query and search service are ready
     */
    protected function boot(): void
    {
        // Override in child repositories for custom setup
    }

    /**
     * Configure the Model
     */
    abstract public function model(): string|BaseModel;

    /**
     * Instantiates a new query in the model
     */
    public function newBaseQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Resets the base query to prevent stale state between operations
     */
    public function resetBaseQuery(): static
    {
        $this->baseQuery = $this->newBaseQuery();

        return $this;
    }

    /**
     * Make Model instance
     *
     * @throws Exception
     */
    protected function makeModel(): Model
    {
        $baseModel = $this->app->make($this->model());

        if (! $baseModel instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Illuminate\Database\Eloquent\Model");
        }

        return $this->model = $baseModel;
    }
}

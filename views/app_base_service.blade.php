@php
    echo "<?php".PHP_EOL;
@endphp

namespace App\Services;

use App\Models\BaseModel;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;

abstract class BaseService
{
    protected Request $request;
    protected BaseRepository $repository;

    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Make Model instance
     */
    public function makeModel(): BaseRepository
    {
        $baseRepo = \App::make($this->repo());
        if (!$baseRepo instanceof BaseRepository) {
            throw new \Exception('Class {$this->repo()} must be an instance of BaseRepository');
        }
        $this->repository = $baseRepo;
        return $this->repository;
    }

    /**
     * Configure the repository
     */
    abstract public function repo(): string|BaseRepository;

    /**
     * Call repository to create one record from data property
     */
    public function create(): array
    {
        $classification = $this->repository->create($this->request->all());
        return $classification->toArray();
    }

    /**
     * Call repository to deactivate or activate record in database
     */
    public function delete(BaseModel $model): array
    {
        return $this->repository->deleteOrUndelete($model);
    }

    /**
     * Call repository to find a record according to param of search
     */
    public function search(Request $request): array
    {
        $company = $this->repository->executeSearch($request);
        return $company->toArray();
    }

    /**
     * Set property request to use in methods inside class
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Call repository to update record according id
     */
    public function update(BaseModel $model): array
    {
        return $this->repository->updateFromModel($this->request->all(), $model);
    }
}

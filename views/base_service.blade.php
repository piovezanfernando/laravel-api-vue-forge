@php
    echo "<?php".PHP_EOL;
@endphp

namespace App\Services;

use App\Models\BaseModel;
use App\Repositories\BaseRepository;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

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
        $baseRepo = App::make($this->repo());
        if (!$baseRepo instanceof BaseRepository) {
            throw new Exception('Class {$this->repo()} must be an instance of BaseRepository');
        }
        $this->repository = $baseRepo;
        return $this->repository;
    }

    /**
     * Configure the repository
     */
    abstract public function repo(): string|BaseRepository;

    /**
     * Call repository to create one record
     */
    public function create(Request $request): BaseModel|Model
    {
        return $this->repository->create($request->all());
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
    public function search(Request $request): LengthAwarePaginator
    {
        return $this->repository->executeSearch($request);
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
    public function update(Request $request, BaseModel $model): BaseModel|Model
    {
        return $this->repository->updateFromModel($request->all(), $model);
    }
}

@php
    echo "<?php".PHP_EOL;
@endphp

namespace App\Services;

use App\Models\BaseModel;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class BaseService
{
    /**
     * Repository is injected via Laravel's container when the child service is resolved.
     * Each child class declares its own constructor with the concrete repository type.
     */
    public function __construct(protected readonly BaseRepository $repository) {}

    /**
     * Create a record from the request data
     */
    public function create(Request $request): BaseModel|Model
    {
        return $this->repository->create($request->all());
    }

    /**
     * Toggle soft-delete state of a record
     *
     * @return array{code: int, message: string}
     */
    public function delete(BaseModel $model): array
    {
        return $this->repository->deleteOrUndelete($model);
    }

    /**
     * Execute search with pagination
     */
    public function search(Request $request): LengthAwarePaginator
    {
        return $this->repository->executeSearch($request);
    }

    /**
     * Update a record from the request data
     */
    public function update(Request $request, BaseModel $model): BaseModel|Model
    {
        return $this->repository->updateFromModel($request->all(), $model);
    }
}

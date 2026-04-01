@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $namespaceApp }}Services;

use App\Models\BaseModel;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Class BaseService
 * Base class for all services in the application.
 * 
 * NOTE: We use variadic arguments or no-signature methods where necessary 
 * to maintain compatibility with existing project services during modernization.
 */
#[\AllowDynamicProperties]
abstract class BaseService
{
    /** @var BaseRepository $repository */
    protected $repository;

    /**
     * Standard constructor previously used in legacy architecture
     */
    public function __construct()
    {
        $this->instanceRepository();
    }

    /**
     * Set the current request for the service
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Safely retrieve the current request from property or helper
     */
    protected function currentRequest(?Request $request = null): Request
    {
        return $request ?? $this->request ?? request();
    }

    /**
     * Call repository to create one record
     *
     * @return mixed
     */
    public function create()
    {
        $args = func_get_args();
        return $this->repository->create($this->currentRequest($args[0] ?? null));
    }

    /**
     * Configure the Repository
     */
    abstract public function repo(): string|BaseRepository;

    /**
     * Call repository to delete record according id
     *
     * @return array{code: int, message: string}
     */
    public function delete(BaseModel|Model $model): array
    {
        return $this->repository->destroy($model);
    }

    /**
     * Call repository to return records from database
     *
     * @return mixed
     */
    public function search()
    {
        $args = func_get_args();
        return $this->repository->search($this->currentRequest($args[0] ?? null));
    }

    /**
     * Call repository to update record according id
     *
     * @return mixed
     */
    public function update(BaseModel|Model $model)
    {
        $args = func_get_args();
        // Since the first arg is $model in the legacy signature, we need to handle that.
        // In new architecture, update($model, $request).
        $request = (isset($args[1]) && $args[1] instanceof Request) ? $args[1] : null;
        
        return $this->repository->update($this->currentRequest($request), $model);
    }

    /**
     * Instance the repository based on the repo() method return
     */
    protected function instanceRepository(): void
    {
        $repo = $this->repo();
        if (is_string($repo)) {
            $this->repository = app($repo);
        } else {
            $this->repository = $repo;
        }
    }
}

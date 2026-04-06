@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $namespaceApp }}Services;

use {{ $namespaceApp }}Models\BaseModel;
use {{ $namespaceApp }}Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Class BaseService
 * Classe base para todos os serviços da aplicação.
 * 
 * NOTA: Usamos argumentos variádicos ou métodos sem assinatura rígida onde necessário 
 * para manter a compatibilidade com serviços existentes durante a modernização.
 */
#[\AllowDynamicProperties]
abstract class BaseService
{
    /** @var BaseRepository $repository */
    protected $repository;

    /** @var Request $request */
    protected $request;

    /**
     * Construtor padrão usado anteriormente na arquitetura legada
     */
    public function __construct()
    {
        $this->instanceRepository();
    }

    /**
     * Define a requisição atual para o serviço
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Recupera com segurança a requisição atual da propriedade ou do helper
     */
    protected function currentRequest(?Request $request = null): Request
    {
        return $request ?? $this->request ?? request();
    }

    /**
     * Chama o repositório para criar um registro
     *
     * @param  Request  $request
     * @return mixed
     */
    public function create(Request $request)
    {
        return $this->repository->create($request->all());
    }

    /**
     * Configura o Repositório
     */
    public function repo(): string|BaseRepository
    {
        return '';
    }

    /**
     * Chama o repositório para remover um registro de acordo com o modelo
     *
     * @param  BaseModel|Model  $model
     * @return array{code: int, message: string}
     */
    public function delete(BaseModel|Model $model): array
    {
        return $this->repository->delete($model);
    }

    /**
     * Chama o repositório para retornar registros do banco de dados
     *
     * @param  Request|null  $request
     * @return mixed
     */
    public function search(?Request $request = null)
    {
        return $this->repository->search($this->currentRequest($request));
    }

    /**
     * Chama o repositório para atualizar um registro de acordo com o modelo
     *
     * @param  Request  $request
     * @param  BaseModel|Model  $model
     * @return mixed
     */
    public function update(Request $request, BaseModel|Model $model)
    {
        return $this->repository->update($request->all(), $model);
    }

    /**
     * Instancia o repositório com base no retorno do método repo()
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

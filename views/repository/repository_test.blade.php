@php
    echo "<?php".PHP_EOL;
@endphp

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use {{ $config->namespaces->repository }}\{{ $config->modelNames->name }}Repository;
use Illuminate\Http\Request;

uses(\Tests\ApiTestTrait::class);

uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->{{ $config->modelNames->camel }}Repo = app({{ $config->modelNames->name }}Repository::class);
});

test('create {{ $config->modelNames->human }}', function () {
    ${{ $config->modelNames->camel }} = new Request({{ $config->modelNames->name }}::factory()->make()->toArray());

    $created{{ $config->modelNames->name }} = $this->{{ $config->modelNames->camel }}Repo->create(${{ $config->modelNames->camel }});

    $created{{ $config->modelNames->name }} = $created{{ $config->modelNames->name }}->toArray();
    expect($created{{ $config->modelNames->name }})->toHaveKey('id')
        ->and($this->{{ $config->modelNames->camel }}Repo->find($created{{ $config->modelNames->name }}['id'])
        )->not->toBeNull('Classification with given id must be in DB');
    $this->assertModelData(${{ $config->modelNames->camel }}->all(), $created{{ $config->modelNames->name }});
});

test('delete {{ $config->modelNames->human }}', function () {
    ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create();

    $resp = $this->{{ $config->modelNames->camel }}Repo->deleteOrUndelete(${{ $config->modelNames->camel }});

    ${{ $config->modelNames->camel }}Db = $this->{{ $config->modelNames->camel }}Repo->find(${{ $config->modelNames->camel }}['id']);
    expect($resp['code'])->toEqual(200)
        ->and(${{ $config->modelNames->camel }}Db)->toBeNull('{{ $config->modelNames->camel }} successfully deactivated');
});

test('update {{ $config->modelNames->human }}', function () {
    ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create();
    $fake{{ $config->modelNames->name }} = new Request({{ $config->modelNames->name }}::factory()->make()->toArray());

    $updated{{ $config->modelNames->name }} = $this->{{ $config->modelNames->camel }}Repo->updateFromModel($fake{{ $config->modelNames->name }}, ${{ $config->modelNames->camel }});

    $this->assertModelData($fake{{ $config->modelNames->name }}->all(), $updated{{ $config->modelNames->name }});
    $db{{ $config->modelNames->name }} = $this->{{ $config->modelNames->camel }}Repo->find(${{ $config->modelNames->camel }}['{{ $config->primaryName }}']);
    $this->assertModelData($fake{{ $config->modelNames->name }}->all(), $db{{ $config->modelNames->name }}->toArray());
});

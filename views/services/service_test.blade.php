@php
    echo "<?php".PHP_EOL;
@endphp

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use {{ $config->namespaces->services }}\{{ $config->modelNames->name }}Service;
use {{ $config->namespaces->repository }}\{{ $config->modelNames->name }}Repository;
use Illuminate\Http\Request;

uses(\Tests\ApiTestTrait::class);
uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);
uses(\Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $this->{{ $config->modelNames->camel }}Service = app({{ $config->modelNames->name }}Service::class);
    $this->{{ $config->modelNames->camel }}Repository = app({{ $config->modelNames->name }}Repository::class);
});

test('create {{ $config->modelNames->human }} by service', function () {
    $data = new Request({{ $config->modelNames->name }}::factory()->make()->toArray());

    $this->{{$config->modelNames->camel}}Service->setRequest($data);
    $created{{ $config->modelNames->name }} = $this->{{$config->modelNames->camel}}Service->create();

    expect($created{{ $config->modelNames->name }})->toHaveKey('id')
        ->and($created{{ $config->modelNames->name }}['id'])->not->toBeNull('Created {{ $config->modelNames->human }} must have id specified')
        ->and($this
            ->{{$config->modelNames->camel}}Repository
            ->find($created{{ $config->modelNames->name }}['id']))
            ->not
            ->toBeNull('Classification with given id must be in DB');
    $this->assertModelData($data->all(), $created{{ $config->modelNames->name }});
});

test('delete {{ $config->modelNames->human }} by service', function () {
    $data = {{ $config->modelNames->name }}::factory()->create();

    $delete{{ $config->modelNames->name }} = $this->{{$config->modelNames->camel}}Service->delete($data);

    expect($delete{{ $config->modelNames->name }}['code'] === 200)->toBeTrue()
        ->and($this->{{$config->modelNames->camel}}Repository->find($data['id']))->toBeNull('Classification should not exist in DB');
});

test('read all {{ $config->modelNames->human }} by service', function () {
    $data = {{ $config->modelNames->name }}::factory()->create();

    $req = new Request(['limit' => 1, 'direction' => 'desc', 'hide_relation' => '*']);
    $db{{ $config->modelNames->name }} = $this->{{$config->modelNames->camel}}Service->search($req);

    expect($db{{ $config->modelNames->name }})->toHaveKey('data');
    $this->assertModelData($db{{ $config->modelNames->name }}['data'][0], $data->toArray());
});

test('update {{ $config->modelNames->human }} by service', function () {
    ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create();
    $fake{{ $config->modelNames->name }} = new Request({{ $config->modelNames->name }}::factory()->make()->toArray());

    $this->{{$config->modelNames->camel}}Service->setRequest($fake{{ $config->modelNames->name }});
    $updated{{ $config->modelNames->name }} = $this->{{$config->modelNames->camel}}Service->update(${{ $config->modelNames->camel }});

    $this->assertModelData($fake{{ $config->modelNames->name }}->all(), ${{ $config->modelNames->camel }}->toArray());
    $db{{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find(${{ $config->modelNames->camel }}['id']);
    $this->assertModelData($fake{{ $config->modelNames->name }}->all(), $db{{ $config->modelNames->camel }}->toArray());
});

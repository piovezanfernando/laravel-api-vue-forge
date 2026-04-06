@php
    echo "<?php".PHP_EOL;
@endphp

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use {{ $config->namespaces->services }}\{{ $config->modelNames->name }}Service;
use {{ $config->namespaces->repository }}\{{ $config->modelNames->name }}Repository;
use Illuminate\Http\Request;

uses(\Tests\ApiTestTrait::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->{{ $config->modelNames->camel }}Service = app({{ $config->modelNames->name }}Service::class);
    $this->{{ $config->modelNames->camel }}Repository = app({{ $config->modelNames->name }}Repository::class);
});

test('create {{ $config->modelNames->human }} by service', function () {
    $request = new Request({{ $config->modelNames->name }}::factory()->make()->toArray());

    $created{{ $config->modelNames->name }} = $this->{{$config->modelNames->camel}}Service->create($request);

    expect($created{{ $config->modelNames->name }})->not->toBeNull()
        ->and($this->{{$config->modelNames->camel}}Repository->find($created{{ $config->modelNames->name }}->id))->not->toBeNull();
    $this->assertModelData($request->all(), $created{{ $config->modelNames->name }}->toArray());
});

test('delete {{ $config->modelNames->human }} by service', function () {
    $data = {{ $config->modelNames->name }}::factory()->create();

    $delete{{ $config->modelNames->name }} = $this->{{$config->modelNames->camel}}Service->delete($data);

    expect($delete{{ $config->modelNames->name }}['code'])->toBe(200)
        ->and($this->{{$config->modelNames->camel}}Repository->find($data->id))->toBeNull();
});

test('read all {{ $config->modelNames->human }} by service', function () {
    $data = {{ $config->modelNames->name }}::factory()->create();

    $req = new Request(['limit' => 1, 'direction' => 'desc', 'hide_relation' => '*']);
    $db{{ $config->modelNames->name }} = $this->{{$config->modelNames->camel}}Service->search($req);

    expect($db{{ $config->modelNames->name }}->isNotEmpty())->toBeTrue();
    $this->assertModelData($db{{ $config->modelNames->name }}->first()->toArray(), $data->toArray());
});

test('update {{ $config->modelNames->human }} by service', function () {
    ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create();
    $request = new Request({{ $config->modelNames->name }}::factory()->make()->toArray());

    $updated{{ $config->modelNames->name }} = $this->{{$config->modelNames->camel}}Service->update($request, ${{ $config->modelNames->camel }});

    $this->assertModelData($request->all(), ${{ $config->modelNames->camel }}->toArray());
    $db{{ $config->modelNames->camel }} = $this->{{ $config->modelNames->camel }}Repository->find(${{ $config->modelNames->camel }}['id']);
    $this->assertModelData($request->all(), $db{{ $config->modelNames->camel }}->toArray());
});

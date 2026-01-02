@php
    echo "<?php".PHP_EOL;
@endphp

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use App\Models\User;

uses({{ $config->namespaces->tests }}\ApiTestTrait::class);
uses(Illuminate\Foundation\Testing\DatabaseTransactions::class);
uses(Illuminate\Foundation\Testing\DatabaseMigrations::class);

beforeEach(function () {
    $user = User::factory()->create();
    $this->actingAs($user);
});

test('create {{ $config->modelNames->human }}', function () {
    ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->make()->toArray();

    $this->response = $this
        ->post('/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}', ${{ $config->modelNames->camel }})
        ->assertStatus(200);

    $this->assertApiResponse(${{ $config->modelNames->camel }});
});

test('read {{ $config->modelNames->human }}', function () {
    ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create()->toArray();

    $this->response = $this
        ->get('/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}/' . ${{ $config->modelNames->camel }}['{{ $config->primaryName }}'])
        ->assertStatus(200);

    $this->assertApiResponse(${{ $config->modelNames->camel }});
});

test('update {{ $config->modelNames->human }}', function () {
    ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create()->toArray();
    $edited{{ $config->modelNames->name }} = {{ $config->modelNames->name }}::factory()->make()->toArray();

    $this->response = $this
        ->put('/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}/' . ${{ $config->modelNames->camel }}['{{ $config->primaryName }}'], $edited{{ $config->modelNames->name }})
        ->assertStatus(200);

    $this->assertApiResponse($edited{{ $config->modelNames->name }});
});

test('delete {{ $config->modelNames->human }}', function () {
    ${{ $config->modelNames->camel }} = {{ $config->modelNames->name }}::factory()->create()->toArray();

    $this->response = $this
        ->delete('/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}/' . ${{ $config->modelNames->camel }}['{{ $config->primaryName }}'])
        ->assertStatus(200);

    $this->assertApiSuccess();
    $this->response = $this
        ->get('/{{ $config->apiPrefix }}/{{ $config->modelNames->dashedPlural }}/' . ${{ $config->modelNames->camel }}['{{ $config->primaryName }}'])
        ->assertStatus(404);
});

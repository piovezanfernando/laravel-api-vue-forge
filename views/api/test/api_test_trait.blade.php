@php
    echo "<?php".PHP_EOL;
@endphp

use Illuminate\Testing\TestResponse;

trait ApiTestTrait
{
    private TestResponse $response;

    private array $scapeValue = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'is_active',
        'deleted_by',
        'created_by',
        'updated_by'
    ];

    public function assertApiResponse(Array $actualData): void
    {
        $this->assertApiSuccess();

        $response = $this->response->json();
        $responseData = $response['data'];

        expect($responseData['id'])->not(null);
        $this->assertModelData($actualData, $responseData);
    }

    public function assertApiSuccess(): void
    {
        expect($this->response->json())->toHaveKey('success', true);
    }

    public function assertModelData(Array $actualData, Array $expectedData): void
    {
        foreach ($actualData as $key => $value) {
            if (in_array($key, $this->scapeValue)) {
                continue;
            }
            expect($expectedData)->toHaveKey($key, $value);
        }
    }
}

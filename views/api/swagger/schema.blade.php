@php
    echo '<?php' .PHP_EOL;
@endphp

{{'namespace App\Swagger;'}}

/**
 * @OA\Schema(schema="{{ $config->modelNames->name }}Request",
 *    required={!! $requiredFields !!},
 *
 {!! $properties !!},
 *    @OA\Property(
 *        property="attachments",
 *        type="array",
 *        @OA\Items(ref="#/components/schemas/AttachmentRequest")
 *    ),
 *    @OA\Property(
 *        property="icon",
 *        type="object",
 *        nullable=true,
 *        ref="#/components/schemas/IconRequest"
 *    ),
 * )
 *
 * @OA\Schema(schema="{{ $config->modelNames->name }}Response",
 *    required={!! $requiredFields !!},
 *
 {!! $properties !!},
 *    @OA\Property(
 *        property="attachments",
 *        type="array",
 *        @OA\Items(ref="#/components/schemas/AttachmentResponse")
 *    ),
 *    @OA\Property(
 *        property="icon",
 *        type="object",
 *        nullable=true,
 *        ref="#/components/schemas/IconResponse"
 *    ),
 * )
 *
 */
class {{ $config->modelNames->name }}Schema
{
}

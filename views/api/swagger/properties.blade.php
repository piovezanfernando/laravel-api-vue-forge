* @OA\Property(
 *    property="{{ $fieldName }}",
 *    nullable={{ $nullable }},
 *    type="{{ $type }}",
 *    example="{{ $example }}",
@if($format)
 *    format="{{ $format }}",
@endif
 *    description="{{ $description }}
@foreach($validateFields as $index => $rule)
@if($index === count($validateFields) - 1)
 *    {{ $rule }}"
@else
 *    {{ $rule }}
@endif
@endforeach
 *    )
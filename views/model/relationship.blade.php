    public function {{ $functionName }}(): {{ $relationClass }}
    {
        return $this->{{ $relation }}({{ $relatedModel }}::class{!! $fields !!})
            ->select(['id', 'name'])->setEagerLoads([]);
    }
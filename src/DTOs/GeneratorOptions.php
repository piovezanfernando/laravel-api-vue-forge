<?php

namespace PiovezanFernando\LaravelApiVueForge\DTOs;

class GeneratorOptions
{
    public bool $softDelete;
    public bool $saveSchemaFile;
    public bool $localized;
    public bool $repositoryPattern;
    public bool $resources;
    public bool $factory;
    public bool $seeder;
    public bool $swagger;
    public bool $tests;
    public bool $servicePattern;
    public bool $uniqueRequest;
    public array $excludedFields;
    public array $excludedFillable;
}

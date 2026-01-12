<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    */

    'path' => [

        'migration' => database_path('migrations/'),

        'model' => app_path('Models/'),

        'repository' => app_path('Repositories/'),

        'service' => app_path('Services'),

        'api_routes' => base_path('routes/api.php'),

        'request' => app_path('Http/Requests/'),

        'api_request' => app_path('Http/Requests/API/'),

        'controller' => app_path('Http/Controllers/'),

        'api_controller' => app_path('Http/Controllers/API/'),

        'api_resource' => app_path('Http/Resources/'),

        'schema_files' => resource_path('model_schemas/'),

        'templates_dir' => resource_path('apiforge/apiforge-generator-templates/'),

        'seeder' => database_path('seeders/'),

        'database_seeder' => database_path('seeders/DatabaseSeeder.php'),

        'factory' => database_path('factories/'),

        'tests' => base_path('tests/'),

        'repository_test' => base_path('tests/Repositories/'),

        'api_test' => base_path('tests/APIs/'),

        'frontend' => base_path('front/'),

        'frontend_repository' => 'git@github.com:piovezanfernando/quasar-api-vue-forge.git',
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    */

    'namespace' => [

        'model' => 'App\Models',

        'repository' => 'App\Repositories',

        'services' => 'App\Services',

        'controller' => 'App\Http\Controllers',

        'api_controller' => 'App\Http\Controllers\API',

        'api_resource' => 'App\Http\Resources',

        'request' => 'App\Http\Requests',

        'api_request' => 'App\Http\Requests\API',

        'seeder' => 'Database\Seeders',

        'factory' => 'Database\Factories',

        'tests' => 'Tests',

        'repository_test' => 'Tests\Repositories',

        'service_test' => 'Tests\Services',

        'api_test' => 'Tests\APIs',
    ],


    /*
    |--------------------------------------------------------------------------
    | Model extend class
    |--------------------------------------------------------------------------
    |
    */

    'model_extend_class' => 'Illuminate\Database\Eloquent\Model',

    /*
    |--------------------------------------------------------------------------
    | API routes prefix & version
    |--------------------------------------------------------------------------
    |
    */

    'api_prefix' => 'api',

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    */

    'options' => [

        'soft_delete' => true,

        'save_schema_file' => false,

        'localized' => true,

        'repository_pattern' => true,

        'resources' => false,

        'factory' => true,

        'seeder' => false,

        'tests' => true, // generate test cases for your APIs

        'excluded_fields' => [
            'id',
            'created_by',
            'updated_by',
            'deleted_by',
            'created_at',
            'updated_at',
            'deleted_at',
            'tenant_id',
        ], // Array of columns that doesn't required while creating module,

        'excluded_fillable' => [
            'tenant_id',
            'created_by',
            'updated_by',
            'deleted_by',
        ],

        'separate_rules' => true,

        'body_parameter' => true,

        'base_model' => false,

        'base_service' => true,

        'base_request' => true,

        'service_pattern' => true,

        'unique_request' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Timestamp Fields
    |--------------------------------------------------------------------------
    |
    */

    'timestamps' => [

        'enabled' => true,

        'created_at' => 'created_at',

        'updated_at' => 'updated_at',

        'deleted_at' => 'deleted_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Specify custom doctrine mappings as per your need
    |--------------------------------------------------------------------------
    |
    */

    'from_table' => [

        'doctrine_mappings' => [],
    ],

];

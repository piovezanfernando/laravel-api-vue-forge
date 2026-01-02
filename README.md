<h1 align="center">Laravel API Vue Forge</h1>

Laravel API and Vue.js Code Generator - Database-first approach.

[![Latest Stable Version](http://poser.pugx.org/piovezanfernando/laravel-api-vue-forge/v)](https://packagist.org/packages/piovezanfernando/laravel-api-vue-forge) 
[![Total Downloads](http://poser.pugx.org/piovezanfernando/laravel-api-vue-forge/downloads)](https://packagist.org/packages/piovezanfernando/laravel-api-vue-forge) 
[![License](http://poser.pugx.org/piovezanfernando/laravel-api-vue-forge/license)](https://packagist.org/packages/piovezanfernando/laravel-api-vue-forge) 
[![PHP Version Require](http://poser.pugx.org/piovezanfernando/laravel-api-vue-forge/require/php)](https://packagist.org/packages/piovezanfernando/laravel-api-vue-forge)

Generate high-quality APIs and Vue.js (Quasar) frontends directly from your existing database tables.

## Features

- **Database-first**: Uses your existing database schema as the source of truth.
- **RESTful API**: Generates Controllers, Requests, Resources, and Routes.
- **Service & Repository Patterns**: Implements clean architecture by default.
- **Vue.js + Quasar**: Generates a complete frontend based on Quasar Framework.
- **Automated Tests**: Generates Pest/PHPUnit tests for your APIs and Repositories.
- **Rollback Support**: Easy rollback of generated files.

## Installation

```bash
composer require piovezanfernando/laravel-api-vue-forge
```

## Quick Start

### 1. Publish Configuration

```bash
php artisan apiforge:publish
```

### 2. Generate API from Table

```bash
php artisan apiforge:api Post --fromTable --table=posts
```

### 3. Generate Vue.js Frontend

```bash
php artisan apiforge:front-quasar Post
```

## Available Commands

| Command | Description |
| --- | --- |
| `apiforge:api` | Generate API from a table |
| `apiforge:front-quasar` | Generate Quasar-based frontend |
| `apiforge:publish` | Publish configuration and base files |
| `apiforge:rollback` | Rollback generated files |
| `apiforge:migration` | Generate a migration from table (internal use) |
| `apiforge:model` | Generate a model from table |
| `apiforge:repository` | Generate a repository for a model |

## Configuration

The configuration file is located at `config/laravel_api_vue_forge.php`.

## Credits

- [Fernando Piovezan](https://github.com/piovezanfernando)
- Based on the original InfyOm Laravel Generator.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

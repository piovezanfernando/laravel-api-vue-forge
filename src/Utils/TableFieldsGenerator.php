<?php

namespace PiovezanFernando\LaravelApiVueForge\Utils;

use DB;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PiovezanFernando\LaravelApiVueForge\Common\GeneratorConfig;
use PiovezanFernando\LaravelApiVueForge\Common\GeneratorField;
use PiovezanFernando\LaravelApiVueForge\Common\GeneratorFieldRelation;

class GeneratorForeignKey
{
    /** @var string */
    public $name;
    public $localField;
    public $foreignField;
    public $foreignTable;
    public $onUpdate;
    public $onDelete;
}

class GeneratorTable
{
    /** @var string */
    public $primaryKey;

    /** @var GeneratorForeignKey[] */
    public $foreignKeys;
}

class TableFieldsGenerator
{
    /** @var string */
    public $tableName;
    public $primaryKey;

    /** @var bool */
    public $defaultSearchable;

    /** @var array */
    public $timestamps;
    public $fullTextIndices;

    /** @var AbstractSchemaManager */
    private $schemaManager;

    /** @var Column[] */
    private $columns;

    /** @var GeneratorField[] */
    public $fields;

    /** @var GeneratorFieldRelation[] */
    public $relations;

    /** @var array */
    public $ignoredFields;

    /** @var \Doctrine\DBAL\Schema\Table */
    public $tableDetails;

    public GeneratorConfig $config;

    public function __construct($tableName, $ignoredFields, $connection = '')
    {
        $this->config = app(GeneratorConfig::class);
        $this->tableName = $tableName;
        $this->ignoredFields = $ignoredFields;

        $columns = Schema::getColumns($tableName);
        $this->columns = [];
        foreach ($columns as $column) {
            if (!in_array($column['name'], $ignoredFields)) {
                $this->columns[] = $column;
            }
        }

        $this->fullTextIndices = $this->getFullTextIndexOfTable($tableName);
        $this->primaryKey = $this->getPrimaryKeyOfTable($tableName);
        $this->timestamps = static::getTimestampFieldNames();
        $this->defaultSearchable = config('laravel_api_vue_forge.options.tables_searchable_default', false);
    }

    /**
     * Prepares array of GeneratorField from table columns.
     */
    public function prepareFieldsFromTable()
    {
        foreach ($this->columns as $column) {
            $type = $column['type_name'];

            switch ($type) {
                case 'integer':
                case 'int':
                    $field = $this->generateIntFieldInput($column, 'integer');
                    break;
                case 'smallint':
                    $field = $this->generateIntFieldInput($column, 'smallInteger');
                    break;
                case 'bigint':
                    $field = $this->generateIntFieldInput($column, 'bigInteger');
                    break;
                case 'boolean':
                case 'tinyint':
                    $field = $this->generateField($column, 'boolean', 'checkbox');
                    break;
                case 'datetime':
                    $field = $this->generateField($column, 'datetime', 'date');
                    break;
                case 'datetimetz':
                    $field = $this->generateField($column, 'dateTimeTz', 'date');
                    break;
                case 'date':
                    $field = $this->generateField($column, 'date', 'date');
                    break;
                case 'time':
                    $field = $this->generateField($column, 'time', 'text');
                    break;
                case 'decimal':
                    $field = $this->generateNumberInput($column, 'decimal');
                    break;
                case 'float':
                    $field = $this->generateNumberInput($column, 'float');
                    break;
                case 'text':
                    $field = $this->generateField($column, 'text', 'textarea');
                    break;
                default:
                    $field = $this->generateField($column, 'string', 'text');
                    break;
            }

            if (strtolower($field->name) == 'password') {
                $field->htmlType = 'password';
            } elseif (strtolower($field->name) == 'email') {
                $field->htmlType = 'email';
            } elseif (in_array($field->name, $this->timestamps)) {
                $field->isSearchable = false;
                $field->isFillable = false;
                $field->inForm = false;
                $field->inIndex = false;
                $field->inView = false;
            } elseif (in_array($field->name, $this->config->options->excludedFillable)) {
                $field->isFillable = false;
            }
            $field->isNotNull = !$column['nullable'];
            $field->description = $column['comment'] ?? ''; // get comments from table

            $this->fields[] = $field;
        }
    }

    /**
     * Get primary key of given table.
     *
     * @param string $tableName
     *
     * @return string|null The column name of the (simple) primary key
     */
    public function getPrimaryKeyOfTable($tableName)
    {
        $column = '';
        foreach (Schema::getIndexes($tableName) as $index) {
            if ($index['primary']) {
                $column = $index['columns'][0];
            }
        }

        return $column;
    }

    public function getFullTextIndexOfTable($tableName)
    {

        $indexes = Schema::getIndexes($tableName);
        $complete = false;
        $fullTextIndices = [];

        /**  TODO */
//        foreach ($indexes as $index) {
//            foreach ($index->getFlags() as $flag) {
//                if ($flag == 'fulltext' && !$complete) {
//                    foreach ($index->getColumns() as $column) {
//                        $fullTextIndices[] = $column;
//                        $complete = true;
//                    }
//                }
//            }
//        }
        return $fullTextIndices;
    }

    /**
     * Get timestamp columns from config.
     *
     * @return array the set of [created_at column name, updated_at column name]
     */
    public static function getTimestampFieldNames()
    {
        if (!config('laravel_api_vue_forge.timestamps.enabled', true)) {
            return [];
        }

        $createdAtName = config('laravel_api_vue_forge.timestamps.created_at', 'created_at');
        $updatedAtName = config('laravel_api_vue_forge.timestamps.updated_at', 'updated_at');
        $deletedAtName = config('laravel_api_vue_forge.timestamps.deleted_at', 'deleted_at');

        return [$createdAtName, $updatedAtName, $deletedAtName];
    }

    /**
     * Generates integer text field for database.
     *
     * @param string $dbType
     * @param Column $column
     *
     * @return GeneratorField
     */
    private function generateIntFieldInput($column, $dbType)
    {
        $field = new GeneratorField();
        $field->name = $column['name'];
        $field->parseDBType($dbType);
        $field->htmlType = 'number';

        if ($column['auto_increment']) {
            $field->dbType .= ',true';
        } else {
            $field->dbType .= ',false';
        }

        if (str_contains($column['type'], 'unsigned')) {
            $field->dbType .= ',true';
        }

        return $this->checkForPrimary($field);
    }

    /**
     * Check if key is primary key and sets field options.
     *
     * @param GeneratorField $field
     *
     * @return GeneratorField
     */
    private function checkForPrimary(GeneratorField $field)
    {
        if ($field->name == $this->primaryKey) {
            $field->isPrimary = true;
            $field->isFillable = false;
            $field->isSearchable = false;
            $field->inIndex = false;
            $field->inForm = false;
            $field->inView = false;
        }

        return $field;
    }

    private function checkForFullText(GeneratorField $field)
    {
        return in_array($field->name, $this->fullTextIndices);
    }

    /**
     * Generates field.
     *
     * @param Column $column
     * @param        $dbType
     * @param        $htmlType
     *
     * @return GeneratorField
     */
    private function generateField($column, $dbType, $htmlType)
    {
        $field = new GeneratorField();
        $field->name = $column['name'];
        $field->fieldDetails = $column;
        $field->parseDBType($dbType); //, $column); TODO: handle column param
        $field->parseHtmlInput($htmlType);
        $field->isFullText = $this->checkForFullText($field);

        return $this->checkForPrimary($field);
    }

    /**
     * Generates number field.
     *
     * @param Column $column
     * @param string $dbType
     *
     * @return GeneratorField
     */
    private function generateNumberInput($column, $dbType)
    {
        $field = new GeneratorField();
        $field->name = $column['name'];
        $length = get_field_length($column['type']);
        $field->parseDBType($dbType . ',' . get_field_precision($length) . ',' . get_field_scale($length));
        $field->htmlType = 'number';

        if ($dbType === 'decimal') {
            $field->numberDecimalPoints = explode(',', $length)[1];
        }

        return $this->checkForPrimary($field);
    }

    /**
     * Prepares relations (GeneratorFieldRelation) array from table foreign keys.
     */
    public function prepareRelations()
    {
        $foreignKeys = $this->prepareForeignKeys();
        $this->checkForRelations($foreignKeys);
    }

    /**
     * Prepares foreign keys from table with required details.
     *
     * @return GeneratorTable[]
     */
    public function prepareForeignKeys()
    {
        $tables = Schema::getTables();

        $fields = [];

        foreach ($tables as $table) {
            $primaryKey = '';
            foreach (Schema::getIndexes($table['name']) as $index) {
                if ($index['primary']) {
                    $primaryKey = $index['columns'][0];
                }
            }
            $formattedForeignKeys = [];
            $tableForeignKeys = Schema::getForeignKeys($table['name']);
            foreach ($tableForeignKeys as $tableForeignKey) {
                $generatorForeignKey = new GeneratorForeignKey();
                $generatorForeignKey->name = $tableForeignKey['name'];
                $generatorForeignKey->localField = $tableForeignKey['columns'][0];
                $generatorForeignKey->foreignField = $tableForeignKey['foreign_columns'][0];
                $generatorForeignKey->foreignTable = $tableForeignKey['foreign_table'];
                $generatorForeignKey->onUpdate = $tableForeignKey['on_update'];
                $generatorForeignKey->onDelete = $tableForeignKey['on_delete'];

                $formattedForeignKeys[] = $generatorForeignKey;
            }

            $generatorTable = new GeneratorTable();
            $generatorTable->primaryKey = $primaryKey;
            $generatorTable->foreignKeys = $formattedForeignKeys;

            $fields[$table['name']] = $generatorTable;
        }

        return $fields;
    }

    /**
     * Prepares relations array from table foreign keys.
     *
     * @param GeneratorTable[] $tables
     */
    private function checkForRelations($tables)
    {
        // get Model table name and table details from tables list
        $modelTableName = $this->tableName;
        $modelTable = $tables[$modelTableName];
        unset($tables[$modelTableName]);

        $this->relations = [];

        // detects many to one rules for model table
        $manyToOneRelations = $this->detectManyToOne($tables, $modelTable);

        if (count($manyToOneRelations) > 0) {
            $this->relations = array_merge($this->relations, $manyToOneRelations);
        }

        foreach ($tables as $tableName => $table) {
            $foreignKeys = $table->foreignKeys;
            $primary = $table->primaryKey;

            // if foreign key count is 2 then check if many to many relationship is there
            if (count($foreignKeys) == 2) {
                $manyToManyRelation = $this->isManyToMany($tables, $tableName, $modelTable, $modelTableName);
                if ($manyToManyRelation) {
                    $this->relations[] = $manyToManyRelation;
                    continue;
                }
            }

            // iterate each foreign key and check for relationship
            foreach ($foreignKeys as $foreignKey) {
                // check if foreign key is on the model table for which we are using generator command
                if ($foreignKey->foreignTable == $modelTableName) {
                    // detect if one to one relationship is there
                    $isOneToOne = $this->isOneToOne($primary, $foreignKey, $modelTable->primaryKey);
                    if ($isOneToOne) {
                        $modelName = model_name_from_table_name($tableName);
                        $this->relations[] = GeneratorFieldRelation::parseRelation('1t1,'.$modelName);
                        continue;
                    }

                    // detect if one to many relationship is there
                    $isOneToMany = $this->isOneToMany($primary, $foreignKey, $modelTable->primaryKey);
                    if ($isOneToMany) {
                        $modelName = model_name_from_table_name($tableName);
                        $this->relations[] = GeneratorFieldRelation::parseRelation(
                            '1tm,'.$modelName.','.$foreignKey->localField
                        );
                        continue;
                    }
                }
            }
        }
    }

    /**
     * Detects many to many relationship
     * If table has only two foreign keys
     * Both foreign keys are primary key in foreign table
     * Also one is from model table and one is from diff table.
     *
     * @param GeneratorTable[] $tables
     * @param string           $tableName
     * @param GeneratorTable   $modelTable
     * @param string           $modelTableName
     *
     * @return bool|GeneratorFieldRelation
     */
    private function isManyToMany($tables, $tableName, $modelTable, $modelTableName)
    {
        // get table details
        $table = $tables[$tableName];

        $isAnyKeyOnModelTable = false;

        // many to many model table name
        $manyToManyTable = '';

        $foreignKeys = $table->foreignKeys;
        $primary = $table->primaryKey;

        // check if any foreign key is there from model table
        foreach ($foreignKeys as $foreignKey) {
            if ($foreignKey->foreignTable == $modelTableName) {
                $isAnyKeyOnModelTable = true;
            }
        }

        // if foreign key is there
        if (!$isAnyKeyOnModelTable) {
            return false;
        }

        foreach ($foreignKeys as $foreignKey) {
            $foreignField = $foreignKey->foreignField;
            $foreignTableName = $foreignKey->foreignTable;

            // if foreign table is model table
            if ($foreignTableName == $modelTableName) {
                $foreignTable = $modelTable;
            } else {
                $foreignTable = $tables[$foreignTableName];
                // get the many to many model table name
                $manyToManyTable = $foreignTableName;
            }

            // if foreign field is not primary key of foreign table
            // then it can not be many to many
            if ($foreignField != $foreignTable->primaryKey) {
                return false;
                break;
            }

            // if foreign field is primary key of this table
            // then it can not be many to many
            if ($foreignField == $primary) {
                return false;
            }
        }

        if (empty($manyToManyTable)) {
            return false;
        }

        $modelName = model_name_from_table_name($manyToManyTable);

        return GeneratorFieldRelation::parseRelation('mtm,'.$modelName.','.$tableName);
    }

    /**
     * Detects if one to one relationship is there
     * If foreign key of table is primary key of foreign table
     * Also foreign key field is primary key of this table.
     *
     * @param string              $primaryKey
     * @param GeneratorForeignKey $foreignKey
     * @param string              $modelTablePrimary
     *
     * @return bool
     */
    private function isOneToOne($primaryKey, $foreignKey, $modelTablePrimary)
    {
        if ($foreignKey->foreignField == $modelTablePrimary) {
            if ($foreignKey->localField == $primaryKey) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detects if one to many relationship is there
     * If foreign key of table is primary key of foreign table
     * Also foreign key field is not primary key of this table.
     *
     * @param string              $primaryKey
     * @param GeneratorForeignKey $foreignKey
     * @param string              $modelTablePrimary
     *
     * @return bool
     */
    private function isOneToMany($primaryKey, $foreignKey, $modelTablePrimary)
    {
        if ($foreignKey->foreignField == $modelTablePrimary) {
            if ($foreignKey->localField != $primaryKey) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect many to one relationship on model table
     * If foreign key of model table is primary key of foreign table.
     *
     * @param GeneratorTable[] $tables
     * @param GeneratorTable   $modelTable
     *
     * @return array
     */
    private function detectManyToOne($tables, $modelTable)
    {
        $manyToOneRelations = [];

        $foreignKeys = $modelTable->foreignKeys;

        foreach ($foreignKeys as $foreignKey) {
            $foreignTable = $foreignKey->foreignTable;
            $foreignField = $foreignKey->foreignField;

            if (!isset($tables[$foreignTable])) {
                continue;
            }

            if ($foreignField == $tables[$foreignTable]->primaryKey) {
                $modelName = model_name_from_table_name($foreignTable);
                $manyToOneRelations[] = GeneratorFieldRelation::parseRelation(
                    'mt1,'.$modelName.','.$foreignKey->localField
                );
            }
        }

        return $manyToOneRelations;
    }
}

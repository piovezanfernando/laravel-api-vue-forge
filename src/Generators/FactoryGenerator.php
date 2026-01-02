<?php

namespace PiovezanFernando\LaravelApiVueForge\Generators;

use Illuminate\Support\Str;
use PiovezanFernando\LaravelApiVueForge\Utils\GeneratorFieldsInputUtil;

class FactoryGenerator extends BaseGenerator
{
    private string $fileName;

    private array $relations = [];
    private array $removeFields = [];

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->factory;
        $this->fileName = $this->config->modelNames->name.'Factory.php';
        $this->removeFields = config('laravel_api_vue_forge.options.hidden_fields', [])
            + config('laravel_api_vue_forge.options.excluded_fields');
        $clean = array_search('tenant_id', $this->removeFields);
        unset($this->removeFields[$clean]);

        //setup relations if available
        //assumes relation fields are tailed with _id if not supplied
        if (property_exists($this->config, 'relations')) {
            foreach ($this->config->relations as $r) {
                if ($r->type == 'mt1') {
                    $relation = (isset($r->inputs[0])) ? $r->inputs[0] : null;
                    if (isset($r->inputs[1])) {
                        $field = $r->inputs[1];
                    } else {
                        $field = Str::snake($relation).'_id';
                    }
                    if ($field) {
                        $rel = $relation;
                        $this->relations[$field] = [
                            'relation'      => $rel,
                            'model_class'   => $this->config->namespaces->model.'\\'.$relation,
                        ];
                    }
                }
            }
        }
    }

    public function variables(): array
    {
        $relations = $this->getRelationsBootstrap();

        return [
            'fields'        => $this->generateFields(),
            'relations'     => $relations['text'],
            'usedRelations' => $relations['uses'],
        ];
    }

    public function generate()
    {
        $templateData = view('laravel-api-vue-forge::model.factory', $this->variables())->render();

        g_filesystem()->createFile($this->path.$this->fileName, $templateData);

        $this->config->commandComment(apiforge_nl().'Factory created: ');
        $this->config->commandInfo($this->fileName);
    }

    protected function generateFields(): string
    {
        $fields = [];

        //get model validation rules
        /** @var ModelGenerator $modelGenerator */
        $modelGenerator = app(ModelGenerator::class);
        $rules = $modelGenerator->generateRules();

        $relations = array_keys($this->relations);

        foreach ($this->config->fields as $field) {
            if ($field->isPrimary) {
                continue;
            }

            if (in_array($field->name, $this->removeFields)) {
                continue;
            }

            $fieldData = "'".$field->name."' => ".'$this->faker->';
            $rule = null;
            if (isset($rules[$field->name])) {
                $rule = $rules[$field->name];
            }

            switch (explode(',', strtolower($field->dbType))[0]) {
                case 'integer':
                case 'unsignedinteger':
                case 'smallinteger':
                case 'biginteger':
                case 'unsignedbiginteger':
                    $fakerData = in_array($field->name, $relations) ? ':relation' : $this->getValidNumber($rule, 999);
                    break;
                case 'long':
                case 'double':
                case 'float':
                case 'decimal':
                    $fakerData = $this->getValidNumber($rule, 999);
                    break;
                case 'string':
                case 'char':
                    $lower = strtolower($field->name);
                    $firstChar = substr($lower, 0, 1);
                    if (str_contains($lower, 'email')) {
                        $fakerData = 'email';
                    } elseif ($firstChar == 'f' && str_contains($lower, 'name')) {
                        $fakerData = 'firstName';
                    } elseif (($firstChar == 's' || $firstChar == 'l') && str_contains($lower, 'name')) {
                        $fakerData = 'lastName';
                    } elseif (str_contains($lower, 'phone')) {
                        $fakerData = "numerify('0##########')";
                    } elseif (str_contains($lower, 'password')) {
                        $fakerData = "lexify('1???@???A???')";
                    } elseif (strpos($lower, 'address')) {
                        $fakerData = 'address';
                    } else {
                        if (!$rule) {
                            $rule = 'max:255';
                        }
                        $fakerData = $this->getValidText($rule);
                    }
                    break;
                case 'text':
                    $fakerData = $rule ? $this->getValidText($rule) : 'text(500)';
                    break;
                case 'boolean':
                    $fakerData = 'boolean';
                    break;
                case 'date':
                    $fakerData = "date('Y-m-d')";
                    break;
                case 'datetime':
                case 'timestamp':
                    $fakerData = "date('Y-m-d H:i:s')";
                    break;
                case 'time':
                    $fakerData = "date('H:i:s')";
                    break;
                case 'enum':
                    $fakerData = 'randomElement('.
                        GeneratorFieldsInputUtil::prepareValuesArrayStr($field->htmlValues).
                        ')';
                    break;
                default:
                    $fakerData = 'word';
            }

            if ($fakerData == ':relation') {
                $fieldData = $this->getValidRelation($field->name);
            } else {
                $fieldData .= $fakerData;
            }

            $fields[] = $fieldData;
        }

        return implode(','.apiforge_nl_tab(1, 3), $fields);
    }

    /**
     * Generates a valid number based on applicable model rule.
     *
     * @param string $rule The applicable model rule
     * @param int    $max  The maximum number to generate.
     *
     * @return string
     */
    public function getValidNumber($rule = null, $max = PHP_INT_MAX): string
    {
        if ($rule) {
            $max = $this->extractMinMax($rule, 'max') ?? $max;
            $min = $this->extractMinMax($rule, 'min') ?? 0;

            return "numberBetween($min, $max)";
        } else {
            return 'randomDigitNotNull';
        }
    }

    /**
     * Generates a valid relation if applicable
     * This method assumes the related field primary key is id.
     */
    public function getValidRelation(string $fieldName): string
    {
        $relation = $this->relations[$fieldName]['relation'];
        $variable = Str::camel($relation);

        return "'".$fieldName."' => ".'$'.$variable.'->id';
    }

    /**
     * Generates a valid text based on applicable model rule.
     *
     * @param string $rule The applicable model rule.
     */
    public function getValidText($rule = null): string
    {
        if ($rule) {
            $max = $this->extractMinMax($rule, 'max') ?? 4096;
            $min = $this->extractMinMax($rule) ?? 5;

            if ($max < 5) {
                //faker text requires at least 5 characters
                return "lexify('?????')";
            }
            if ($min < 5) {
                //faker text requires at least 5 characters
                $min = 5;
            }

            return 'text('.'$this->faker->numberBetween('.$min.', '.$max.'))';
        } else {
            return 'text';
        }
    }

    /**
     * Extracts min or max rule for a laravel model.
     */
    public function extractMinMax($rule, $t = 'min')
    {
        $i = strpos($rule, $t);
        $e = strpos($rule, '|', $i);
        if ($e === false) {
            $e = strlen($rule);
        }
        if ($i !== false) {
            $len = $e - ($i + 4);

            return substr($rule, $i + 4, $len);
        }

        return null;
    }

    /**
     * Generate valid model so we can use the id where applicable
     * This method assumes the model has a factory.
     */
    public function getRelationsBootstrap(): array
    {
        $text = '';
        $uses = '';
        foreach ($this->relations as $field => $data) {
            if (in_array($field, $this->removeFields)) {
                continue;
            }
            $relation = $data['relation'];
            $qualifier = $data['model_class'];
            $variable = Str::camel($relation);
            $model = Str::studly($relation);
            if (!empty($text)) {
                $text .= apiforge_nl_tab(1, 2);
            }
            $text .= !mb_strpos($text, $model) ? '$'.$variable.' = '.$model.'::first();'.
            apiforge_nl_tab(1, 2).
            'if (! $'.$variable.') {'.
            apiforge_nl_tab(1, 3).
            '$'.$variable.' = '.$model.'::factory()->create();'.
            apiforge_nl_tab(1, 2).'}'.apiforge_nl() : null;
            $uses .= !mb_strpos($uses, $qualifier ) ? apiforge_nl()."use $qualifier;" : null;
        }

        return [
            'text' => $text,
            'uses' => $uses,
        ];
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->config->commandComment('Factory file deleted: '.$this->fileName);
        }
    }
}

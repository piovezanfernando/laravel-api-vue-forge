<?php

namespace PiovezanFernando\LaravelApiVueForge\Generators;

use Illuminate\Support\Str;
use Symfony\Component\VarExporter\VarExporter;

class FrontQuasarGenerator extends BaseGenerator
{
    private string $pageFileName;
    private string $gridFileName;
    private string $formFileName;
    private string $modelFileName;

    public function __construct()
    {
        parent::__construct();

        $this->path = $this->config->paths->frontend . '/';
        $this->pageFileName = $this->config->modelNames->name . '.vue';
        $this->gridFileName = $this->config->modelNames->name . 'Grid.vue';
        $this->formFileName = $this->config->modelNames->name . 'Form.vue';
        $this->modelFileName = $this->config->modelNames->camel . '.ts';
    }

    public function generate()
    {
        $this->generatePage();
        $this->generateGrid();
        $this->generateForm();
        $this->generateModel();
        $this->registerMenuAndRoute();
    }

    protected function generatePage()
    {
        $templateData = view('laravel-api-vue-forge::front.page', $this->variables())->render();

        g_filesystem()->createFile($this->path . '/src/pages/' . $this->pageFileName, $templateData);

        $this->config->commandComment(apiforge_nl() . 'Page : ' . $this->pageFileName);
        $this->config->commandInfo($this->pageFileName);
    }

    protected function generateGrid()
    {
        $templateData = view('laravel-api-vue-forge::front.grid', $this->variables())->render();

        g_filesystem()->createFile($this->path . '/src/components/grids/' . $this->gridFileName, $templateData);

        $this->config->commandComment(apiforge_nl() . 'Grid : ' . $this->gridFileName);
        $this->config->commandInfo($this->gridFileName);
    }

    protected function generateForm()
    {
        $templateData = view('laravel-api-vue-forge::front.form', $this->variables())->render();

        g_filesystem()->createFile($this->path . '/src/components/forms/' . $this->formFileName, $templateData);

        $this->config->commandComment(apiforge_nl() . 'Form : ' . $this->formFileName);
        $this->config->commandInfo($this->formFileName);
    }

    protected function generateModel()
    {
        $templateData = view('laravel-api-vue-forge::front.model', $this->variables())->render();

        g_filesystem()->createFile($this->path . '/src/models/' . $this->modelFileName, $templateData);

        $this->config->commandComment(apiforge_nl() . 'Model : ' . $this->modelFileName);
        $this->config->commandInfo($this->modelFileName);
    }

    public function variables(): array
    {
        return [
            'fieldOptions' => implode(',' . apiforge_nl_tab(1, 2), $this->generateFields()),
            'columns' => implode(',' . apiforge_nl_tab(1, 2), $this->generateGridColumn()),
            'rowData' => implode(',' . apiforge_nl_tab(1, 2), $this->generateRowData()),
            'fieldsForm' => implode('' . apiforge_nl_tab(1, 0), $this->generateFieldForm()),
            'fieldsModel' => implode(';' . apiforge_nl(1) . apiforge_tab(2), $this->generateFieldsModel()) . ',',
        ];
    }

    public function generateFields(): array
    {
        $fields = [];
        $fields[] = '{label: "Ativo", value: "is_active", type: "boolean"}';
        $fields[] = '{label: "Data de Criação", value: "created_at", type: "date"}';
        $fields[] = '{label: "Data de atualização", value: "updated_at", type: "date"}';
        foreach ($this->config->fields as $field) {
            if ($field->isFillable) {
                $fields[] = '{label: "' . Str::title(str_replace('_', ' ', $field->name)) . '",'
                    . ' value: "' . $field->name . '", type: "' . $this->getTableType($field->dbType) . '"}';
            }
        }

        return $fields;
    }

    public function generateGridColumn(): array
    {
        $columns = [];
        foreach ($this->config->fields as $field) {
            if ($field->isFillable) {
                $columns[] =
                    "{"
                    . "name: '" . $field->name . "', "
                    . "label: '" . $this->getPrettyName($field->name) . "', "
                    . "field: '" . $field->name . "', "
                    . "sortable: true, "
                    . "align: 'left', "
                    . ($this->getTableType($field->dbType) == 'string' ? 'style: generateColumnStyle(150)' : '')
                    . "}";
            }
        }
        $columns[] = "{name: 'is_active', label: 'Ativo', field: 'is_active', align: 'left' }";
        $columns[] = "{name: 'edit', label: 'Editar', align: 'center' }";

        return $columns;
    }

    public function generateRowData(): array
    {
        $fields = [];
        $fields[] = 'id: undefined';

        foreach ($this->config->fields as $field) {
            if ($field->isFillable) {
                $type = $this->getTableType($field->dbType) == 'number' ? 0 : "''";
                $fields[] =
                    $field->name . ": " . $type;
            }
        }
        $fields[] = "created_at: ''";
        $fields[] = "updated_at: ''";
        $fields[] = "deleted_at: ''";
        return $fields;
    }

    public function getPrettyName(string $name): string
    {
        return Str::title(str_replace('_', ' ', $name));
    }

    public function getTableType(string $type, bool $isModel = false): string
    {
        return match ($type) {
            'string', 'text' => 'string',
            'boolean' => 'boolean',
            'datetime' => $isModel ? 'string' : 'date',
            default => 'number',
        };
    }

    public function generateFieldForm(): array
    {
        $fields = [];
        $fields[] = $this->getInputIdField('id', 'Id');

        foreach ($this->config->fields as $field) {
            if ($field->isFillable) {
                $matchingFields = array_filter($this->config->relations, function ($relation) use ($field) {
                    return $relation->inputs[1] === $field->name;
                });

                if (!$matchingFields) {
                    $fields[] = $this->getInputField($field->name, $this->getPrettyName($field->name));
                }
            }
        }

        return $fields;
    }

    public function getInputIdField(string $field, string $label): string
    {
        return <<<INPUT
            <q-input 
               v-model="formData.$field"
               label="$label"
               outlined
               bg-color="slate-50"
               label-color="slate-400"
               class="q-mb-md modern-input"
               disable
               readonly
            />
        INPUT;
    }
    public function getInputField(string $field, string $label): string
    {
        //TODO: adicionar o rules no input com as devidas validações
        return <<<INPUT
            <q-input 
               v-model="formData.$field"
               label="$label"
               outlined
               clearable
               bg-color="white"
               label-color="slate-500"
               class="q-mb-md modern-input"
               :disable="formStore.isDisable"
               :readonly="formStore.isDisable"
            />
        INPUT;
    }

    public function generateFieldsModel(): array
    {
        $fields = [];
        foreach ($this->config->fields as $field) {
            $fields[] = $field->name . ': ' . $this->getTableType($field->dbType, true);
        }
        return $fields;
    }

    protected function registerMenuAndRoute()
    {
        $menuConfigPath = $this->path . 'src/assets/menu-config.json';

        $assetsDir = dirname($menuConfigPath);
        if (!file_exists($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }

        $menuConfig = [];
        if (file_exists($menuConfigPath)) {
            $jsonContent = file_get_contents($menuConfigPath);
            $menuConfig = json_decode($jsonContent, true) ?? [];
        }

        $newItem = [
            'label' => $this->config->modelNames->name,
            'icon' => 'list_alt',
            'route' => '/' . $this->config->modelNames->camel,
            'component' => $this->config->modelNames->name . '.vue',
            'name' => $this->config->modelNames->camel,
            'page' => $this->config->modelNames->name . 'Page'
        ];

        $exists = false;
        foreach ($menuConfig as $index => $item) {
            if (isset($item['name']) && $item['name'] === $newItem['name']) {
                $menuConfig[$index] = $newItem; // Update existing
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $menuConfig[] = $newItem;
        }

        file_put_contents(
            $menuConfigPath,
            json_encode($menuConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->config->commandComment(apiforge_nl() . 'Menu config updated: menu-config.json');
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path . 'src/components/forms/', $this->formFileName)) {
            $this->config->commandComment('Form file deleted: ' . $this->formFileName);
        }
    }
}

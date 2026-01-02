export interface {{ $config->modelNames->name }}Data {
  {!! $fieldsModel !!}
}

export interface {{ $config->modelNames->name }}Response {
  data: {{ $config->modelNames->name }}Data;
}
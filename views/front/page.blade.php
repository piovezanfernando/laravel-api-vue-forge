<template>
  <q-page class="q-pa-md">
    <div class="column q-gutter-y-lg">
      <grid-{{ $config->modelNames->camel }} />
    </div>
  </q-page>
</template>

<script lang="ts">
  import Grid{{ $config->modelNames->name }} from 'components/grids/{{ $config->modelNames->name }}Grid.vue';

  export default {
    name: '{{ $config->modelNames->name }}Page',
    components: { Grid{{ $config->modelNames->name }} },
    setup() {
      return {};
    }
  };
</script>

<style>
  table tr:hover {
    background: #F9F9FA;
  }

  body {
    background-color: #F9F9FA;
  }
</style>
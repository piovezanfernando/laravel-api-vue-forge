<template>
    <div>
        <q-page padding>
            <default-filter v-if="showFilter" grid-name="{{ $config->modelNames->name }}" :fields="fieldOptions"></default-filter>
            <grid-{{ $config->modelNames->camel }} class="q-pt-md"></grid-{{ $config->modelNames->camel }}>
            <q-page-sticky position="bottom-right" :offset="[25,25]">
                <q-btn fab :icon="icon" color="primary" @click="showFilter = !showFilter"/>
            </q-page-sticky>
        </q-page>
    </div>
</template>

<script lang="ts">
  import Grid{{ $config->modelNames->name }} from 'components/registers/{{ $config->modelNames->name }}Grid.vue';
  import { dom } from 'quasar';
  import { computed, ref } from 'vue';
  import DefaultFilter from 'components/filters/DefaultFilter.vue';

  export default {
    name: '{{ $config->modelNames->name }}Page',
    components: { DefaultFilter, Grid{{ $config->modelNames->name }} },
    setup() {
      const filter = ref('');
      const { width } = dom;
      const showFilter = ref(false);
      const icon = computed(() => showFilter.value ? 'close' : 'search');
      const fieldOptions = [
        {!! $fieldOptions !!}
      ];

      return {
        filter,
        drawerLeft: width,
        showFilter,
        icon,
        fieldOptions
      };
    }
  };
</script>

<style>
    table tr:hover {
        background: #F9F9FA;
    }
    body{
        background-color: #F9F9FA;
    }
</style>
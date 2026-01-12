<template>
  <div class="q-pa-md">
    <q-card flat class="glass-card shadow-sm border-slate-200" style="min-height: 80vh">

      <!-- Top Tab Bar -->
      <div class="bg-slate-50 border-bottom rounded-t-xl">
        <q-tabs
          v-model="activeTab"
          class="text-slate-500"
          active-color="indigo"
          indicator-color="indigo"
          align="left"
          dense
          inline-label
          outside-arrows
          mobile-arrows
        >
          <!-- Main Grid Tab -->
          <q-tab name="main" icon="list_alt" label="Listagem" class="text-weight-bold q-px-lg" />

          <!-- Dynamic Open Tabs -->
          @verbatim
          <q-tab
            v-for="tab in tabs"
            :key="tab.id"
            :name="tab.id"
            class="q-px-md text-capitalize group"
          >
            <div class="row items-center no-wrap">
              <q-icon :name="tab.isNew ? 'add_circle' : 'edit_note'" size="xs" class="q-mr-sm" />
              <div class="ellipsis" style="max-width: 150px;">{{ tab.label }}</div>
              <q-btn
                icon="close"
                flat
                round
                dense
                size="xs"
                color="slate-400"
                class="q-ml-sm opacity-50 hover:opacity-100 transition-opacity"
                @click.stop="closeTab(tab.id)"
              />
            </div>
          </q-tab>
          @endverbatim
        </q-tabs>
      </div>

      <!-- Tab Content -->
      <q-tab-panels v-model="activeTab" animated transition-prev="fade" transition-next="fade" class="bg-transparent">

        <!-- LISTING PANEL -->
        <q-tab-panel name="main" class="q-pa-none column full-height">
          <!-- Header & Filter Wrapper -->
          <div class="q-pa-md">
            <!-- Header Row: Title & Actions -->
            <div class="full-width row items-center justify-between q-mb-md">
              <div class="row items-center">
                <div class="text-h6 text-weight-bold text-slate-800">{{ $config->modelNames->name }}</div>
                @verbatim
                <q-badge color="indigo-50" text-color="indigo-600" class="q-ml-sm text-weight-bold">
                  {{ responseData?.data?.meta?.total || responseData?.data?.total || 0 }} registros
                </q-badge>
                @endverbatim
              </div>

              <div class="row q-gutter-sm">
                <q-btn
                  outline
                  color="indigo"
                  icon="filter_list"
                  label="Filtros"
                  @click="showFilter = !showFilter"
                  class="rounded-lg bg-indigo-50"
                />
                <q-btn label="Novo" icon="add" @click="openNew()" unelevated color="primary" class="rounded-lg" />
              </div>
            </div>

            <!-- Filter Component Area -->
            <q-slide-transition>
              <div v-show="showFilter" class="q-mb-md">
                <default-filter
                  grid-name="{{ $config->modelNames->name }}"
                  :fields="columns.filter(c => c.name !== 'edit')"
                />
              </div>
            </q-slide-transition>
          </div>

          <!-- Table Container - Flex Grow to take available space -->
          <div class="col overflow-hidden">
            <q-table
              :rows="responseData?.data?.data || []"
              :columns="columns"
              row-key="id"
              :loading="loading"
              selection="none"
              :pagination="{ page: 1, rowsPerPage: 0 }"
              hide-pagination
              flat
              class="bg-transparent fit q-pa-md"
              table-header-class="text-slate-500 text-uppercase text-weight-bold"
            >

              <template #body-cell-is_active="props">
                <q-td :props="props">
                  <q-chip
                    :color="props.row.is_active ? 'emerald-50' : 'rose-50'"
                    :text-color="props.row.is_active ? 'emerald-600' : 'rose-600'"
                    :label="props.row.is_active ? 'Ativo' : 'Inativo'"
                    size="sm"
                    class="text-weight-bold"
                  />
                </q-td>
              </template>

              <template v-slot:body-cell-edit="props">
                <q-td :props="props" align="center">
                  <q-btn flat round color="indigo-600" icon="edit_note" size="sm" @click="onEditClick(props.row)" />
                </q-td>
              </template>
            </q-table>
          </div>

          <!-- Legacy Pagination Area -->
          <div class="row justify-end q-pa-md border-top bg-slate-50-opt col-auto">
            <div class="col-auto flex items-center q-mr-md">
              <q-pagination
                v-model="pagination.page"
                color="indigo-600"
                :max="responseData?.data?.meta?.last_page || responseData?.data?.last_page"
                size="md"
                @update:model-value="changePage"
                boundary-numbers
                :max-pages="6"
                direction-links
                flat
                active-design="unelevated"
                active-color="primary"
                active-text-color="white"
              />
            </div>
            <div class="col-auto" style="min-width: 100px;">
              <q-select
                v-model="pagination.rowsPerPage"
                :options="pageOptions"
                label="Por página"
                dense
                borderless
                options-dense
                @update:model-value="changeRowPerPage"
                class="text-caption text-slate-600"
              />
            </div>
          </div>
        </q-tab-panel>

        <!-- FORM PANELS (Dynamic) -->
        @verbatim
        <q-tab-panel
          v-for="tab in tabs"
          :key="tab.id"
          :name="tab.id"
          class="q-pa-lg scroll"
          style="height: calc(100vh - 180px)"
        >
          <div class="row items-center justify-between q-mb-lg border-bottom q-pb-md">
            <div>
              <div class="text-h6 text-weight-bold text-slate-800">{{ tab.label }}</div>
              <div class="text-caption text-slate-400">
                {{ tab.isNew ? 'Preencha os dados abaixo para criar um novo registro.' : 'Edite as informações do registro selecionado.' }}
              </div>
            </div>
            <div class="row q-gutter-sm">
              <q-btn label="Voltar para Lista" flat color="slate-500" @click="activeTab = 'main'" />
              <q-btn label="Fechar Aba" outline color="slate-400" @click="closeTab(tab.id)" />
            </div>
          </div>

          <!-- Content Form -->
          <div class="row justify-center">
            <div class="col-12 col-md-10 col-lg-8">
                <default-form
                  :model="tab.data"
        @endverbatim
                  routeApi='{{ $config->modelNames->camelPlural }}/'
                  component="{{ $config->modelNames->name }}Form"
        @verbatim
                  @saved="onSaved(tab.id)"
                  @deleted="onSaved(tab.id)"
                  @cancelled="activeTab = 'main'"
                ></default-form>
            </div>
          </div>
        </q-tab-panel>
        @endverbatim

      </q-tab-panels>
    </q-card>
  </div>
</template>

<script lang="ts">
  import defaultService from 'src/api/default';
  import { useFormStore } from 'stores/form';
  import { ref } from 'vue';
  import { useQuasar } from 'quasar';
  import { ApiResponse, DefaultRequest } from 'src/models/default';
  import { {{ $config->modelNames->name }}Data, {{ $config->modelNames->name }}Response } from 'src/models/{{ $config->modelNames->camel }}';
  import DefaultForm from 'components/forms/defaultForm.vue';
  import DefaultFilter from 'components/filters/DefaultFilter.vue';
  import { useFilterStore } from 'stores/filter';

  interface TabData {
    id: string | number;
    label: string;
    data: {{ $config->modelNames->name }}Data | any;
    isNew: boolean;
  }

  export default {
    name: 'Grid{{ $config->modelNames->name }}',
    components: { DefaultForm, DefaultFilter },
    setup() {
      const generateColumnStyle = (maxWidth) => {
        return `max-width: ${maxWidth}px; overflow: hidden; text-overflow: ellipsis;`;
      };
      const showFilter = ref(false);
      const columns = [
        {!! $columns !!}
      ];
      const activeTab = ref<string | number>('main');
      const tabs = ref<TabData[]>([]);
      const pageOptions = [5, 15, 30, 50, 100];

      const pagination = ref < DefaultRequest > ({
        sortBy: '',
        descending: false,
        page: 1,
        rowsPerPage: 10,
        fields: ''
      });
      const formStore = useFormStore();
      const filterStore = useFilterStore();
      const $q = useQuasar();
      const responseData = ref < ApiResponse < {{ $config->modelNames->name }}Response > | null > (null);
      const loading = ref(true);
      const { getAll, remove } = defaultService();
      const paginationModel = pagination.value;

      filterStore.$subscribe((_, state) => {
        if (state.filter !== null) {
          fetchFromServer();
        }
      });
      const fetchFromServer = () => {
        loading.value = true;
        let filter = filterStore.getFilterName();
        // Pass pagination.value directly
        getAll < {{ $config->modelNames->name }}Response > ('{{ $config->modelNames->camelPlural }}', filter, paginationModel, (res: ApiResponse<{{ $config->modelNames->name }}Response>) => {
          responseData.value = res;
          setTimeout(() => {
            loading.value = false;
          }, 500);
        });

      };

      // Tab Management Logic
      const openNew = () => {
        const newId = 'new-' + Date.now();
        tabs.value.push({
          id: newId,
          label: 'Novo Registro',
          data: { is_active: true },
          isNew: true
        });
        activeTab.value = newId;
        formStore.setIsDisable(false);
      };

      const onEditClick = (row: {{ $config->modelNames->name }}Data) => {
        const existingTab = tabs.value.find(t => t.id === row.id);
        if (existingTab) {
          activeTab.value = existingTab.id;
          return;
        }

        tabs.value.push({
          id: row.id!,
          label: row.name || `Registro #${row.id}`,
          data: { ...row },
          isNew: false
        });
        activeTab.value = row.id!;
        formStore.setIsDisable(true);
      };

      const closeTab = (id: string | number) => {
        const index = tabs.value.findIndex(t => t.id === id);
        if (index > -1) {
          tabs.value.splice(index, 1);
          if (activeTab.value === id) {
            activeTab.value = tabs.value.length > 0 ? tabs.value[Math.max(0, index - 1)].id : 'main';
          }
        }
      };

      const onSaved = (tabId: string | number) => {
        fetchFromServer();
        closeTab(tabId);
      };

      // Initial Load
      fetchFromServer();

      const changePage = (page: number) => {
        paginationModel.page = page;
        fetchFromServer();
      };

      const changeRowPerPage = (rows: number) => {
        paginationModel.rowsPerPage = rows;
        fetchFromServer();
      };

      const onPagination = (params: any) => {
        Object.assign(paginationModel, params);
        fetchFromServer();
      };

      return {
        activeTab,
        tabs,
        openNew,
        closeTab,
        onSaved,
        loading,
        pagination,
        columns,
        responseData,
        pageOptions,
        paginationModel,
        onEditClick,
        changePage,
        changeRowPerPage,
        onPagination,
        filterStore,
        formStore,
        showFilter
      };
    }
  };
</script>

<style lang="scss" scoped>
.rounded-t-xl {
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
}
.border-bottom {
  border-bottom: 1px solid #e2e8f0;
}
.border-top {
  border-top: 1px solid #e2e8f0;
}
.bg-slate-50-opt {
  background-color: #f8fafc;
}
.transition-opacity {
  transition: opacity 0.2s;
}
.group:hover .opacity-0 {
  opacity: 100;
}
</style>
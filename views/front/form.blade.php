<template>
{!! $fieldsForm !!}
</template>
<script setup lang="ts">
  import { useFormStore } from 'stores/form';
  import { defineProps, ref, toRefs } from 'vue';

  const props = defineProps(['model']);

  const { model } = toRefs(props);
  const formData = ref(model.value);
  const formStore = useFormStore();
</script>

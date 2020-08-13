<style scoped>
</style>
<template>
  <div :class="`btn-group float-${isRtl? 'left':'right'} ltr`">
    <a :href="`${window.TwUtils.baseUrl}task/${task.id}/download${isMediaTask(task) ? '/html' : ''}`" :class="`btn btn-outline-gray btn-sm d-flex justify-content-center align-items-center`" download target="_blank">
      <span class="oi" data-glyph="data-transfer-download"></span>
      <span class="sr-only">
        {{__('download')}}
      </span>
      <span v-if="!isMediaTask(task)" :class="`oi float-${isRtl ? 'left' : 'right'} downloadType__icon--hint`" title="Excel" data-glyph="grid-three-up"></span>
      <span v-if="isMediaTask(task)" :class="`oi float-${isRtl ? 'left' : 'right'} downloadType__icon--hint`" title="HTML" data-glyph="globe"></span>
    </a>
    <button type="button" class="btn btn-outline-gray dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="fa fa-ellipsis-v"></i>
      <span class="sr-only">Toggle Dropdown</span>
    </button>
    <div class="dropdown-menu dropdown-menu-right">
      <a :href="`${window.TwUtils.baseUrl}task/${task.id}/download`" :class="`dropdown-item float-${isRtl? 'left':'right'} text-center`" download target="_blank">
        Excel
        <span class="oi float-right" data-glyph="grid-three-up"></span>
      </a>
      <div class="dropdown-divider"></div>
      <a :href="`${window.TwUtils.baseUrl}task/${task.id}/download/html`" :class="`dropdown-item float-${isRtl? 'left':'right'} text-center`" download target="_blank">
        HTML
        <span class="oi float-right" data-glyph="globe"></span>
      </a>
    </div>
  </div>
</template>
<script>

export default {
  components: {
  },
  props: {
    task: {
        type: Object,
    },
  },
  data() {
    return {
      
    }
  },
  mounted() {
    
  },
  methods: {
  },
}
</script>
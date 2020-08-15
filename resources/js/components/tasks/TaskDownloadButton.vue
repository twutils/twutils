<style scoped>
</style>
<template>
<a :href="`${window.TwUtils.baseUrl}task/${download.task_id}/download/..`" :class="`btn btn-outline-gray btn-sm d-flex justify-content-center align-items-center`" download target="_blank">
    <span class="oi" data-glyph="data-transfer-download"></span>
    <span class="sr-only">
    {{__('download')}}
    </span>
    <span
      :class="`oi float-${isRtl ? 'left' : 'right'} downloadType__icon--hint`"
      title="HTML"
      data-glyph="globe"
    ></span>
</a>
</template>
<script>

export default {
  components: {
  },
  props: {
    download: {
      type: Object,
    },
  },
  data () {
    return {

    }
  },
  mounted () {

  },
  methods: {
  },
}
</script>

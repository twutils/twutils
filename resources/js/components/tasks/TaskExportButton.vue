<style scoped>
</style>
<template>
<a
  @click="clicked"
  :href="taskExport.status === 'success' ? `${window.TwUtils.baseUrl}task/${taskExport.task_id}/export/${taskExport.id}`: '#'"
  :class="`taskExport__button taskExport__button--${taskExport.status} d-flex justify-content-center align-items-center`"
  :download="taskExport.status === 'success' ? '' : false"
  :target="taskExport.status === 'success' ? '_blank' : false"
  :title="__(`exports_desc.${taskExport.type}`)"
  data-toggle="tooltip"
  data-placement="bottom"
>
    <span class="sr-only">
    {{__('download')}}
    </span>
    <div class="taskExport__iconWrapper">
      <span
          v-for="(icon,index) in getExportTypeIcon(taskExport.type)"
          :class="`oi float-${isRtl ? 'left' : 'right'} taskExport__icon taskExport__icon--type index-${index}`"
          :title="taskExport.filename"
          :key="taskExport.id + 'icon' + index"
          :data-glyph="icon"
      ></span>
    </div>
    <span class="taskExport__desc">
      {{__(`exports.${taskExport.type}`)}}
    </span>
    <span v-if="taskExport.size && taskExport.size > 0" class="taskExport__fileSize">
      {{ filesize(taskExport.size, {round: 0}) }}
    </span>
</a>
</template>
<script>
import filesize from 'filesize'
import EventBus from '@/EventBus'

export default {
  components: {
  },
  props: {
    taskExport: {
      type: Object,
    },
  },
  data () {
    return {

    }
  },
  mounted () {
    this.tooltip()
  },
  methods: {
    filesize,
    clicked() {
      if (this.taskExport.status === 'success')
      {
        return ;
      }

      EventBus.fire('open-taskExports-modal', this.taskExport.type)
      return ;
    },
  },
}
</script>

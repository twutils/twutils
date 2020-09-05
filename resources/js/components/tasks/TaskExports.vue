<style scoped>
</style>
<template>
    <div class="taskExports">
        <div class="modal fade" tabindex="-1" role="dialog" id="taskExports">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <div :class="`modal-header ${isRtl ? 'rtl': 'ltr'}`">
                  <h5 :class="`modal-title`">
                    <span v-if="locale === 'en'">
                      ({{__(task.type)}}) Exports
                    </span>
                    <span v-if="locale === 'ar'">
                      تصديرات مهمة ({{__(task.type)}})
                    </span>
                  </h5>
                  <button type="button" :class="`close m-0 p-1 ${isRtl ? 'mr-auto':'ml-auto'}`" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div :class="`modal-body ${isRtl ? 'rtl': 'ltr'}`">
                  ....
                </div>
                <div :class="`modal-footer ${isRtl ? 'rtl': 'ltr'}`">
                  <button type="button" class="btn btn-soft-gray" data-dismiss="modal">
                    <span>{{__('close')}}</span>
                  </button>
                </div>
              </div>
            </div>
        </div>
        <div :class="`btn-group float-${isRtl? 'left':'right'} ${isRtl ? 'rtl': 'ltr'}`">
            <task-export-button
              :key="taskExport.id"
              :taskExport="taskExport"
              v-for="taskExport in featuredExports"
            ></task-export-button>
            <button
             type="button"
             class="taskExport__more"
             data-toggle="modal"
             data-target="#taskExports"
             aria-haspopup="true"
             aria-expanded="false"
            >
              <i class="fa fa-ellipsis-v"></i>
              <span class="sr-only">Toggle Dropdown</span>
            </button>
        </div>
    </div>
</template>
<script>
import taskExportButton from '@/components/tasks/TaskExportButton'
import EventBus from '@/EventBus'

export default {
  components: {
    taskExportButton,
  },
  props: {
    task: {
      type: Object,
    },
    exports: {
      type: Array,
    },
  },
  data () {
    return {

    }
  },
  mounted () {
    EventBus.listen('open-taskExports-modal', type => {
      $(this.$el).find('#taskExports').modal('show')
    })
  },
  methods: {
  },
  computed: {
    featuredExports () {
      if (this.exports.length <= 2) { return this.exports }

      return this.exports
        .filter(x => [`excel`, `htmlEntities`,].includes(x.type))
        .slice(-2)
    },
  },
}
</script>

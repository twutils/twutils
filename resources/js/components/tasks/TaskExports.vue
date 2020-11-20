<style scoped>
</style>
<template>
    <div class="taskExports">
      <portal to="modal">
        <div class="modal fade" tabindex="-1" role="dialog" id="taskExports">
            <div class="modal-dialog modal-xl" role="document">
              <div class="modal-content">
                <div :class="`modal-header ${isRtl ? 'rtl': 'ltr'}`">
                  <h5 :class="`modal-title`">
                    <span v-if="locale === 'en'">
                      (<span class="taskType--in-modal-title taskExport__title__type">{{__(task.type)}}</span>) Exports
                    </span>
                    <span v-if="locale === 'ar'">
                      تصديرات مهمة (<span class="taskExport__title__type">{{__(task.type)}}</span>)
                    </span>
                  </h5>
                  <button type="button" :class="`close m-0 p-1 ${isRtl ? 'mr-auto':'ml-auto'}`" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div :class="`modal-body ${isRtl ? 'ltr': 'ltr'}`">
                  <task-exports-details :exports="exports" :task="task"></task-exports-details>
                </div>
                <div :class="`modal-footer ${isRtl ? 'rtl': 'ltr'}`">
                  <button type="button" class="btn btn-soft-gray" data-dismiss="modal">
                    <span>{{__('close')}}</span>
                  </button>
                </div>
              </div>
            </div>
        </div>
      </portal>
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
import taskExportsDetails from '@/components/tasks/TaskExportsDetails'
import EventBus from '@/EventBus'
import maxBy from 'lodash/maxBy'

export default {
  components: {
    taskExportButton,
    taskExportsDetails,
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
      systemExports: Object.keys(window.TwUtils.exports),
    }
  },
  mounted () {
    EventBus.listen(`open-taskExports-modal`, type => {
      $(`#taskExports`).modal(`show`)
    })

    let shouldRefresh = true

    const refreshTask = () => {
      if (!shouldRefresh) { return false }

      setTimeout(function () {
        EventBus.fire(`refresh-task`, refreshTask)
      }, 5000)
    }

    this.$nextTick(x => {
      $(`#taskExports`).on(`shown.bs.modal`, () => {
        shouldRefresh = true
        refreshTask()
      })

      $(`#taskExports`).on(`hidden.bs.modal`, () => {
        shouldRefresh = false
      })
    })
  },
  methods: {
  },
  computed: {
    featuredExports () {
      if (this.exports.length <= 2) { return this.exports }

      return [`html`, `excel`, `htmlEntities`,]
        .map(exportType => {
          return maxBy(this.exports.filter(x => x.type === exportType), `id`)
        })
        .filter(x => x)
        .slice(-2)
    },
  },
}
</script>

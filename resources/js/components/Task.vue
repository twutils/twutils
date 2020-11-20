<style lang="scss">
  @import "../../sass/global.scss";

  .task {
    background: rgba($body-background, 0.8);
  }
</style>
<template>
  <div class="task task__container">
    <div class="row p-2">
      <div v-if="!isLocal" :class="`col-12 ${isRtl ? 'float-right rtl':''}`">
        <router-link :to="{path: '/'}" class="task__button--routeLink">« {{__('tasks')}}</router-link><br>
      </div>
    </div>
    <div :class="`row text-center ${isRtl ? 'rtl':''}`" v-if="task != null">
      <div class="col col-sm-12">
        <div :class="`border-radius-1rem d-flex flex-column flex-sm-row justify-content-between alert alert-${task.status === 'completed' ? 'success' : (task.status === 'broken' ? 'danger' : 'primary')} text-${isRtl ? 'right' : 'left' }`">
          <div class="d-flex flex-row flex-sm-column  justify-content-between flex-1">
            <div>
              {{__(task.type)}}
              <hr />
            </div>
            <small v-text="taskStatusDesc"></small>
          </div>
          <div class="d-flex flex-row flex-sm-column  justify-content-between align-items-center" style="min-width: 150px;">
            <strong>{{__(task.status)}}</strong>
            <from-now
                :value="task.created_at"
                :title="moment(task.created_at).format('YYYY-MMM-DD hh:mm A')"
                data-placement="bottom"
                :has-tooltip="true"
            ></from-now>
          </div>
          <div class="d-flex flex-row flex-sm-column justify-content-between align-items-end" style="min-width: 150px;">
            <button v-if="!isLocal && task.managed_by_task_id === null" @click="remove" class="btn btn-outline-danger btn-sm">
              <span class="oi" data-glyph="trash"></span>
              <span class="sr-only">
                {{__('remove')}}
              </span>
            </button>
            <task-exports :task="task" :exports="task.exports" v-if="!isLocal"></task-exports>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-12">
        <hr>
        <component v-if="task != null" :is="`task-${task.componentName}`" :task="task"></component>
      </div>
    </div>
    <portal v-if="task != null" to="modal">
      <div :class="`modal fade ${isRtl ? 'rtl' : 'ltr'}`" id="removeTask">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">
                {{__('remove')}}
                <span class="taskType--in-modal-title">
                  ({{__(task.type)}})
                </span>
              </h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                <span class="sr-only">Close</span>
              </button>
            </div>
            <div class="modal-body">
              <div v-if="!removing">
                <p v-if="locale === 'en'">
                  Are you sure you want to remove this task?
                </p>
                <p v-if="locale === 'ar'">
                  هل أنت متأكد من رغبتك بحذف هذه المهمة؟
                </p>
              </div>
              <div v-if="removing">
                <div v-if="errors.length == 0">
                  <p v-if="locale === 'en'">
                    Removing..
                  </p>
                  <p v-if="locale === 'ar'">
                    جاري الحذف..
                  </p>
                </div>
              </div>
              <div class="mt-2 alert alert-danger" v-for="error in errors" v-if="errors.length > 0" v-text="error"></div>
            </div>
            <div v-if="!removing" class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">
                  <span v-if="locale === 'en'">
                    Cancel
                  </span>
                  <span v-if="locale === 'ar'">
                    تراجع
                  </span>
              </button>
              <button @click="doRemove" class="btn btn-danger">
                  <span v-if="locale === 'en'">
                    Remove
                  </span>
                  <span v-if="locale === 'ar'">
                    حذف
                  </span>
                <span class="oi" data-glyph="trash"></span>
              </button>
            </div>
          </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
      </div><!-- /.modal -->
    </portal>
  </div>
</template>

<script>
import taskFetchlikes from '@/components/tasks/TaskFetchlikes'
import taskFetchentitieslikes from '@/components/tasks/TaskFetchEntitiesLikes'
import taskFetchentitiesusertweets from '@/components/tasks/taskFetchEntitiesUsertweets'
import taskFetchusertweets from '@/components/tasks/TaskFetchusertweets'
import taskFetchfollowing from '@/components/tasks/TaskFetchfollowing'
import taskFetchfollowers from '@/components/tasks/TaskFetchfollowers'
import taskDestroylikes from '@/components/tasks/TaskDestroylikes'
import taskDestroytweets from '@/components/tasks/TaskDestroytweets'
import taskManagedtask from '@/components/tasks/TaskManagedtask'
import taskExports from '@/components/tasks/TaskExports'
import EventBus from '../EventBus'
import fromNow from './FromNow'
import get from 'lodash/get'

export default {
  props: [`id`, ],
  components: {
    fromNow,
    taskFetchlikes,
    taskFetchentitieslikes,
    taskFetchentitiesusertweets,
    taskFetchusertweets,
    taskFetchfollowing,
    taskFetchfollowers,
    taskDestroylikes,
    taskDestroytweets,
    taskManagedtask,
    taskExports,
  },
  data () {
    return {
      task: null,
      removing: false,
      errors: [],
    }
  },
  mounted () {
    window.preventHideLoadingOnReady = true

    EventBus.listen(`refresh-task`, routerData => {
      this.$nextTick(x => this.fetchTask(x => {
        if (typeof routerData === `function`) {
          routerData()
        }
      }, { hideLoading: true, }))
    })

    EventBus.listen(`force-refresh-task`, routerData => {
      this.removing = false
      this.errors = []
      this.task = null
      this.$nextTick(this.fetchTask)
    })

    if (window.TwUtils.tasks != null) {
      this.task = window.TwUtils.tasks[0]
    } else {
      this.fetchTask()
    }
  },
  methods: {
    remove () {
      $(`#removeTask`).modal(`show`)
    },
    doRemove () {
      this.removing = true
      this.errors = []
      axios.delete(`${window.TwUtils.apiBaseUrl}tasks/${this.id}`)
        .then((response) => {
          this.removing = false
          if (!response.data.ok) { return this.errors.push(`There is an error removing this task`) }
          $(`#removeTask`).modal(`hide`)
          this.$router.push(`/`)
        })
        .catch((error) => {
          this.removing = false
          return this.errors.push(`There is an error removing this task`)
        })
    },
    fetchTask (callback = null, options = {}) {
      if (!options.hideLoading) {
        this.showLoading()
      }

      axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.id}`)
        .then((response) => {
          this.task = response.data
          this.tooltip()

          if (callback) {
            callback()
          }
        })
    },
  },
  computed: {
    taskStatusDesc () {
      const status = this.task.status

      if (status === `broken`) {
        const errors = get(this.task.extra, `break_response.errors`)
        let errorsFound = ``

        if (errors && Array.isArray(errors)) {
          errorsFound = ` ` + errors.map(x => x.message).join(`,`)
        }

        if (this.isRtl) {
          return `تمّت مقاطعة المهمة لوجود بعض الأخطاء. ` + errorsFound
        }

        return `Task interrupted.` + errorsFound
      }

      if (status === `completed`) {
        if (this.isRtl) {
          return `اكتملت المهمة بنجاح، بدأت: ` +
                  window.moment(this.task.created_at).format(`hh:mm A DD-MMM-YYYY`) +
                  `، وانتهت: ` +
                  window.moment(this.task.updated_at).format(`hh:mm A DD-MMM-YYYY`)
        }
        return `The task completed successfully, started: ` +
                window.moment(this.task.created_at).format(`hh:mm A YYYY-MMM-DD`) +
                `, ended: ` +
                window.moment(this.task.updated_at).format(`hh:mm A YYYY-MMM-DD`)
      }

      if (this.isMediaTask(this.task) && status === `staging`) {
        if (this.isRtl) {
          return `تم نسخ التغريدات بنجاح. جاري تحميل ملفات الوسائط..`
        }

        return `Tweets are collected successfully. Downloading media files..`
      }

      if (status === `staging`) {
        if (this.isRtl) {
          return `تم نسخ قائمة المُتابَعين. جاري العمل على جمع بعض المعلومات الإضافية لعرضها لك`
        }

        return `Followings list are collected. Still collecting some informations about the list to show it to you`
      }

      if (this.isRtl) {
        return `جاري العمل..`
      }

      return `Working...`
    },
  },
}
</script>

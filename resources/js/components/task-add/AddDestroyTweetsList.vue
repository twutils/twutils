<style lang="scss" scoped>
</style>
<template>
<div class="container">
  <div class="row">
    <div class="col-12 mb-3">
      <span style="border-bottom: 1px solid #eee; padding: 0.5rem;">
        <slot name="header" />
      </span>
    </div>
  </div>
  <div v-if="isLoading" class="row">
    <div class="col-12">
      {{loadingDestroyTweetsLang}}
    </div>
  </div>
  <div :class="`row`" style="position: relative;">
    <write-access-warning v-if="showWriteAccessWarning && ! exploringMode" @activateExploringMode="exploringMode = true"></write-access-warning>
    <add-destroy-tweets-list-options :class="`${!userHavePrivilige(taskDefinition.scope) && ! exploringMode ? 'taskAdd__disabled':''}`" v-if="!isLoading" v-model.sync="options"></add-destroy-tweets-list-options>
    <div v-if="!isLoading" :class="`container mb-b ${!userHavePrivilige(taskDefinition.scope) && ! exploringMode ? 'taskAdd__disabled':''}`">
      <div class="row">
        <div :class="`col-12 text-${isRtl ? 'left':'right'}`">
          <button @click="confirm" class="m-auto btn-soft-red btn shadow">
            <span data-glyph="bolt" class="oi"></span>
            {{__('remove')}}
          </button>
        </div>
      </div>
    </div>
  </div>
  <portal to="modal">
    <div class="modal fade" tabindex="-1" role="dialog" id="confirmDestroy">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div :class="`modal-header ${isRtl ? 'rtl': 'ltr'}`">
            <h5 :class="`modal-title`">
              <span v-if="locale === 'en'">
                Are you sure?
              </span>
              <span v-if="locale === 'ar'">
                هل أنت متأكد؟
              </span>
            </h5>
            <button type="button" :class="`close m-0 p-1 ${isRtl ? 'mr-auto':'ml-auto'}`" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div :class="`modal-body ${isRtl ? 'rtl': 'ltr'}`">
            <slot name="confirmBody" />
          </div>
          <div :class="`modal-footer ${isRtl ? 'rtl': 'ltr'}`">
            <button type="button" class="btn btn-soft-gray" data-dismiss="modal">
              <span>{{__('close')}}</span>
            </button>
            <button data-action="startButton" @click="confirmed" type="button" class="btn btn-primary">
              <slot name="confirmButton" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </portal>
</div>
</template>

<script>
import tasksList from '../TasksList'
import addDestroyTweetsListOptions from './AddDestroyTweetsListOptions'
import writeAccessWarning from './WriteAccessWarning'

const data = {
  isLoading: false,
  showWriteAccessWarning: false,
  exploringMode: false,
  options: {
    retweets: false,
    tweets: false,
    replies: false,
    start_date: null,
    end_date: null,
  },
}

const clonedData = JSON.parse(JSON.stringify(data, true))

export default {
  components: {
    tasksList,
    writeAccessWarning,
    addDestroyTweetsListOptions,
  },
  props: {
    loadingDestroyTweetsLang: {
      type: String,
    },
    taskDefinition: {
      type: Object,
    },
    taskEndpoint: {
      type: String,
    },
  },
  data () {
    return { ...clonedData, }
  },
  mounted () {
    this.showWriteAccessWarning = ! this.userHavePrivilige(this.taskDefinition.scope)
  },
  methods: {
    confirm () {
      $(`#confirmDestroy`).modal(`show`)
    },
    confirmed () {
      if ( this.exploringMode )
      {
        return alert('Action prevented.. You said you are just exploring :D')
      }

      this.isLoading = true
      this.showLoading()
      this.closeConfirm()

      axios.post(`${window.TwUtils.apiBaseUrl}${this.taskEndpoint}/`, { settings: { ...this.options, }, })
        .then(resp => {
          this.hideLoading()
          this.$router.push(`/`)
        })
        .catch(err => {
          this.hideLoading()
        })
    },
    closeConfirm () {
      $(`#confirmDestroy`).modal(`hide`)
    },
  },
}
</script>

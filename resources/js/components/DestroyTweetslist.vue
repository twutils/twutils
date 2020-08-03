<style lang="scss">
</style>
<template>
  <div class="my-3 row">
    <div class="col-12" style="height: 60px" v-if="loading">
      <img :src="loadingGifSrc" class="m-auto loadingGif">
    </div>
    <div v-if="!loading && relatedTask == null" :class="`col-12 ${isRtl ? 'rtl':''}`">
      <div class="row">
        <div class="col-12">
          We can't retrieve more info about this operation..
        </div>
      </div>
    </div>
    <div v-if="!loading && relatedTask != null" :class="`col-12 ${isRtl ? 'rtl':''}`">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <slot name="header"></slot>: {{relatedTaskRemovedTweetsLength}} / {{removeScopeCount}}
            </div>
            <div class="card-body">
              <h5>
                <span
                  data-toggle="popover"
                  :data-content="__('selected_tweets_source_desc')"
                  data-placement="bottom"
                  data-trigger="hover"
                  data-container="body"
                  style="opacity: 0.5;"
                  class="text-secondary"
                >
                  <i class="fa fa-info-circle" aria-hidden="true"></i>
                </span>
                {{__('selected_tweets_source')}}:
              </h5>
              <tasks-list-item :selectionMode="false" :task.sync="relatedTask"></tasks-list-item>
              <h5>
                {{__('options')}}:
              </h5>
              <destroy-tweets-options-view :task="task"></destroy-tweets-options-view>
              <h5>
                {{__('removed')}}:
              </h5>
              <tweets-list-item v-for="(tweet,index) in relatedTaskRemovedTweets" :tweet="tweet" :key="tweet.id" :isChild="true"></tweets-list-item>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import get from 'lodash/get'
import tasksListItem from './TasksListItem'
import tweetsListItem from './TweetsListItem'
import destroyTweetsOptionsView from './DestroyTweetsOptionsView'

export default {
  beforeDestroy () {
    $(this.$el).find(`[data-toggle=popover]`).popover(`dispose`)
  },
  components: {
    tasksListItem,
    tweetsListItem,
    destroyTweetsOptionsView,
  },
  props: [`task`, ],
  data () {
    return {
      loading: true,
      relatedTask: null,
      relatedTaskRemovedTweets: [],
    }
  },
  computed: {
    relatedTaskRemovedTweetsLength () {
      return this.relatedTaskRemovedTweets.length
    },
    removeScopeCount () {
      return get(this.task.extra, `removeScopeCount`)
    },
  },
  mounted () {
    this.fetchTaskData(1)
    this.fetchRelatedTask()
  },
  methods: {
    fetchTaskData (page) {
      axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.task.id}/data?page=${page}`)
        .then(resp => {
          const currentPage = resp.data.current_page
          const lastPage = resp.data.last_page

          this.relatedTaskRemovedTweets = this.relatedTaskRemovedTweets.concat(resp.data.data)

          if (currentPage !== lastPage) {
            this.fetchTaskData(currentPage + 1)
          }
        })
    },
    fetchRelatedTask () {
      axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.task.targeted_task_id}`)
        .then(response => {
          this.loading = false
          this.relatedTask = response.data
          this.hideLoading()
          this.$nextTick(x => {
            $(this.$el).find(`[data-toggle=popover]`).popover({ animation: true, })
          })
        })
        .catch(error => {
          this.loading = false
          this.hideLoading()
        })
    },
  },
}
</script>

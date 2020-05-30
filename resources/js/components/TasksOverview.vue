<style lang="scss" scoped>
</style>

<template>
  <div class="row taskTypes__container">
    <portal :key="task.id" v-for="task in tasks" :to="`task-counts-${task.id}`">
          <span v-if="task.baseName === 'fetchlikes' || task.baseName === 'fetchusertweets'"  :class="`${isRtl ? 'rtl':''}`">
            ({{intlFormat(task.likes_count) }} {{__('tweet')}})
          </span>
          <span v-if="['destroylikes', 'destroytweets', 'manageddestroytweets', 'manageddestroylikes'].includes(task.baseName)"  :class="`${isRtl ? 'rtl':''}`">
            ({{task.removedCount || '?' }} {{__('tweet')}})
          </span>
          <span v-if="task.baseName === 'fetchfollowing'"  :class="`${isRtl ? 'rtl':''}`">
            ({{intlFormat(task.followings_count) }} {{__('following')}})
          </span>
          <span v-if="task.baseName === 'fetchfollowers'"  :class="`${isRtl ? 'rtl':''}`">
            ({{intlFormat(task.followers_count) }} {{__('followers')}})
          </span>
    </portal>
    <tasks-overview-item :tasks="tweetsTasks" :header="__('tweets')">
      <template slot="actions">
        <router-link
          :to="tasksToAdd.find(x => x.type === 'userTweets').route"
          tag="button"
          :class="`taskType__button taskType__button--${isRtl ? 'right':'left'} flex-1 btn btn-soft-gray d-flex flex-row justify-content-between align-items-center`">
          <div class="taskType__button__innerContainer">
            <i class="fa fa-save"></i>
            <span>
              {{__('user_tweets')}}
            </span>
          </div>
          <div class="position-relative">
            <div style="width: 40px;" :data-glyph="`chevron-${isRtl ? 'left': 'right'}`" class="oi"></div>
            <div class="text-light-primary text-right taskType__button--hint">
              {{__('go_to_details')}}
            </div>
          </div>
        </router-link>
        <router-link
          :to="tasksToAdd.find(x => x.type === 'destroyTweets').route"
          tag="button"
          :class="`taskType__button taskType__button--${isRtl ? 'left':'right'} flex-1 btn btn-soft-red d-flex flex-row justify-content-between align-items-center`">
          <div class="taskType__button__innerContainer">
            <i class="fa fa-trash-o"></i>
            <span>
              {{__('destroy_tweets')}}
            </span>
          </div>
          <div class="position-relative">
            <div style="width: 40px;" :data-glyph="`chevron-${isRtl ? 'left': 'right'}`" class="oi"></div>
            <div class="text-light-primary text-right taskType__button--hint">
              {{__('go_to_details')}}
            </div>
          </div>
        </router-link>
      </template>
    </tasks-overview-item>

    <tasks-overview-item :tasks="likesTasks" :header="__('likes')">
      <template slot="actions">
        <router-link
          :to="tasksToAdd.find(x => x.type === 'backupLikes').route"
          tag="button"
          :class="`taskType__button taskType__button--${isRtl ? 'right':'left'} flex-1 btn btn-soft-gray d-flex flex-row justify-content-between align-items-center`">
          <div class="taskType__button__innerContainer">
            <i class="fa fa-save"></i>
            <span>
              {{__('backup_likes')}}
            </span>
          </div>
          <div class="position-relative">
            <div style="width: 40px;" :data-glyph="`chevron-${isRtl ? 'left': 'right'}`" class="oi"></div>
            <div class="text-light-primary text-right taskType__button--hint">
              {{__('go_to_details')}}
            </div>
          </div>
        </router-link>
        <router-link
          :to="tasksToAdd.find(x => x.type === 'destroyLikes').route"
          tag="button"
          :class="`taskType__button taskType__button--${isRtl ? 'left':'right'} flex-1 btn btn-soft-red d-flex flex-row justify-content-between align-items-center`">
          <div class="taskType__button__innerContainer">
            <i class="fa fa-trash-o"></i>
            <span>
              {{__('destroy_likes')}}
            </span>
          </div>
          <div class="position-relative">
            <div style="width: 40px;" :data-glyph="`chevron-${isRtl ? 'left': 'right'}`" class="oi"></div>
            <div class="text-light-primary text-right taskType__button--hint">
              {{__('go_to_details')}}
            </div>
          </div>
        </router-link>
      </template>
    </tasks-overview-item>

    <tasks-overview-item :tasks="followingTasks" :header="__('following')">
      <template slot="actions">
        <router-link
          :to="tasksToAdd.find(x => x.type === 'fetchFollowing').route"
          tag="button"
          class="taskType__button taskType__button--left taskType__button--right flex-1 btn btn-soft-gray d-flex flex-row justify-content-between align-items-center">
          <div class="taskType__button__innerContainer">
            <i class="fa fa-save"></i>
            <span>
              {{__('fetch_following')}}
            </span>
          </div>
          <div class="position-relative">
            <div style="width: 40px;" :data-glyph="`chevron-${isRtl ? 'left': 'right'}`" class="oi"></div>
            <div class="text-light-primary text-right taskType__button--hint">
              {{__('go_to_details')}}
            </div>
          </div>
        </router-link>
      </template>
    </tasks-overview-item>

    <tasks-overview-item :tasks="followersTasks" :header="__('followers')">
      <template slot="actions">
        <router-link
          :to="tasksToAdd.find(x => x.type === 'fetchFollowers').route"
          tag="button"
          class="taskType__button taskType__button--left taskType__button--right flex-1 btn btn-soft-gray d-flex flex-row justify-content-between align-items-center">
          <div class="taskType__button__innerContainer">
            <i class="fa fa-save"></i>
            <span>
              {{__('fetch_followers')}}
            </span>
          </div>
          <div class="position-relative">
            <div style="width: 40px;" :data-glyph="`chevron-${isRtl ? 'left': 'right'}`" class="oi"></div>
            <div class="text-light-primary text-right taskType__button--hint">
              {{__('go_to_details')}}
            </div>
          </div>
        </router-link>
      </template>
    </tasks-overview-item>
  </div>
</template>

<script>
import EventBus from '../EventBus'
import tasksOverviewItem from './TasksOverviewItem'
import tasksToAdd from '../tasks'

export default {
  components: {
    tasksOverviewItem,
  },
  beforeDestroy () {
    clearInterval(this.refreshInterval)
  },
  props: {
    selectionMode: {
      type: Boolean,
      default: false,
    },
    onSelect: {
      type: Function,
      default: () => {},
    },
    selectableTasks: {
      type: Array,
      default: () => [],
    },
  },
  data () {
    return {
      tasks: [],
      refreshInterval: null,
      loaded: false,
      busy: false,
      tasksToAdd,
    }
  },
  mounted () {
    this.refreshTasks()
    EventBus.listen(`refresh-tasks`, this.fetchTasks)
  },
  methods: {
    refreshTasks () {
      this.fetchTasks()
      this.refreshInterval = setInterval(this.fetchTasks, 5000)
    },
    fetchTasks () {
      if (this.busy) { return }

      this.busy = true
      axios.get(`${window.TwUtils.baseUrl}api/tasks`)
        .then((response) => {
          this.tasks = response.data.filter(x => x.managed_by_task_id === null)

          EventBus.fire(`loaded-tasks`, response.data)
          this.loaded = true
          this.busy = false
        })
        .catch((error) => {
          this.loaded = true
          this.busy = false
        })
    },
  },
  computed: {
    tweetsTasks () {
      return this.tasks.filter(x => [`fetchusertweets`, `fetchentitiesusertweets`, `destroytweets`, `manageddestroytweets`,].includes(x.baseName))
    },
    likesTasks () {
      return this.tasks.filter(x => [`fetchlikes`, `fetchentitieslikes`, `destroylikes`, `manageddestroylikes`,].includes(x.baseName))
    },
    followingTasks () {
      return this.tasks.filter(x => [`fetchfollowing`, ].includes(x.baseName))
    },
    followersTasks () {
      return this.tasks.filter(x => [`fetchfollowers`, ].includes(x.baseName))
    },
  },
}
</script>

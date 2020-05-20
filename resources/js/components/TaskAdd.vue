<style>
.taskAdd {
  background: $body-background;
}
</style>
<template>
  <div>
    <tasks-view>
      <transition name="slideFade">
          <div v-if="show" class="w-100 taskAdd">
            <router-link :to="{path: '/'}" class="task__button--routeLink">« {{__('tasks')}}</router-link>
            <hr>
            <component v-if="tasksToAdd.filter(x => x.type === type).length > 0" :is="type"></component>
          </div>
      </transition>
    </tasks-view>
  </div>
</template>

<script>
import tasksView from './TasksView'
import backupLikes from './task-add/BackupLikes'
import userTweets from './task-add/UserTweets'
import destroyLikes from './task-add/DestroyLikes'
import destroyTweets from './task-add/DestroyTweets'
import fetchFollowing from './task-add/FetchFollowing'
import fetchFollowers from './task-add/FetchFollowers'
import EventBus from '../EventBus'
import tasksToAdd from '../tasks'

export default {
  components: {
    tasksView,
    backupLikes,
    userTweets,
    destroyLikes,
    destroyTweets,
    fetchFollowing,
    fetchFollowers,
  },
  props: [`type`, ],
  watch: {
    show (newValue) {
      this.$nextTick(this.$el.parentElement.scrollIntoView())
    },
  },
  data () {
    return {
      show: false,
      tasksToAdd,
    }
  },
  mounted () {
    this.show = true
    EventBus.fire(`off-transition`)
    EventBus.listen(`addTask-open`, () => {
      this.show = false
      this.$nextTick(() => {
        this.show = true
      })
    })
  },
}
</script>

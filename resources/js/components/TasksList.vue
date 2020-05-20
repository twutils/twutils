<style lang="scss" scoped>
.loadingGif {
  width: 100px;
}

</style>

<template>
<div class="col-sm-12 tasksList tasksList__container">
  <tasks-list-item :selectionMode="selectionMode" :onSelect="onSelect" :task.sync="task" :index.sync="index" v-for="(task, index) in (selectionMode ? selectableTasks : tasks)" :key="index"></tasks-list-item>
  <div :class="`text-center ${isRtl?'rtl':''}`" v-if="tasks.length === 0">
    <span v-if="loaded">
      {{__('no_tasks')}}
    </span>
    <span v-else>
      <img :src="loadingGifSrc" class="m-auto loadingGif">
    </span>
  </div>
</div>
</template>

<script>
import EventBus from '../EventBus'
import tasksListItem from './TasksListItem'

export default {
  components: {
    tasksListItem,
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
          this.tasks = response.data
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
}
</script>

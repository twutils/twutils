<style lang="scss">
.managedTask {
    border: 1px dashed black;
    width: 300px;
    margin: 0.5rem auto 1rem auto;
    padding: 1rem;
    border-radius: 1rem;
}
</style>
<template>
<div class="row">
  <div
    v-if="['manageddestroytweets', 'manageddestroylikes'].includes(task.baseName)"
    :class="`col-12 ${isRtl ? 'rtl' : 'ltr'}`"
  >
    <h5>
      {{__('options')}}:
    </h5>
    <destroy-tweets-options-view :task="task"></destroy-tweets-options-view>
  </div>
	<div v-for="(managedTask, index) in managedTasks" :class="`col-12 col-sm-6 offset-sm-3  text-center ${isRtl ? 'rtl' : 'ltr'}`">
		<span class="badge badge-pill badge-dark">
			{{index + 1}}
		</span>
    <div
      v-if="
        task.baseName === 'manageddestroytweets' && managedTask.baseName === 'fetchusertweets' ||
        task.baseName === 'manageddestroylikes' && managedTask.baseName === 'fetchlikes'
      "
      style="opacity: 0.5;"
      :class="`alert alert-secondary text-justify m-3`"
    >
      <i class="fa fa-info-circle" aria-hidden="true"></i>
      {{__('selected_tweets_source_desc')}}
    </div>
		<tasks-list-item
			:selectionMode="false"
			:task.sync="managedTask"
			:index.sync="index"
			:key="index"
		></tasks-list-item>
	</div>
</div>
</template>

<script>
import tasksListItem from '@/components/TasksListItem'
import destroyTweetsOptionsView from '@/components/DestroyTweetsOptionsView'

export default {
  components: {
  	tasksListItem,
    destroyTweetsOptionsView,
  },
  data () {
  	return {
  		managedTasks: [],
  	}
  },
  props: [`task`, ],
  mounted () {
    if (window.TwUtils.managedTasks != null) {
      this.managedTasks = window.TwUtils.managedTasks
    } else {
	  	this.fetchManagedTasks()
    }
  },
  methods: {
  	fetchManagedTasks () {
  		axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.task.id}/managedTasks`)
  		.then(resp => {
		    this.hideLoading()
  			this.managedTasks = resp.data
  		})
  	},
  },
}
</script>

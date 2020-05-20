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

	<div v-for="(task, index) in managedTasks" class="col-12 col-sm-6 offset-sm-3  text-center">
		<span class="badge badge-pill badge-dark">
			{{index + 1}}
		</span>
		<tasks-list-item
			:selectionMode="false"
			:task.sync="task"
			:index.sync="index"
			:key="index"
		></tasks-list-item>
	</div>
</div>
</template>

<script>
import tasksListItem from '@/components/TasksListItem'

export default {
  components: {
  	tasksListItem,
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

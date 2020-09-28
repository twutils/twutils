<style scoped>
</style>
<template>
<div class="col-12 col-lg-6 taskTypeContainer">
  <div class="text-center taskType__header">
    <span class="taskType__header--label">{{header}}</span>
  </div>
  <div class="taskType d-flex">
    <div class="taskType__historyContainer">
      <label class="taskType__historyLabel">
        {{__('history')}}:
      </label>
      <div class="taskType__history">
        <div v-if="tasks.length === 0" class="taskType__emptyLabel">
        	{{__('no_previous_tasks')}}
        </div>
        <template v-for="task in tasks">
      <router-link class="row no-gutters m-0 taskOverviewItem__container mb-2 text-decoration-none" :to="{name: 'task.show', params: {id: task.id}}">
			  <div class="col-sm-6 col-6 order-sm-1 order-1 taskOverviewItem__typeLabel--container">
			      <span>
			        {{ __(task.type) }}
			      </span>&nbsp;
			      <portal-target tag="span" :name="`task-counts-${task.id}`"></portal-target>
			  </div>
			  <div class="col-sm-3 col-6 order-sm-2 order-3 text-muted">
			    <from-now
			      :value="task.created_at"
			      :title="moment(task.created_at).format('YYYY-MMM-DD hh:mm A')"
			      data-placement="bottom"
			      :has-tooltip="true"
			    ></from-now>
			  </div>
			  <div :class="`col-sm-2 col-6 order-sm-3 order-2 taskOverviewItem__status taskOverviewItem__status--${task.status === 'completed' ? 'success' : (task.status === 'broken' ? 'error' : '')}`">
		  	    {{ startCase(__(task.status)) }}
			  </div>
			  <div class="col-sm-1 col-6 order-sm-4 order-4 text-center">
			      <span data-glyph="magnifying-glass" class="oi"></span>
			  </div>
      </router-link>
        </template>
      </div>
    </div>
    <div class="taskType__actions d-flex">
    	<slot name="actions"></slot>
    </div>
  </div>
</div>
</template>
<script>
import fromNow from './FromNow'
import groupBy from 'lodash/groupBy'

export default {
  components: {
    fromNow,
  },
  props: {
  	header: {
  		type: String,
  		default: ``,
  	},
  	tasks: {
  		type: Array,
  		default: () => [],
  	},
  },
  data () {
    return {

    }
  },
  mounted () {

  },
  methods: {
    groupBy,
  },
}
</script>

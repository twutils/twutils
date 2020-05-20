<style lang="scss">
.tasksListItem {
    border: 1px solid #ccc;
    padding: 1rem;
    border-radius: 1rem;
    margin: 0.8rem 0rem;
}
</style>

<template>
<div class="tasksListItem">
    <portal :to="`task-counts-${task.id}`">
          <span v-if="task.baseName === 'fetchlikes' || task.baseName === 'fetchusertweets'"  :class="`${isRtl ? 'rtl':''}`">
            ({{task.likes_count }} {{__('tweet')}})
          </span>
          <span v-if="['destroylikes', 'destroytweets'].includes(task.baseName)"  :class="`${isRtl ? 'rtl':''}`">
            ({{task.removedCount }} {{__('tweet')}})
          </span>
          <span v-if="task.baseName === 'fetchfollowing'"  :class="`${isRtl ? 'rtl':''}`">
            ({{task.followings_count }} {{__('following')}})
          </span>
          <span v-if="task.baseName === 'fetchfollowers'"  :class="`${isRtl ? 'rtl':''}`">
            ({{task.followers_count }} {{__('followers')}})
          </span>
    </portal>
    <div :class="`w-100 d-flex justify-content-between flex-row ${isRtl ? 'rtl' :''}`">
        <span>
          <router-link v-if="selectionMode" :to="{name: 'task.show', params: {id: task.id}}">
            {{__(task.type)}}
          <portal-target :name="`task-counts-${task.id}`"></portal-target>
          </router-link>
          <span v-if="!selectionMode">{{__(task.type)}}</span>
          <portal-target v-if="!selectionMode" :name="`task-counts-${task.id}`"></portal-target>
        </span>
        <span class="text-muted">
          {{moment(task.created_at).fromNow()}}
        </span>
    </div>
    <div :class="`mt-2 w-100 d-flex justify-content-between flex-row ${isRtl ? 'rtl' :''} align-items-center`">
        <span class="text-muted">{{__(task.status)}}</span>
        <router-link v-if="!selectionMode" class="tasksList__button--routeLink" :to="{name: 'task.show', params: {id: task.id}}">
            <span class="oi" data-glyph="magnifying-glass"></span>
            {{__('details')}}
          </router-link>
          <button v-if="selectionMode" class="tasksList__button--routeLink" @click="onSelect(task)">
            <span class="oi" data-glyph="magnifying-glass"></span>
            {{__('chose')}}
          </button>
    </div>
</div>
</template>

<script>
import EventBus from '../EventBus'

export default {
  beforeDestroy () {
  },
  props: {
    selectionMode: {
      type: Boolean,
      default: false,
    },
    task: {
      type: Object,
    },
    index: {
      type: Number,
    },
    onSelect: {
      type: Function,
      default: () => {},
    },
  },
  data () {
    return {
    }
  },
  mounted () {

  },
  methods: {

  },
}
</script>

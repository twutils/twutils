<style lang="scss" scoped>
</style>
<template>
  <div :class="`layoutContainer ${isRtl ? 'rtl':''}`">
    <div id="sidebar" :class="`sidebar p-1 d-flex flex-column collapse ${isRtl ? 'rtl':''}`">
      <sidebar class="mt-2"></sidebar>
      <tasks-controls class="flex-1" ref="tasks-controls"></tasks-controls>
    </div>
    <div class="mainContent tasksView__container hasDividerAfter-sm">
     <div class="row m-0 mx-2 tasksView__columnContainer" style="">
       <slot>
         <tasks-overview ref="tasks-overview"></tasks-overview>
         <portal to="footerOfTasksView"></portal>
       </slot>
     </div>
     <portal-target name="footerOfTasksView"></portal-target>
    </div>
    <div class="overlay"></div>
  </div>
</template>

<script>
import tasksControls from './TasksControls'
import tasksOverview from './TasksOverview'
import sidebar from './Sidebar'
import EventBus from '../EventBus'

export default {
  components: {
    tasksControls,
    tasksOverview,
    sidebar,
  },
  data () {
    return {
    }
  },
  watch: {
  },
  mounted () {
    EventBus.listen(`loaded-tasks`, tasks => {

    })

    $(`#sidebar`).on(`shown.bs.collapse`, function () {
      $(`.overlay`).addClass(`active`)
    })

    $(`#sidebar`).on(`hidden.bs.collapse`, function () {
      $(`.overlay`).removeClass(`active`)
    })

    const $menu = $(`#sidebar`)

    $(document).mouseup(e => {
      if (!$menu.is(e.target) && // if the target of the click isn't the container...
       $menu.has(e.target).length === 0) // ... nor a descendant of the container
      {
        $menu.collapse(`hide`)
      }
    })
  },
  props: [],
}
</script>

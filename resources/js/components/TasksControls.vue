<style scoped lang="scss">
  a {
    color: black;
  }
  .list-group-item {
    margin-left: -1rem;
    margin-right: -1rem;
    background: transparent;
  }
  .tasksControl__link {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .tasksControl__link.isActive {
    color: #23629f;
    font-weight: 600;
  }
</style>
<template>
<div :class="`tasksControl tasksControl__container ${isRtl ? 'rtl':''}`">
  <div class="list-group">
    <template v-for="(category, categoryIndex) in uniqueCategories">
      <div v-if="!tasksToAdd.find(x => x.category === category).invisible" class="list-group-item m-0 tasksControl__category" v-text="__(category)"></div>
      <router-link @click="turnOffTransition" :to="task.route" tag="a" v-for="(task,index) in tasksToAdd.filter(x => x.category == category)" :key="categoryIndex + '' + index" class="list-group-item m-0 text-decoration-none">
        <div :class="`hoverUnderlineFromCenter tasksControl__link ${isActiveTask(task) ? 'isActive':''}`">
          <i v-if="task.icon" :class="task.icon" />
          <span :style="userHavePrivilige(task.scope) ? '':`text-decoration: line-through;`">
            {{__(task.langButton)}}
          </span>
          <span :class="`float-${isRtl?'left':'right'} taskControl__headerIcon`"><span class="oi" :data-glyph="`chevron-${isRtl?'left':'right'}`"></span></span>
        </div>
      </router-link>
    </template>
  </div>
</div>
</template>

<script>
import uniq from 'lodash/uniq'
import EventBus from '../EventBus'

import tasksToAdd from '../tasks'

export default {
  data () {
    return {
      tasksToAdd,
    }
  },
  mounted () {

  },
  computed: {
    uniqueCategories () {
      const categories = this.tasksToAdd.map(x => x.category)
      return uniq(categories)
    },
    isActiveTask (task) {
      return (task) => {
        const currentRoute = this.$route
        const currentRouteName = currentRoute.name
        const currentRoutePath = currentRoute.path
        const currentRouteType = currentRoute.params.type

        return currentRoutePath === task.route.path || (currentRouteType === task.type && currentRouteName === task.route.name)
      }
    },
  },
  methods: {
    turnOffTransition () {
      EventBus.fire(`off-transition`)
    },
  },
}
</script>

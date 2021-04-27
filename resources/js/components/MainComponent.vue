<style lang="scss">
.flip-enter-active {
  transition: all .2s cubic-bezier(0.55, 0.085, 0.68, 0.53); //ease-in-quad
}

.flip-leave-active {
  transition: all .25s cubic-bezier(0.25, 0.46, 0.45, 0.94); //ease-out-quad
}

.flip-enter, .flip-leave-to {
  transform: scaleY(0) translateZ(0);
  opacity: 0;
}
</style>
<template>
  <div class="container-fluid m-0 p-0">
    <transition :name="transitionName" mode="out-in">
      <router-view></router-view>
    </transition>
    <portal-target v-if="env === 'test'" name="modal" multiple></portal-target>
  </div>
</template>

<script>
import tasksView from './TasksView'
import EventBus from '../EventBus'

const env = process.env.NODE_ENV

export default {
  components: {
    tasksView,
  },
  data () {
    return {
      transitionName: `flip`,
      env,
    }
  },
  mounted () {
    EventBus.listen(`off-transition`, () => {
      this.transitionName = ``
    })
    EventBus.listen(`on-transition`, () => {
      this.transitionName = `flip`
    })
    if (![null, ``,].includes(window.TwUtils.returnUrl)) {
      this.$router.push({ path: window.TwUtils.returnUrl, })
    }
  },
  props: [],
}
</script>

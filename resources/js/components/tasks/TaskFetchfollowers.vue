<style lang="scss">
</style>
<template>
  <div class="my-3">
    <users-list :task="task" :refine-user-func="refineUser">
      <div :class="`col-12 my-2 ${isRtl ? 'rtl':''}`">
          <h4 v-if="task != null" :class="`float-${isRtl? 'right rtl':'left'}`">
            <span v-if="locale==='ar'">
              {{task.followers_count}}
              {{__('followers')}}
            </span>
            <span v-if="locale==='en'">
              {{__('followers')}}
              {{task.followers_count}}
            </span>
          </h4>
      </div>
    </users-list>
  </div>
</template>

<script>
import usersList from '@/components/UsersList'

export default {
  components: {
    usersList,
  },
  props: [`task`, ],
  data () {
    return {
    }
  },
  mounted () {
  },
  methods: {
    refineUser (follower) {
      const tweep = follower.tweep
      tweep.followed_by_me = follower.followed_by_me
      tweep.following_id = follower.id

      return tweep
    },
  },
}
</script>

<style lang="scss">
</style>
<template>
  <div class="my-3">
    <users-list :task="task" :refine-user-func="refineUser">
      <div :class="`col-12 my-2 ${isRtl ? 'rtl':''}`">
          <h4 v-if="task != null" :class="`float-${isRtl? 'right rtl':'left'}`">
            <span v-if="locale==='ar'">
              {{task.followings_count}}
              {{__('following')}}
            </span>
            <span v-if="locale==='en'">
              {{__('following')}}
              {{task.followings_count}}
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
    refineUser (following) {
      const tweep = following.tweep
      tweep.followed_by = following.followed_by
      tweep.following_id = following.id

      return tweep
    },
  },
}
</script>

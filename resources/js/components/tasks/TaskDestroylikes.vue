<style lang="scss">
</style>
<template>
    <destroy-tweetslist :task="task">
      <template slot="header">
        {{ __('removedLikes')}}
      </template>
    </destroy-tweetslist>
</template>

<script>
import destroyTweetslist from '@/components/DestroyTweetslist'

export default {
  components: {
    destroyTweetslist,
  },
  props: [`task`, ],
}
</script>

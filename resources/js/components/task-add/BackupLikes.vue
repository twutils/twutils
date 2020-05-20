<style lang="sass" scoped>

</style>
<template>
  <add-tweets-list :actionButton="__('backup_likes')" :loadingAddMessage="__('adding_backup_likes')" :endpoint="endpoint" :withMediaEndpoint="withMediaEndpoint" :ongoingUserTweets="__('ongoing_backup_likes')">
    <div slot="header">
      <strong>{{__('backup_likes')}}</strong> {{__('backup_likes_desc')}}
    </div>
  </add-tweets-list>
</template>

<script>
import addTweetsList from '@/components/task-add/AddTweetsList'

export default {
  components: {
    addTweetsList,
  },
  data () {
    return {
      endpoint: `likes`,
      withMediaEndpoint: `entitiesLikes`,
    }
  },
  mounted () {
  },
}
</script>

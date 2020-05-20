<style lang="sass" scoped>

</style>
<template>
  <add-tweets-list :actionButton="__('user_tweets')" :loadingAddMessage="__('adding_user_tweets')" :endpoint="endpoint" :withMediaEndpoint="withMediaEndpoint" :ongoingUserTweets="__('ongoing_user_tweets')">
    <div slot="header">
      <strong>{{__('user_tweets')}}</strong> {{__('user_tweets_desc')}}
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
      endpoint: `userTweets`,
      withMediaEndpoint: `entitiesUserTweets`,
    }
  },
  mounted () {
  },
}
</script>

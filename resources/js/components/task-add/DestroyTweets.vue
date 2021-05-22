<style lang="scss" scoped>
</style>
<template>
  <add-destroy-tweets-list
  ref="addDestroyTweetsList"
  :taskDefinition="taskDefinition"
  :taskEndpoint="`ManagedDestroyTweets`"
  :loadingDestroyTweetsLang="__('loading_destroy_tweets')"
  :header="__('destroy_tweets')"
  :confirmationText="__('destroy_tweets_confirmation')"
  :confirmationButton="__('destroy_likes_confirmation_button')"
  />
</template>

<script>
import tasksToAdd from '../../tasks'
import addDestroyTweetsList from './AddDestroyTweetsList'

const taskDefinition = tasksToAdd.find(x => x.type === `destroyTweets`)

export default {
  components: {
    addDestroyTweetsList,
  },
  data () {
    return { taskDefinition, }
  },
  mounted () {
  },
  methods: {
  },
}
</script>

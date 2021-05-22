<style lang="sass" scoped>
</style>
<template>
  <add-destroy-tweets-list
  ref="addDestroyTweetsList"
  :taskDefinition="taskDefinition"
  :taskEndpoint="`ManagedDestroyLikes`"
  :loadingDestroyTweetsLang="__('loading_destroy_likes')"
  :header="__('destroy_likes')"
  :confirmationText="__('destroy_likes_confirmation')"
  :confirmationButton="__('destroy_tweets_confirmation_button')"
  />
</template>

<script>
import tasksToAdd from '../../tasks'
import addDestroyTweetsList from './AddDestroyTweetsList'

const taskDefinition = tasksToAdd.find(x => x.type === `destroyLikes`)

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

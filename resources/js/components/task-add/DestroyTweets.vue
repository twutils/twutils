<style lang="scss" scoped>
</style>
<template>
  <add-destroy-tweets-list
  ref="addDestroyTweetsList"
  :taskDefinition="taskDefinition"
  :taskEndpoint="`ManagedDestroyTweets`"
  :loadingDestroyTweetsLang="__('loading_destroy_tweets')"
  >
    <template slot="header">
        <strong>{{__('destroy_tweets')}}</strong> {{__('destroy_tweets_desc')}}
    </template>
    <template slot="confirmBody">
      <p v-if="locale === 'en'">
        By Confirming, you will remove the tweets from your twitter account. This action can't be undone.
      </p>
      <p v-if="locale === 'ar'">
        بالموافقة، سوف تقوم بحذف التغريدات من حسابك في تويتر. هذا الإجراء غير قابل للتراجع.
      </p>
    </template>
    <template slot="confirmButton">
      <span v-if="locale === 'en'">
        Remove My Tweets
      </span>
      <span v-if="locale === 'ar'">
        حذف تغريداتي
      </span>
    </template>
  </add-destroy-tweets-list>
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

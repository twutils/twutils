<style lang="sass" scoped>
</style>
<template>
  <add-destroy-tweets-list
  ref="addDestroyTweetsList"
  :taskDefinition="taskDefinition"
  :taskEndpoint="`ManagedDestroyLikes`"
  :loadingDestroyTweetsLang="__('loading_destroy_likes')"
  >
    <template slot="header">
        <h2>{{__('destroy_likes')}}</h2>
    </template>
    <template slot="confirmBody">
      <p v-if="locale === 'en'">
        By Confirming, you will remove the likes from your twitter account (according to the range/scope defined). This action can't be undone.
      </p>
      <p v-if="locale === 'ar'">
        بالموافقة، سوف تقوم بحذف المفضّلة من حسابك في تويتر بحسب النطاق المحدد. هذا الإجراء غير قابل للتراجع.
      </p>
    </template>
    <template slot="confirmButton">
      <span v-if="locale === 'en'">
        Remove My Likes
      </span>
      <span v-if="locale === 'ar'">
        حذف مفضّلتي
      </span>
    </template>
  </add-destroy-tweets-list>
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

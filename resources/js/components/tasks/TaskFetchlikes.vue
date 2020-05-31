<style lang="scss">
</style>
<template>
  <div>
    <tweets-list :task="task">
      <div v-if="!isLocal" :class="`col-12 my-2 ${isRtl ? 'rtl':''}`">
        <h4 v-if="task != null" :class="`float-${isRtl? 'right':'left'}`">
          {{__('tweets')}}: {{task.likes_count}}
        </h4>
        <twitter-limitations-info
          v-if="shouldShowTwitterLimitations()"
          :task="task"
        ></twitter-limitations-info>
      </div>
    </tweets-list>
  </div>
</template>

<script>
import tweetsList from '@/components/TweetsList'
import twitterLimitationsInfo from '@/components/TwitterLimitationsInfo'

export default {
  components: {
    twitterLimitationsInfo,
    tweetsList,
  },
  props: [`task`, ],
  data () {
    return {
    }
  },
  mounted () {

  },
  methods: {
  },
}
</script>

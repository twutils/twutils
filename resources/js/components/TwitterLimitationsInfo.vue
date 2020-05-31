<style lang="scss">
</style>
<template>
<div class="d-inline-block mb-3">
  <button data-toggle="modal" data-target="#twitterLimitationInfo" type="button"  class="btn btn-default rounded-circle border mx-3">
    <i style="font-size: 1.3rem;" class="fa fa-info-circle" aria-hidden="true"></i>
  </button>
  <div class="modal fade" tabindex="-1" role="dialog" id="twitterLimitationInfo">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div :class="`modal-header ${isRtl ? 'rtl': 'ltr'}`">
          <h5 :class="`modal-title`">
            <span v-if="locale === 'en'">
              Something is wrong?
            </span>
            <span v-if="locale === 'ar'">
              هناك خطأٌ ما؟
            </span>
          </h5>
          <button type="button" :class="`close m-0 p-1 ${isRtl ? 'mr-auto':'ml-auto'}`" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div :class="`modal-body ${isRtl ? 'rtl': 'ltr'}`">
          <div class="alert alert-secondary">
            <template>
              <strong>
                Something is wrong?
              </strong>
              <p>
                On your profile, Twitter says you have ({{ intlFormat(countFromProfile) }} {{ tweetTypeFromTask.toLowerCase() }}),
                But here, it's only ({{intlFormat(tweetsCountFromTask)}} {{ tweetTypeFromTask.toLowerCase() }}).
              </p>
              <p>
                Here is what you should know:
                <ul>
                  <li>
                    The numbers in your Twitter profile is not correct! <br>
                    It's cached, and it's not updated properly. Twitter admits this themselves
                    <a href="https://help.twitter.com/en/using-twitter/missing-tweets" target="_blank">
                      here
                    </a>
                    .
                  </li>
                  <li>
                    Twitter gives third-party applications (like TwUtils) a limitation of retrieving only the recent ~3200 tweet.
                  </li>
                  <li>
                    If the tweet was Retweeted from or Tweeted by a suspended/closed/removed account,
                    you will not see it here.
                    This might also affect the numbers on your twitter profile.
                  </li>
                </ul>
              </p>
              <p>
                Here is what you can do:
                <ul>
                  <li>
                    Since tweets from closed accounts are not discoverable,
                    If the account was reactivated again, the tweets from that account will become discoverable again.
                    So maybe you want to redo this task again in a different time.
                  </li>
                  <li v-if="isUserTweetsTask(task)">
                    Use
                    <a href="https://twitter.com/search-advanced?f=live" target="_blank">
                      Twitter advanced search
                    </a>
                    feature to check for the existence of older tweets.
                  </li>
                  <li v-if="isUserTweetsTask(task)">
                    Download the official Twitter archive. Steps
                    <a href="https://help.twitter.com/en/managing-your-account/how-to-download-your-twitter-archive" target="_blank">
                      here
                    </a>
                  </li>
                </ul>
              </p>
            </template>
          </div>
        </div>
        <div :class="`modal-footer ${isRtl ? 'rtl': 'ltr'}`">
          <button type="button" class="btn btn-soft-gray" data-dismiss="modal">
            <span>{{__('close')}}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import get from 'lodash/get'
export default {
  components: {
  },
  props: {
    task: {
      type: Object,
    },
  },
  data () {
    return {
    }
  },
  computed: {
    countFromProfile () {
      return this.getCountFromProfileByTask(this.task)
    },
    tweetsCountFromTask () {
      return this.getTweetsCountFromTask(this.task)
    },
    tweetTypeFromTask () {
      if (this.isLikesTask(this.task)) {
        return this.isRtl ? `تغريدة مفضّلة` : `Like`
      }

      if (this.isUserTweetsTask(this.task)) {
        return this.isRtl ? `تغريدة` : `Tweet`
      }

      return ``
    },
  },
}
</script>

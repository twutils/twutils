<style>
.childTweet {
  max-width: 85%;
  border: 1px solid #e5e5e5;
  font-size: 0.8rem;
}
.tweetContainer {
    border: 1px dotted #cecece;
    margin-top: 1rem;
    margin-bottom: 1rem;
}
</style>
<template>
        <div :class="`row tweetContainer ${isChild ? 'childTweet' : ''}`">
          <div class="col-12 pt-3 mb-3 col-md-12 tweetHeaderMetaContainer">
            <a class="tweetAvatarLink d-flex flex-row justify-content-start flex-wrap text-left" :href="`https://twitter.com/${refinedTweet.tweep.screen_name}`"  target="_blank" rel="noopener">
              <img
                v-if="! isChild"
                style="width: 48px;"
                @error="avatarOnError"
                :src="userPlaceholder"
                :data-src="tweepAvatar"
                class="lazy rounded-circle"
              >
              <div class="ml-2 d-flex flex-row flex-wrap align-items-center">
                <span class="text-muted">@{{refinedTweet.tweep.screen_name}}</span>
                <span class="pl-1 text-link">{{refinedTweet.tweep.name}}</span>
              </div>
            </a>
            <a class="tweetLink text-right" target="_blank" rel="noopener" :title="tweet.tweet_created_at" :href="`https://twitter.com/${tweet.tweep.screen_name}/status/${tweet.id_str}`">
                <span v-if="! isChild">{{moment(tweet.tweet_created_at).format('YYYY-MMM-DD')}}</span>
                <span v-if="isChild">{{moment(refinedTweet.created_at || refinedTweet.tweet_created_at, 'dd MMM DD HH:mm:ss ZZ YYYY','en').format('YYYY-MMM-DD')}}</span>
            </a>
          </div>
            <div class="col-12 col-md-12 text-center">
              <div :class="`tweetText text-${isRtlText(refinedTweet.text) ? 'right dir-rtl' : 'left'}`" v-html="parseTweetText(refinedTweet.text)"></div>
              <div v-if="refinedTweet.media" class="d-flex flex-wrap tweetImagesContainer">
                <tweet-media :isChild="isChild" :tweet="refinedTweet" :index="index" :media="media" v-for="(media, index) in refinedTweet.media" :key="index"></tweet-media>
              </div>
              <self v-if="refinedTweet.is_quote_status && refinedTweet.quoted_status" :tweet="quotedStatus" :isChild="true" class="childQuotedStatus"></self>
              <div class="d-flex pt-2 justify-content-around align-items-center">
                <div class="text-center text-muted px-2">
                  <span class="oi" data-glyph="heart" :style="tweetPivot.favorited ? `color: red;` : ''"></span>
                  {{intlFormat(refinedTweet.favorite_count)}}
                </div>
                <div class="text-center text-muted px-2">
                  <span class="fa fa-retweet" :style="tweetPivot.retweeted ? `color: green;`:''"></span>
                  {{intlFormat(refinedTweet.retweet_count)}}
                </div>
              </div>
              <!-- <hr v-if="! isChild"> -->
            </div>
        </div>
</template>

<script>
import get from 'lodash/get'
import TweetMedia from './TweetMedia'

export default {
  components: {
    TweetMedia,
    // eslint-disable-next-line
    self: () => import('./TweetsListItem'),
  },
  props: {
    tweet: {
      default: () => { return {} },
    },
    isChild: {
      default: false,
      type: Boolean,
    },
  },
  data () {
    return {

    }
  },
  mounted () {

  },
  methods: {
    avatarOnError (e) {
      const el = e.target

      if (el.isRemote) {
        el.src = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNc+B8AAkcBolySrScAAAAASUVORK5CYII=`
        return
      }

      el.isRemote = true
      el.src = this.refinedTweet.tweep.avatar
    },
    buildTweet (tweet) {
      return {
        ...tweet,
        tweep: tweet.user ? {
          avatar: tweet.user.profile_image_url_https,
          screen_name: tweet.user.screen_name,
          name: tweet.user.name,
          id_str: tweet.user.id_str,
        } : {},
        tweet_created_at: tweet.created_at,
        text: tweet.full_text,
        pivot: this.tweet.pivot,
      }
    },
    sanitizeTweet(tweet) {
      let tweetText = tweet.text || tweet.full_text

      if (tweet.quoted_status_permalink)
      {
        tweetText = tweetText.replaceAll(tweet.quoted_status_permalink.url, tweet.quoted_status_permalink.expanded)
      }

      if (get(tweet, 'extended_entities.media'))
      {
        get(tweet, 'extended_entities.media').map(media => {
          tweetText = tweetText.replaceAll(media.url, media.media_url_https)
        })
      }

      if (get(tweet, 'entities.urls'))
      {
        get(tweet, 'entities.urls').map(url => {
          tweetText = tweetText.replaceAll(url.url, url.expanded_url)
        })
      }

      return {
        ...tweet,
        text: tweetText,
      }
    },
  },
  computed: {
    tweepAvatar () {
      return `${this.isLocal ? `` : window.TwUtils.assetsUrl}avatars/${this.refinedTweet.tweep.id_str}.png`
    },
    refinedTweet () {
      if (typeof this.tweet.tweet_created_at === `string`) { this.tweet.tweet_created_at = new Date(this.tweet.tweet_created_at) }

      if (this.tweet.pivot && this.tweet.pivot.retweeted && this.tweet.retweeted_status) { return this.buildTweet(this.tweet.retweeted_status) }

      return this.sanitizeTweet(this.tweet)
    },
    tweetPivot () {
      return this.tweet.pivot === undefined ? {} : this.tweet.pivot
    },
    quotedStatus () {
      if (!this.refinedTweet.quoted_status) { return }
      return {
        ...this.buildTweet(this.refinedTweet.quoted_status),
        pivot: {
          favorited: this.refinedTweet.quoted_status.favorited,
          retweeted: this.refinedTweet.quoted_status.retweeted,
        }
      }
    },
  },
}
</script>

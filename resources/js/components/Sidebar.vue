<style lang="scss">
  @import "../../sass/global.scss";
  .avatar-container {
    height: 80px;
    background-size: 100% 100% !important;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: -5px;
    border-radius: 10px;

    img.avatar {
      margin-bottom: -30px;
      max-width: 75px;
      width: 75px;
      max-height: 75px;
      height: 75px;
      border: 1px solid $body-background;
    }
  }

  .twitterUser__counter {
    font-family: 'Roboto Mono', monospace;
    height: 50px;
    width: 50px;
    font-size: 0.75rem;
    background: transparent;
    border: 1px dashed black;
    border-radius: 50px;
    margin: auto;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .twitterUser__counterDigit {
    font-size: 0.8rem;
  }
  .sidebar__container {
    background: transparent;
    border-radius: 10px;
  }
</style>
<template>
<div>
  <div class="container-fluid sidebar__container p-1">
    <div class="avatar-container text-center" :style="avatarContainerStyles">
      <img :src="window.TwUtils.assetsUrl+user.social_users[0].avatar" class="rounded-circle avatar">
    </div>
    <div class="ltr pt-3 small d-flex flex-column">
      <div class="flex-1 my-3">
        <span class="py-3 font-size-3">
          @{{user.social_users[0].nickname}}
        </span>
      </div>
      <div class="flex-1">
        <div class="d-flex justify-content-between align-items-center text-center">
          <div class="flex-1">
            <div class="twitterUser__counter" data-toggle="tooltip" data-placement="bottom" :title="user.social_users[0].statuses_count">
              <div class="twitterUser__counterDigit" >{{ humanize(user.social_users[0].statuses_count) }}</div>
            </div>
            <span class="twitterUser__counterDesc">{{__('tweets')}}</span>
          </div>
          <div class="flex-1">
            <div class="twitterUser__counter" data-toggle="tooltip" data-placement="bottom" :title="user.social_users[0].friends_count">
                <div class="twitterUser__counterDigit" >{{ humanize(user.social_users[0].friends_count) }}</div>
            </div>
            <span class="twitterUser__counterDesc">{{__('following')}}</span>
          </div>
          <div class="flex-1">
            <div class="twitterUser__counter" data-toggle="tooltip" data-placement="bottom" :title="user.social_users[0].followers_count">
              <div class="twitterUser__counterDigit" >{{ humanize(user.social_users[0].followers_count) }}</div>
            </div>
            <span class="twitterUser__counterDesc">{{__('followers')}}</span>
          </div>
          <div class="flex-1">
            <div class="twitterUser__counter" data-toggle="tooltip" data-placement="bottom" :title="user.social_users[0].favourites_count">
              <div class="twitterUser__counterDigit" >{{ humanize(user.social_users[0].favourites_count) }}</div>
            </div>
            <span class="twitterUser__counterDesc">{{__('likes')}}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <hr>
</div>
</template>

<script>
export default {
  props: [],
  data () {
    return {
    }
  },
  mounted () {
    $(this.$el).find(`[data-toggle="tooltip"]`).tooltip()
  },
  methods: {
    humanize (value) {
      return window.Humanize.compactInteger(value)
    },
  },
  computed: {
    avatarContainerStyles () {
      const user = this.user.social_users[0]
      return {
        background: user.background_image ? `url("${window.TwUtils.assetsUrl + user.background_image}")` : `#${user.background_color}`,
      }
    },
  },
}
</script>

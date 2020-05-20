import router from '@/router'
import rtlChars from '@/rtlChars'
import startCase from 'lodash/startCase'

const twitterText = require(`twitter-text`)
const isLocal = window.TwUtils.isLocal != null && window.TwUtils.isLocal

export default {
  router,
  destroyed () {
    this.hideLoading()
  },
  data () {
    return {
      window: window,
      locale: window.TwUtils.locale,
      isRtl: window.TwUtils.locale === `ar`,
      routes: window.TwUtils.routes,
      user: window.TwUtils.user,
      userPlaceholder: `${isLocal ? `assets/` : window.TwUtils.baseUrl}images/user-placeholder.gif`,
      isLocal,
      loadingGifSrc: `${isLocal ? `assets/` : window.TwUtils.baseUrl}images/loading.gif`,
    }
  },
  methods: {
    startCase,
    moment: window.moment,
    __: window.__,
    userHavePrivilige (privilege) {
      let privilegeExist = false
      this.user.social_users
        .filter(socialUser => socialUser.scopeIsActive)
        .forEach((socialUser) => {
          if (socialUser.scope.includes(privilege)) { privilegeExist = true }
        })
      return privilegeExist
    },
    showLoading () {
      $(`.loading-gif`).fadeIn()
    },
    hideLoading () {
      $(`.loading-gif`).fadeOut()
    },
    parseTweet (tweet) {
      return twitterText.autoLink(twitterText.htmlEscape(tweet), { targetBlank: true, })
    },
    isRtlText (text) {
      return text.charAt(0).match(rtlChars) || text.charAt(1).match(rtlChars)
    },
    isMediaTask (task) {
      return [`fetchentitiesusertweets`, `fetchentitieslikes`, ].includes(task.baseName)
    },
    tooltip () {
      this.$nextTick(x => {
        $(this.$el).find(`[data-toggle="tooltip"]`).tooltip()
      })
    },
  },
  computed: {
    intlFormat (vm) {
      return number => new Intl.NumberFormat().format(number)
    },
  },
}

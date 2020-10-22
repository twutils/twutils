import get from 'lodash/get'
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
      TwUtils: window.TwUtils,
      locale: window.TwUtils.locale,
      isRtl: window.TwUtils.locale === `ar`,
      routes: window.TwUtils.routes,
      user: window.TwUtils.user,
      userPlaceholder: `${isLocal ? `assets/` : window.TwUtils.baseUrl}images/user-placeholder.gif`,
      isLocal,
      loadingGifSrc: `${isLocal ? `assets/` : window.TwUtils.baseUrl}images/loading.gif`,
      grayBase64Image: `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNc+B8AAkcBolySrScAAAAASUVORK5CYII=`,
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
    parseTweetText (tweet) {
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
    isUserTweetsTask (task) {
      return [`fetchusertweets`, `fetchentitiesusertweets`,].includes(task.baseName)
    },
    isLikesTask (task) {
      return [`fetchlikes`, `fetchentitieslikes`,].includes(task.baseName)
    },
    isFollowingsTask (task) {
      return [`fetchfollowing`,].includes(task.baseName)
    },
    isFollowersTask (task) {
      return [`fetchfollowers`,].includes(task.baseName)
    },
    getCountFromProfileByTask (task) {
      let profileRelatedTaskCount = 0

      if (this.isLikesTask(this.task)) {
        profileRelatedTaskCount = get(this.user, `social_users[0].favourites_count`, 0)
      }

      if (this.isUserTweetsTask(this.task)) {
        profileRelatedTaskCount = get(this.user, `social_users[0].statuses_count`, 0)
      }

      return profileRelatedTaskCount
    },
    getTweetsCountFromTask (task) {
      if (this.isLikesTask(task)) {
        return get(this.task, `likes_count`, 0)
      }

      if (this.isUserTweetsTask(task)) {
        return get(this.task, `likes_count`, 0)
      }

      return 0
    },
    shouldShowTwitterLimitations () {
      const tweetsCountFromProfile = this.getCountFromProfileByTask(this.task)

      const tweetsCountFromTask = this.getTweetsCountFromTask(this.task)

      if (tweetsCountFromProfile > (tweetsCountFromTask + 100)) {
        return true
      }

      return false
    },
    momentCalendar (date) {
      return window.moment(date).calendar(null, { sameElse: `YYYY-MMM-DD h:m A`, })
    },
  },
  computed: {
    intlFormat (vm) {
      return number => new Intl.NumberFormat().format(number)
    },
    getExportTypeIcon (vm) {
      return exportType => {
        if ([window.TwUtils.exports.excel, ].includes(exportType)) {
          return [`grid-three-up`,]
        }

        if ([window.TwUtils.exports.html, ].includes(exportType)) {
          return [`globe`,]
        }

        if ([window.TwUtils.exports.htmlEntities, ].includes(exportType)) {
          return [`image`,]
        }

        return [`data-transfer-download`,]
      }
    },
  },
}

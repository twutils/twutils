<template>
<div class="container">
  <div class="row">
    <div class="col-12">
      <ul class="list-group destroyTweets__optionsList">
        <li class="list-group-item destroyTweets__optionsListItem d-flex justify-content-between align-items-center">
          <div class="w-100">
            <h4 class="border-bottom border-dark d-inline-block mb-5">{{__('destroy_tweets_options.dates_range')}}</h4>
            <p>
              {{__('destroy_tweets_options.dates_range_desc')}}
            </p>
            <p>
              <b>
                {{__('destroy_tweets_options.dates_range_note')}}
              </b>
            </p>
          </div>
          <ul class="list-group destroyTweets__optionsList" style="min-width: 320px;">
            <li class="list-group-item destroyTweets__optionsListItem">
              <h6 class="mb-4 destroyTweets__optionsListItem--header">
                <span v-if="locale==='en'">
                  From <small class="text-muted pl-3">Optional</small>
                </span>
                <span v-if="locale==='ar'">
                  نطاق تاريخ التغريدات المحذوفة | بدايةً من <small class="text-muted">اختياري</small>
                </span>
              </h6>
              <div>
                  <date-input :date.sync="start_date" :endDate.sync="end_date" />
                  <small class="d-block form-text text-muted">
                    <span v-if="locale==='en'">
                      Leave empty for starting from the beginning
                    </span>
                    <span v-if="locale==='ar'">
                      دع التاريخ فارغاً للحذف منذ البداية
                    </span>
                  </small>
                  <div class="d-flex justify-content-between align-items-center px-2">
                    <small class="d-block form-text text-muted" v-text="options.start_date"></small>
                    <span @click="clearStartDate" v-if="options.start_date !== null && options.start_date !== ''" class="clickable" style="font-size: 1.3rem;"><i class="fa fa-times"></i></span>
                  </div>
              </div>
            </li>
            <li class="list-group-item destroyTweets__optionsListItem">
              <h6 class="mb-4 destroyTweets__optionsListItem--header">
                <span v-if="locale==='en'">
                  To <small class="text-muted pl-3">Optional</small>
                </span>
                <span v-if="locale==='ar'">
                  نطاق تاريخ التغريدات المحذوفة | حتى تاريخ <small class="text-muted">اختياري</small>
                </span>
              </h6>
              <div>
                  <date-input :date.sync="end_date" :startDate.sync="start_date"/>
                  <small class="d-block form-text text-muted">
                    <span v-if="locale==='en'">
                      Leave empty for removing until the latest
                    </span>
                    <span v-if="locale==='ar'">
                      دع التاريخ فارغاً للحذف حتى النهاية
                    </span>
                  </small>
                  <div class="d-flex justify-content-between align-items-center px-2">
                    <small class="d-block form-text text-muted" v-text="options.end_date"></small>
                    <span @click="clearEndDate" v-if="options.end_date !== null && options.end_date !== ''" class="clickable" style="font-size: 1.3rem;"><i class="fa fa-times"></i></span>
                  </div>
              </div>
            </li>
          </ul>
        </li>
        <li class="list-group-item destroyTweets__optionsListItem d-flex justify-content-between align-items-center">
          <div class="w-100">
            <h4 class="border-bottom border-dark d-inline-block mb-5">{{__('destroy_tweets_options.tweets_source')}}</h4>
            <div class="d-flex justify-content-around">
              <div
                @click="choseSource(constants.twitter)"
                :class="`tweetsSourceOption ${tweetsSource === constants.twitter ? 'active':''}`"
              >
                <h5>My Account</h5>
                <p>
                  Using this option, TwUtils will read your tweets from Twitter
                  API, but it will be limited to the last ~3200 tweet.
                </p>
                <div class="alert alert-warning">
                  Looks like you have
                  ({{user.social_user.statuses_count}})
                  tweet,
                  Using this option will limit the tweets to the last ~3200.
                </div>
              </div>
              <div
                @click="choseSource(constants.file)"
                :class="`tweetsSourceOption ${tweetsSource === constants.file ? 'active':''}`"
              >
                <h5>Archive File</h5>
                <div>
                  <p>
                    More accurate removal. Upload an archive file.
                  </p>
                  <img v-if="loading" :src="loadingGifSrc" class="m-auto loadingGif" width="20px" height="20px">
                  <span v-if="uploads.length > 0">
                    {{uploads[0]}}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </li>
        <li class="d-none list-group-item destroyTweets__optionsListItem">
          <h6 class="mb-4 destroyTweets__optionsListItem--header">
            <div class="destroyTweets__optionsListItem--bullet">•</div>
            <span v-if="locale==='en'">
              Tweet Type <small class="text-muted pl-3">Optional</small>
            </span>
            <span v-if="locale==='ar'">
              نوع التغريدة
            </span>
          </h6>
        </li>
      </ul>
    </div>
  </div>
</div>
</template>
<script>
import AccordionCard from '@/components/AccordionCard'
import DateInput from '@/components/common/DateInput'

const options = {
  retweets: false,
  tweets: false,
  replies: false,
  start_date: null,
  end_date: null,
}

const years = []
const months = []
const days = []

for (var i = 2006; i <= (new Date()).getFullYear(); i++) {
  years.push(`${i}`)
}

for (var i = 1; i <= 12; i++) {
  months.push(`${i}`.padStart(2, `0`))
}

for (var i = 1; i <= 31; i++) {
  days.push(`${i}`.padStart(2, `0`))
}

const dateOptions = {
  year: ``,
  month: ``,
  day: ``,
}

export default {
  components: {
    AccordionCard,
    DateInput,
  },
  props: {
    value: {
      type: Object,
    },
    taskDefinition: {
      type: Object,
    },
  },
  data () {
    return {
      options: { ...options, },
      start_date: { ...dateOptions, },
      end_date: { ...dateOptions, },
      uploads: [],
      loading: false,
      tweetsSource: `twitter`, // 'twitter', 'file'
    }
  },
  watch: {
    options: {
      deep: true,
      handler (newValue) {
        this.$emit(`input`, this.options)
      },
    },
    start_date: {
      deep: true,
      handler (newValue) {
        this.startDateChanged(newValue)
      },
    },
    end_date: {
      deep: true,
      handler (newValue) {
        this.endDateChanged(newValue)
      },
    },
  },
  mounted () {
    this.fetchUploads()
  },
  methods: {
    choseSource (source) {
      if (source === this.constants.file) {
        return
      }

      this.tweetsSource = source
    },
    fetchUploads () {
      this.loading = true

      axios.get(`${window.TwUtils.apiBaseUrl}tasks/uploads`)
        .then(({ data, }) => {
          this.loading = false
          this.uploads = data
        })
    },
    dateOptionsToString (dateOptions, propName = `startDate`) {
      const defaultMonth = `01` // propName === 'startDate' ? '01' : '12'
      const defaultDay = `01` // propName === 'startDate' ? '01' : '31'
      return `${dateOptions.year}-${dateOptions.month === `` ? defaultMonth : dateOptions.month}-${dateOptions.day === `` ? defaultDay : dateOptions.day}`
    },
    startDateChanged (newValue) {
      if (newValue.year === ``) { return this.options.start_date = `` }
      this.options.start_date = this.dateOptionsToString(newValue, `startDate`)
    },
    endDateChanged (newValue) {
      if (newValue.year === ``) { return this.options.end_date = `` }
      this.options.end_date = this.dateOptionsToString(newValue, `endDate`)

      this.keepDatesRelated()
    },
    clearStartDate () {
      this.options.start_date = ``
      this.start_date = { ...dateOptions, }
    },
    clearEndDate () {
      this.options.end_date = ``
      this.end_date = { ...dateOptions, }
    },
    keepDatesRelated () {
      // This function handles this scenario:
      // - Old value was set to: start = 2006-09-04, end = 2007-04-03
      // - Then, the end year is changed from '2007' to '2006', without changing month/day
      // - Thus, it will be: start = 2006-09-04, end = 2006-04-03
      // which is invalid.
      this.$nextTick(x => {
        const endAndStartSameYear = this.start_date.year === this.end_date.year

        if (endAndStartSameYear && this.start_date.month >= this.end_date.month) { this.start_date.month = this.end_date.month }

        const endAndStartSameMonth = this.start_date.month === this.end_date.month

        if (endAndStartSameYear && endAndStartSameMonth && this.start_date.day >= this.end_date.day) { this.start_date.day = this.end_date.day }
      })
    },
  },
  computed: {

  },
}
</script>

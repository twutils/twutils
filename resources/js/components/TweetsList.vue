<style lang="scss">
.tweets-container {
    min-height: 800px;
    overflow: auto;
    background: white;
}
.tweetAvatarLink {
  &:active, &:focus, &:hover {
    text-decoration: none;
    word-break: break-all;

    .text-link {
      text-decoration: underline;
    }
  }
}
.tweetLink {
  flex: 1 25%;
}
.tweetAvatarLink {
  flex: 1 75%;
}
.tweetText {
//  padding-top: 1.5rem;
  white-space: pre-wrap;
  padding: 0rem 1rem;
  word-break: break-word;
}
.monthTweetsBarContainer {
  background: rgb(245, 245, 245);

  &.selected {
    background: darken( rgb(245, 245, 245), 7%);

    .monthTweetsBar {
      background: darken( #85d3fc , 30%);
    }
  }

  &.selectable {
    cursor: pointer;
  }

&:hover .monthTweetsBar {
    background: darken( #85d3fc , 20%);
  }
}
.monthTweetsBar {
  position: absolute;
  right: 2px;
  left: 2px;
  bottom: 0;
  background: #85d3fc;
}
.monthTweetsBarLabel {
  position: absolute;
  bottom: 0px;
  left: 6px;
  font-size: 10px;
}
.monthTweetsCount {
  position: absolute;
  top: 0;
  z-index: 10;
}
.tweetsList {
  background: rgb(255, 255, 255);
}

.chevronNavigateCircle {
  background: #c7c7c7;
  width: 30px;
  height: 30px;
  padding: 0px;
  border-radius: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  color: black;
  cursor: pointer;

  &.inactive {
    color: lighten(black, 60%);
    background: lighten(#c7c7c7, 10%);
    cursor: default;
  }
}

.chevronNavigateContainer {
    background: #f5f5f5;
    align-items: center;
    border-radius: 20px;
    text-align: center;
}

</style>
<template>
  <div class="my-3 row tweetsList">
    <slot></slot>
    <div class="col-12 tweetsList__controls__fluidContainer">
      <div class="row tweetsList__controls__container">
        <div class="col-sm-8 p-0 tweetsList__controls__wrapper">
          <div class="tweetsList__searchInfo__container d-flex justify-content-between">
            <div class="tweetsList__searchInfo">
              <span>
                {{__('total_tweets')}}: {{intlFormat(totalTweets)}}
              </span>
              <img v-if="loading" :src="loadingGifSrc" class="loadingGif loadingGif--xs">
              <twitter-limitations-info
                v-if="shouldShowTwitterLimitations()"
                :task="task"
              ></twitter-limitations-info>
            </div>
            <div class="flex-1 d-flex align-items-center p-1">
              <div class="tweetsList__sortDescription__container">
              </div>
            </div>
            <div class="tweetsList__searchInfo">
              {{__('search_results')}}: {{intlFormat(resultsCount)}}
            </div>
          </div>
          <div class="d-flex line-height-1_0rem">
            <div class="searchOption__description">
              {{__('filter_by_media')}}
            </div>
            <div class="searchOptions__container">
              <div class="searchOption__wrapper">
                <input v-model="searchOptions.withTextOnly" type="checkbox" class="form-check-input">
                <label :class="`form-check-label ${isRtl ? 'rtl' :''}`" @click="searchOptions.withTextOnly = !searchOptions.withTextOnly ">
                  <template v-if="locale === 'ar'">
                    تغريدات بلا وسائط
                  </template>
                  <template v-if="locale === 'en'">
                    Tweets without Media
                  </template>
                  <small class="text-muted">
                    ({{tweetsWithoutMedia}})
                  </small>
                </label>
              </div>
              <div class="searchOption__wrapper">
                <input v-model="searchOptions.withPhotos" type="checkbox" class="form-check-input">
                <label :class="`form-check-label ${isRtl ? 'rtl' :''}`" @click="searchOptions.withPhotos = !searchOptions.withPhotos ">
                  <template v-if="locale === 'ar'">
                    تغريدات تحتوي على صور
                  </template>
                  <template v-if="locale === 'en'">
                    Tweets with Photos
                  </template>
                  <small class="text-muted">
                    ({{ tweetsWithPhotos }})
                  </small>
                </label>
              </div>
              <div class="searchOption__wrapper">
                <input v-model="searchOptions.withGifs" type="checkbox" class="form-check-input">
                <label :class="`form-check-label ${isRtl ? 'rtl' :''}`" @click="searchOptions.withGifs = !searchOptions.withGifs ">
                  <template v-if="locale === 'ar'">
                    تغريدات تحتوي على صور متحركة
                  </template>
                  <template v-if="locale === 'en'">
                    Tweets with Gif
                  </template>
                  <small class="text-muted">
                    ({{ tweetsWithGif }})
                  </small>
                </label>
              </div>
              <div class="searchOption__wrapper">
                <input v-model="searchOptions.withVideos" type="checkbox" class="form-check-input">
                <label :class="`form-check-label ${isRtl ? 'rtl' :''}`" @click="searchOptions.withVideos = !searchOptions.withVideos ">
                  <template v-if="locale === 'ar'">
                    تغريدات تحتوي على فيديوهات
                  </template>
                  <template v-if="locale === 'en'">
                    Tweets with Videos
                  </template>
                  <small class="text-muted">
                    ({{ tweetsWithVideos }})
                  </small>
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="w-100 p-3">
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">
                  <span class="oi" data-glyph="magnifying-glass"></span>
                </span>
              </div>
              <input v-model="searchKeywords" type="text" class="form-control" :placeholder="__('search')" aria-label="Search">
            </div>
          </div>
          <div class="w-100 px-3">
            <div class="">
              <label class="small" for="perPage">{{__('per_page')}}: {{perPage}}</label>
              <input type="range" class="custom-range" id="perPage" min="100" max="1000" step="10" v-model="perPage">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4 order-sm-2">
      <div v-for="(year, yearIndex) in historyYears" class="row" :key="yearIndex">
        <div class="col-12 mt-3 d-flex justify-content-between">
          <h5>
            {{year}} (<small>{{ getYearTweetsLength(year) }}</small>)
          </h5>
          <div
            v-if="yearIndex === 0"
            class="btn-group"
            data-toggle="buttons"
          >
            <label :class="`btn btn-outline-dark ${searchOnlyInMonth === true ? 'active':''}`">
              <input class="invisibleRadio" type="radio" name="searchOnlyInMonth" v-model="searchOnlyInMonth" :value="true">
                <template v-if="locale === 'ar'">
                  البحث فقط في
                </template>
                <template v-if="locale === 'en'">
                  Search Only in
                </template>
                {{ selected.year === null || selected.month === null ? (locale === 'ar' ?  'الشهر المختار' : 'the selected month') : `${selected.year}-${months[selected.month]}`}}
            </label>
            <label :class="`btn btn-outline-dark ${searchOnlyInMonth === false ? 'active':''}`">
              <input class="invisibleRadio" type="radio" name="searchOnlyInMonth" v-model="searchOnlyInMonth" :value="false">
              <template v-if="locale === 'ar'">
                الكل
              </template>
              <template v-if="locale === 'en'">
                All
              </template>
            </label>
          </div>
        </div>
        <div
          @click="selectYearAndMonth(year, monthIndex)"
          data-toggle="tooltip"
          data-placement="bottom"
          :title="`${year}-${month} ${getYearAndMonthTweetsLength(year, monthIndex)} ${__('tweets')}`"
          v-for="(month, monthIndex) in months"
          :class="`col-1 monthTweetsBarContainer ${selected.year === year && selected.month === monthIndex ? 'selected':''} ${getYearAndMonthTweetsLength(year, monthIndex) > 0 ? 'selectable':''}`"
          style="height: 50px;"
          :key="monthIndex"
        >
          <small
            class="monthTweetsBar"
            :style="`height: ${getMonthBarHeight(year, monthIndex)}px;`">&nbsp;</small>
          <small
            class="monthTweetsBarLabel"
            v-if="monthIndex == 0 || monthIndex == 11"
            v-text="month.substr(0,3).toUpperCase()"
          ></small>
        </div>
      </div>
    </div>
    <div :class="`col-sm-8 order-sm-1 my-2`">
      <div class="row">
      </div>
      <div class="col-12">
          <portal-target class="overflow-auto py-1" name="tweets-list-pager" />
      </div>
      <div :class="`col-12 ${isRtl ? 'rtl':''}`">
          <div class="table-responsive tweets__container">
            <tweets-list-datatable
              class="tweetsList__table"
              ref="tweets-list-datatable"
              :per-page="perPageInt"
              :columns="[{}]"
              :data="tweetsList"
            >
                <template slot-scope="{ row, columns }">
                  <tr v-if="row === undefined || loading" class="">
                    <td>
                      <div class="ph-item animated fadeOut infinite animation-3s">
                        <span class="ph-picture" style="height: 20px; width: 100px;"></span>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="! loading && row !== undefined">
                    <td>
                      <tweets-list-item :ref="row.id" :tweet="row" :key="row.id"></tweets-list-item>
                    </td>
                  </tr>
                </template>
            </tweets-list-datatable>
            <portal-target name="tweets-list-pager" />
            <portal to="tweets-list-pager">
              <tweets-list-datatable-pager
                v-model="page"
                type="long"
                :per-page="perPageInt"
              ></tweets-list-datatable-pager>
            </portal>
          </div>
      </div>
    </div>
  </div>
</template>

<script>
import Vue from 'vue'
import DatatableFactory from 'vuejs-datatable/dist/vuejs-datatable.esm.js'

import debounce from 'lodash/debounce'
import get from 'lodash/get'
import sortBy from 'lodash/sortBy'
import uniqBy from 'lodash/uniqBy'
import groupBy from 'lodash/groupBy'
import max from 'lodash/max'
import min from 'lodash/min'
import searchTweets from '../search'
import tweetsListItem from './TweetsListItem'
import twitterLimitationsInfo from '@/components/TwitterLimitationsInfo'

const months = [`January`, `February`, `March`, `April`, `May`, `June`, `July`, `August`, `September`, `October`, `November`, `December`, ]

const tweetsListDatatable = DatatableFactory.useDefaultType(false).registerTableType(
  `tweets-list-datatable`,
  tableType => {
    tableType.mergeSettings({
      table: {
        class: `table tweetsList__table`,
      },
      pager: {
        classes: {
          li: `page-item`,
          a: `page-link`,
          pager: `pagination text-center m-0 px-3 w-100`,
          selected: `active`,
        },
      },
    })
    return tableType
  }
)

tweetsListDatatable.install(Vue)

export default {
  components: {
    'tweets-list-item': tweetsListItem,
    twitterLimitationsInfo,
  },
  props: {
    task: {
      type: Object,
    },
  },
  data () {
    return {
      filters: [],
      yearAndMonthFilter: null,
      searchFilter: null,

      tweets: [],
      tweetsCopy: [],
      tweetsByYearsAndMonths: {},

      historyYears: [],
      maximumMonthlyTweets: [],

      selected: {
        year: null,
        month: null,
      },
      months,
      searchKeywords: ``,
      searchOnlyInMonth: false,
      searchOptions: {
        withTextOnly: false,
        withPhotos: false,
        withGifs: false,
        withVideos: false,
      },

      jsSearch: searchTweets,
      debouncedSearch: null,
      debouncedAfterFiltering: null,
      perPage: 200,

      taskView: null,
      loading: true,
      page: 1,
    }
  },
  computed: {
    perPageInt() {
      return parseInt(this.perPage)
    },
    tweetsList() {
      if (this.taskView)
      {
        return (Array((this.taskView.from || 1)-1)).concat(this.taskView.data).concat((Array(this.taskView.total - (this.taskView.to || 0))))
      }

      return this.filteredTweets
    },
    totalTweets() {
      return this.task.likes_count
    },
    filteredTweets () {
      let tweets = this.tweetsCopy
      const filters = []

      if (this.searchFilter !== null && this.searchKeywords.length > 0) { filters.push(this.searchFilter) }

      if (this.searchOptions.withTextOnly) {
        const filterFunc = tweets => tweets.filter(x => x.media.length === 0)
        filterFunc.isOrOperatorFilter = true
        filters.push(filterFunc)
      }

      if (this.searchOptions.withPhotos) {
        const filterFunc = tweets => tweets.filter(x => x.media[0] && x.media[0].type === `photo`)
        filterFunc.isOrOperatorFilter = true
        filters.push(filterFunc)
      }

      if (this.searchOptions.withGifs) {
        const filterFunc = tweets => tweets.filter(x => x.media[0] && x.media[0].type === `animated_gif`)
        filterFunc.isOrOperatorFilter = true
        filters.push(filterFunc)
      }

      if (this.searchOptions.withVideos) {
        const filterFunc = tweets => tweets.filter(x => x.media[0] && x.media[0].type === `video`)
        filterFunc.isOrOperatorFilter = true
        filters.push(filterFunc)
      }

      if (this.searchOnlyInMonth && this.yearAndMonthFilter !== null) { filters.push(this.yearAndMonthFilter) }

      filters
        .filter(filter => !filter.isOrOperatorFilter)
        .forEach((filter) => {
          tweets = filter(tweets)
        })

      let orOperatorResults = []

      filters
        .filter(filter => filter.isOrOperatorFilter)
        .forEach((filter) => {
          orOperatorResults = orOperatorResults.concat(filter(tweets))
        })

      if (filters.find(filter => filter.isOrOperatorFilter)) {
        tweets = sortBy(uniqBy(orOperatorResults, `id`), `tweet_created_at`).reverse()
      }

      return tweets
    },
    resultsCount() {
      if ( this.taskView )
      {
        return this.taskView.total
      }

      return this.filteredTweets.length
    },
    tweetsWithoutMedia() {
      return this.taskView ? this.taskView.tweets_text_only : this.tweets.filter(x => x.media.length === 0).length
    },
    tweetsWithPhotos() {
      return this.taskView ? this.taskView.tweets_with_photos : this.tweets.filter(x => x.media[0] && x.media[0].type === 'photo').length
    },
    tweetsWithGif() {
      return this.taskView ? this.taskView.tweets_with_gifs : this.tweets.filter(x => x.media[0] && x.media[0].type === 'animated_gif').length
    },
    tweetsWithVideos() {
      return this.taskView ? this.taskView.tweets_with_videos : this.tweets.filter(x => x.media[0] && x.media[0].type === 'video').length
    },
  },
  mounted () {
    this.debouncedAfterFiltering = debounce(t => {
      return this.afterFiltering()
    }, 100)
    this.buildSearch()

    if (this.isLocal) {
      this.tweets = this.task.likes
      this.autoSelectLatestTweet()
      this.$nextTick(x => this.loading = false)
    } else if (this.task.status === 'completed') {
      this.fetchTweetsFromView()
    } else {
      this.fetchTweetsList()
    }

    this.buildHistory()

    this.hideLoading()
  },
  watch: {
    searchOptions: {
      deep: true,
      handler (newValue) {
        if (this.taskView)
        {
          return this.$nextTick(this.debouncedSearch)
        }

        this.debouncedAfterFiltering()
      },
    },
    searchKeywords (...args) {
      this.$nextTick(this.debouncedSearch)
    },
    perPage (...args) {
        if (this.taskView)
        {
          return this.$nextTick(this.debouncedSearch)
        }

        this.debouncedAfterFiltering()
    },
    page(...args) {
      this.loading = true
      this.search()
    },
    searchOnlyInMonth (newValue) {
      if (! newValue)
      {
        this.selected = {
          year: null,
          month: null,
        }
      }

      this.$nextTick(this.debouncedSearch)
    },
    tweets () {
      this.$nextTick(this.buildHistory)
    },
  },
  methods: {
    autoSelectLatestTweet () {
      this.$nextTick(x => {
        const latestTweet = this.tweetsCopy.length == 0 ? null : this.tweetsCopy.reduce((a, b) => a.tweet_created_at > b.tweet_created_at ? a : b)

        this.filterTweetsByTweet(latestTweet)
      })
    },
    fetchTweetsFromView(callback = null) {
      this.loading = true

      axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.task.id}/view`, {
        params: {
          year: this.selected.year,
          month: this.selected.month !== null ? (this.selected.month + 1) : null,
          page: this.page,
          perPage: this.perPage,
          searchOptions: Object.keys(this.searchOptions).filter(x => this.searchOptions[x]),
          searchKeywords: this.searchKeywords,
          searchOnlyInMonth: this.searchOnlyInMonth ? 1 : 0,
        }
      })
      .then(resp => {
        this.$nextTick(x => this.loading = false)

        let months = {}

        Object.keys(resp.data.months).map(year => {
          months[year] = {}

          Object.keys(resp.data.months[year]).map(month => {
            months[year][parseInt(month)-1] = resp.data.months[year][month]
          })
        })

        this.taskView = {
          ...resp.data,
          months,
        }

        this.tweets = resp.data.data
        this.tweetsCopy = this.tweets.map(tweet => {
          return {
            ...tweet,
            tweet_created_at: new Date(tweet.tweet_created_at),
          }
        })

        this.buildHistory()

        if (callback)
        {
          return callback()
        }

        this.$nextTick(this.afterFiltering)
      })
    },
    fetchTweetsList (page = 1) {
      this.loading = true

      axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.task.id}/data?page=${page}`)
        .then(resp => {
          this.loading = false

          const currentPage = resp.data.current_page
          const lastPage = resp.data.last_page

          const currentTweetsIds = this.tweets.map(x => x.id_str)

          resp.data.data.map(tweet => {
            if (!currentTweetsIds.includes(tweet.id_str)) {
              currentTweetsIds.push(tweet.id_str)
              this.tweets.push(tweet)
            }
          })

          if (currentPage === 1) {
            this.$nextTick(this.autoSelectLatestTweet)
          }

          if (currentPage !== lastPage) {
            this.fetchTweetsList(currentPage + 1)
          }

        })
    },
    buildHistory () {
      this.historyYears = []

      if (this.taskView)
      {
        this.buildHistoryFromTaskView()
      } else {
        this.buildHistoryFromTweets()
      }

      this.historyYears.sort().reverse() // Start from maximum tweet year to minimum..

      this.$nextTick(x => {
        $(this.$el).find(`[data-toggle="tooltip"]`).tooltip(`dispose`)

        this.tooltip()

        $(this.$el).find('[name=searchOnlyInMonth]').change((ev) => {
          this.searchOnlyInMonth = ev.target.value === 'true'
        })
      })
    },
    buildHistoryFromTaskView() {
        let tweetsYearsOnly = Object.keys(this.taskView.months).sort().reverse()

        for (var i = min(tweetsYearsOnly); i <= max(tweetsYearsOnly); i++) {
          if (!this.historyYears.includes(parseInt(i))) {
            this.historyYears.push(parseInt(i))
          }
        }

        this.maximumMonthlyTweets = max(
          this.historyYears.map(year => {
            return Object.keys(this.taskView.months[year] || {})
            .map(month => {
              return this.taskView.months[year][month]
            })
          }).reduce((a,b) => a.concat(b), [])
        )
    },
    buildHistoryFromTweets() {
      this.tweetsCopy = this.tweets.map(tweet => {
        return {
          ...tweet,
          tweet_created_at: new Date(tweet.tweet_created_at),
        }
      })

      const tweetsByYears = groupBy(this.tweetsCopy, (tweet) => tweet.tweet_created_at.getFullYear())
      const tweetsYearsOnly = Object.keys(tweetsByYears)

      this.tweetsByYearsAndMonths = {}

      tweetsYearsOnly.map(year => {
        this.tweetsByYearsAndMonths[year] = groupBy(tweetsByYears[year], (tweet) => tweet.tweet_created_at.getMonth())
      })

      for (var i = min(tweetsYearsOnly); i <= max(tweetsYearsOnly); i++) {
        if (!this.historyYears.includes(parseInt(i))) {
          this.historyYears.push(parseInt(i))
        }
      }
    },
    buildSearch () {
      this.debouncedSearch = debounce(t => {
        return this.search()
      }, 1000)
    },
    filterTweetsByTweet (tweet) {
      if (tweet == null) return
      this.filterTweetsByYearAndMonth(tweet.tweet_created_at.getFullYear(), tweet.tweet_created_at.getMonth())
    },
    search () {
      if (this.taskView) {
        return this.$nextTick(x => this.fetchTweetsFromView(this.debouncedAfterFiltering))
      }

      const searchKeywords = this.searchKeywords
      if (searchKeywords === ``) {
        this.searchFilter = (tweets) => tweets

        this.debouncedAfterFiltering()
        return
      }

      this.searchFilter = (tweets) => {
        tweets = this.jsSearch(tweets, searchKeywords)
        return tweets
      }
      this.debouncedAfterFiltering()
    },
    selectYearAndMonth (year, month) {
      this.filterTweetsByYearAndMonth(year, month)


    },
    filterTweetsByYearAndMonth (year, month) {

      if (this.searchOnlyInMonth)
      {
        this.$nextTick(this.debouncedSearch)
      }

      this.searchOnlyInMonth = true

      if (this.getYearAndMonthTweetsLength(year, month) === 0) { return }


      if (! this.taskView)
      {
        this.searchKeywords = ``
      }

      const monthStart = new Date(year, month, 1, 0, 0, 0, 0)
      const monthEnd = new Date(year, month, 1, 0, 0, 0, 0)
      monthEnd.setMonth(month + 1)
      this.yearAndMonthFilter = (tweets) => {
        return tweets.filter(tweet => {
          return tweet[`tweet_created_at`] >= monthStart && tweet[`tweet_created_at`] <= monthEnd
        })
      }
      this.selected = { year, month, }
      this.afterFiltering()
    },
    afterFiltering () {
      this.$nextTick(() => {
        this.$el.querySelectorAll(`img`).forEach(x => x.isRemote = false)
        $(this.$el).find(`img`).unveil(100)
      })
      this.$nextTick(() => {
        $(document).off(`click`, `[data-toggle="lightbox"]`)
        $(document).on(`click`, `[data-toggle="lightbox"]`, function (event) {
          event.preventDefault()
          $(this).ekkoLightbox()
        })
      })
    },
    getYearTweetsLength (year) {
      if (this.taskView)
      {
        return Object.keys(this.taskView.months[year] || {})
          .map(month => {
            return this.taskView.months[year][month]
          })
          .reduce((a, b) => a + b, 0);
      }
      return this.months
        .map((month, monthIndex) => {
          const monthTweets = get(this.tweetsByYearsAndMonths, [year, monthIndex, ].join(`.`))
          return monthTweets ? monthTweets.length : 0
        })
        .reduce((a, b) => a + b)
    },
    getYearAndMonthTweetsLength (year, month) {
      if (this.taskView)
      {
        return get(this.taskView, `months.${[year, month, ].join(`.`)}`, 0)
      }

      const yearAndMonthTweets = get(this.tweetsByYearsAndMonths, [year, month, ].join(`.`))
      const yearAndMonthTweetsLength = yearAndMonthTweets == undefined ? 0 : yearAndMonthTweets.length

      if (yearAndMonthTweetsLength > this.maximumMonthlyTweets) { this.maximumMonthlyTweets = yearAndMonthTweetsLength }

      return yearAndMonthTweetsLength
    },
    getMonthBarHeight (year, monthIndex) {
      const tweetsLength = this.getYearAndMonthTweetsLength(year, monthIndex)

      const calculatedValue = parseInt((tweetsLength * 50) / this.maximumMonthlyTweets)

      if (tweetsLength !== 0 && calculatedValue < 2) { return 2 }

      return calculatedValue
    },
  },
}
</script>

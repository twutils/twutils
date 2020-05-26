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
    <div class="col-12">
      <div class="row">
        <div class="col-sm-4">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">
                <span class="oi" data-glyph="magnifying-glass"></span>
              </span>
            </div>
            <input v-model="searchKeywords" type="text" class="form-control" :placeholder="__('search')" aria-label="Search">
          </div>
        </div>
        <div class="col-sm-4 offset-sm-2">
          <div class="form-group form-check pt-2">
            <input v-model="searchOnlyInMonth" type="checkbox" class="form-check-input">
            <label :class="`form-check-label ${isRtl ? 'rtl' :''}`" @click="searchOnlyInMonth = !searchOnlyInMonth ">
              <template v-if="locale === 'ar'">
                البحث فقط في شهر
              </template>
              <template v-if="locale === 'en'">Search Only in</template>
              {{ selected.year === null || selected.month === null ? (locale === 'ar' ?  'الشهر المختار' : 'the selected month') : `${selected.year}-${months[selected.month]}`}}
            </label>
          </div>
        </div>
        <div class="col-12" v-if="searchKeywords !== '' && searchSummaryMessage !== '' ">
          {{ searchSummaryMessage }}
        </div>
      </div>
    </div>
    <div class="col-sm-4 order-sm-2">
      <div v-for="(year, yearIndex) in historyYears" class="row" :key="yearIndex">
        <div class="col-12 mt-3">
          <h5>
            {{year}} (<small>{{ getYearTweetsLength(year) }}</small>)
          </h5>
        </div>
        <div @click="filterTweetsByYearAndMonth(year, monthIndex)" data-toggle="tooltip" data-placement="bottom" :title="`${year}-${month} ${getYearAndMonthTweetsLength(year, monthIndex)} ${__('tweets')}`" v-for="(month, monthIndex) in months" :class="`col-1 monthTweetsBarContainer ${selected.year === year && selected.month === monthIndex ? 'selected':''} ${getYearAndMonthTweetsLength(year, monthIndex) > 0 ? 'selectable':''}`" style="height: 50px;" :key="monthIndex">
          <small class="monthTweetsBar" :style="`height: ${parseInt( (getYearAndMonthTweetsLength(year, monthIndex) * 50) / maximumMonthlyTweets)}px;`">&nbsp;</small>
          <small class="monthTweetsBarLabel" v-if="monthIndex == 0 || monthIndex == 11" v-text="month.substr(0,3).toUpperCase()"></small>
        </div>
      </div>
    </div>
    <div :class="`col-sm-8 order-sm-1 my-2`">
      <div class="row">
        <div v-if="resultsCount  > paginatedFilteredTweets.length" class="col-12 chevronNavigateContainer d-flex justify-content-around">
          <div :class="`chevronNavigateCircle ${canNavigatePrev ? '':'inactive'}`" @click="navigatePrev">
            <span class="oi" data-glyph="chevron-left"></span>
          </div>
          <div>
            {{__('page')}} {{ currentPage+1 }} / {{ totalPages }}
            <br>
            <small class="ltr" v-if="locale === 'en'">
              showing {{resultsCount - resultsStart > resultsLength ? resultsLength : resultsCount - resultsStart }} tweet out of {{resultsCount}} tweet
            </small>
            <small class="rtl" v-if="locale === 'ar'">
              عرض
              {{resultsCount - resultsStart > resultsLength ? resultsLength : resultsCount - resultsStart }}
               تغريدة من أصل
              {{resultsCount}}
               تغريدة
            </small>
          </div>
          <div :class="`chevronNavigateCircle ${canNavigateNext ? '':'inactive'}`" @click="navigateNext">
            <span class="oi" data-glyph="chevron-right"></span>
          </div>
        </div>
      </div>
      <tweets-list-item v-for="(tweet,index) in paginatedFilteredTweets" :tweet="tweet" :key="tweet.id"></tweets-list-item>
    </div>
  </div>
</template>

<script>
import debounce from 'lodash/debounce'
import get from 'lodash/get'
import groupBy from 'lodash/groupBy'
import max from 'lodash/max'
import min from 'lodash/min'
import searchTweets from '../search'
import tweetsListItem from './TweetsListItem'

const months = [`January`, `February`, `March`, `April`, `May`, `June`, `July`, `August`, `September`, `October`, `November`, `December`, ]

export default {
  components: {
    'tweets-list-item': tweetsListItem,
  },
  props: {
    task: {
      type: Object,
    },
  },
  data () {
    return {
      isMounted: false,
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
      jsSearch: searchTweets,
      debouncedSearch: null,
      debouncedAfterFiltering: null,
      searchSummaryMessage: ``,
      resultsLength: 200,
      resultsStart: 0,
      resultsCount: 0,
    }
  },
  computed: {
    paginatedFilteredTweets () {
      let tweets = this.tweetsCopy
      const filters = []

      if (this.searchFilter !== null && this.searchKeywords.length > 0) { filters.push(this.searchFilter) }

      if ((filters.length == 0 || this.searchOnlyInMonth) && this.yearAndMonthFilter !== null) { filters.push(this.yearAndMonthFilter) }

      filters.forEach((filter) => {
        tweets = filter(tweets)
      })

      this.resultsCount = tweets.length

      return tweets.slice(this.resultsStart, this.resultsStart + this.resultsLength)
    },
    canNavigatePrev () {
      const prevStart = this.resultsStart - this.resultsLength

      return prevStart >= 0
    },
    canNavigateNext () {
      const nextStart = this.resultsStart + this.resultsLength

      return nextStart < this.resultsCount
    },
    currentPage () {
      return Math.ceil(this.resultsStart / this.resultsLength)
    },
    totalPages () {
      return Math.ceil(this.resultsCount / this.resultsLength)
    },
  },
  mounted () {
    this.debouncedAfterFiltering = debounce(t => {
      return this.afterFiltering()
    }, 300)
    this.buildSearch()

    if (this.task.likes === undefined) {
      this.fetchTweetsList()
    } else {
      this.tweets = this.task.likes
      this.autoSelectLatestTweet()
    }

    this.buildHistory()

    this.hideLoading()
  },
  watch: {
    searchKeywords (...args) {
      this.$nextTick(this.debouncedSearch)
    },
    searchOnlyInMonth (...args) {
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
    fetchTweetsList (page = 1) {
      axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.task.id}/data?page=${page}`)
        .then(resp => {
          const currentPage = resp.data.current_page
          const lastPage = resp.data.last_page

          let currentTweetsIds = this.tweets.map(x => x.id_str)

          resp.data.data.map(tweet => {
            if (! currentTweetsIds.includes(tweet.id_str))
            {
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
      this.tweetsCopy = this.tweets.map(tweet => {
        return {
          ...tweet,
          tweet_created_at: new Date(tweet.tweet_created_at),
        }
      })

      const tweetsByYears = groupBy(this.tweetsCopy, (tweet) => tweet.tweet_created_at.getFullYear())
      const tweetsYearsOnly = Object.keys(tweetsByYears)

      this.tweetsByYearsAndMonths = {}
      this.historyYears = []

      tweetsYearsOnly.map(year => {
        this.tweetsByYearsAndMonths[year] = groupBy(tweetsByYears[year], (tweet) => tweet.tweet_created_at.getMonth())
      })

      for (var i = min(tweetsYearsOnly); i <= max(tweetsYearsOnly); i++) {
        if (!this.historyYears.includes(parseInt(i))) {
          this.historyYears.push(parseInt(i))
        }
      }

      this.historyYears.sort().reverse() // Start from maximum tweet year to minimum..

      this.$nextTick(x => {
        $(this.$el).find(`[data-toggle="tooltip"]`).tooltip(`dispose`)
        this.tooltip()
      })
    },
    navigatePrev () {
      if (this.canNavigatePrev) {
        this.showLoading()
        this.resultsStart = this.resultsStart - this.resultsLength
        this.$nextTick(this.hideLoading)
        this.debouncedAfterFiltering()
      }
    },
    navigateNext () {
      if (this.canNavigateNext) {
        this.showLoading()
        this.resultsStart = this.resultsStart + this.resultsLength
        this.$nextTick(this.hideLoading)
        this.debouncedAfterFiltering()
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
      const searchKeywords = this.searchKeywords
      if (searchKeywords === ``) {
        this.searchFilter = (tweets) => tweets

        this.debouncedAfterFiltering()
        return
      }

      this.resultsStart = 0

      this.searchFilter = (tweets) => {
        tweets = this.jsSearch(tweets, searchKeywords)
        return tweets
      }
      this.debouncedAfterFiltering()
    },
    filterTweetsByYearAndMonth (year, month) {
      if (this.getYearAndMonthTweetsLength(year, month) === 0) { return }

      this.resultsStart = 0
      this.searchKeywords = ``

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
      return this.months
        .map((month, monthIndex) => {
          const monthTweets = get(this.tweetsByYearsAndMonths, [year, monthIndex, ].join(`.`))
          return monthTweets ? monthTweets.length : 0
        })
        .reduce((a, b) => a + b)
    },
    getYearAndMonthTweetsLength (year, month) {
      const yearAndMonthTweets = get(this.tweetsByYearsAndMonths, [year, month, ].join(`.`))
      const yearAndMonthTweetsLength = yearAndMonthTweets == undefined ? 0 : yearAndMonthTweets.length

      if (yearAndMonthTweetsLength > this.maximumMonthlyTweets) { this.maximumMonthlyTweets = yearAndMonthTweetsLength }

      return yearAndMonthTweetsLength
    },
  },
}
</script>

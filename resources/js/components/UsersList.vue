<style lang="scss" scoped>
.ph-item {
  background: transparent;
  border: 0;
  margin: 0;
  padding: 1.5rem;
}

.ph-picture {
  margin: 0;
  padding: 1rem;
}
</style>
<!--  SHOULD BE BACKEND -->
<template>
  <div class="my-3 row">
    <slot></slot>
    <div
      v-if="shouldShowExpectedToFinish"
      :class="`col-12 ${isRtl ? 'rtl':''}`"
    >
      <div class="alert alert-secondary">
        <i class="fa fa-info-circle" aria-hidden="true"></i>
        <span v-if="!isRtl">
          This will take time. Maybe <strong>({{ taskExpectedToFinishInMins }} Minutes)</strong>.
          Looks like you have more than 2800 {{task.baseName === 'fetchfollowers' ? 'follower': 'following'}}, and twitter allows to fetch only 2800 {{task.baseName === 'fetchfollowers' ? 'follower': 'following'}} in each 15 minute.
        </span>
        <span v-if="isRtl">
          هذه المهمة قد تستغرق بعض الوقت. ربما <strong>({{ taskExpectedToFinishInMins }} دقيقة)</strong>.
          على ما يبدو أن لديك أكثر من ٢٨٠٠ متابع، وتويتر يسمح فقط بنسخ ٢٨٠٠ متابع كل ١٥ دقيقة.
        </span>
      </div>
    </div>
    <div :class="`col-12 usersList__controls__fluidContainer ${isRtl ? 'rtl':''}`">
      <div class="row usersList__controls__container">
        <div class="col-sm-8 p-0 usersList__controls__wrapper">
          <div class="usersList__searchInfo__container d-flex justify-content-between">
            <div class="usersList__searchInfo">
              <span>
                {{__('total_users')}}: {{intlFormat(totalUsers)}}
              </span>
              <img v-if="loading" :src="loadingGifSrc" class="loadingGif loadingGif--xs">
            </div>
            <div class="flex-1 d-flex align-items-center p-1">
              <div class="small text-muted" style="min-width: 70px;">
                {{__('sorted_by')}}:
              </div>
              <div class="usersList__sortDescription__container">
                <span class="usersList__sortDescription font-mono" v-for="(key, index) in orderFields">
                  <span v-if="key === 'following_id'">
                    {{ isRtl ? 'ترتيب المتابعة':'Following Order'}}:
                  </span>
                  <span v-if="key !== 'following_id'">
                    {{columns.find(x => x.sortField === key).label}}:
                  </span>
                  <span v-if="orderDirections[index] === 'asc'" class="oi text-muted" data-glyph="arrow-top"></span>
                  <span v-if="orderDirections[index] === 'desc'" class="oi text-muted" data-glyph="arrow-bottom"></span>
                  {{orderDirections[index] === 'asc' ? __('ascending') : __('descending')}}
                </span>
              </div>
            </div>
            <div class="usersList__searchInfo">
              {{__('search_results')}}: {{intlFormat(filteredUsers.length)}}
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
              <input v-model="searchKeywords" type="text" class="form-control" :placeholder="__('search_in_users_list')" aria-label="Search">
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
    <div class="col-12">
        <portal-target class="overflow-auto py-1" name="users-list-pager" />
    </div>
    <div :class="`col-12 ${isRtl ? 'rtl':''}`">
        <div class="table-responsive users__container">
          <users-list-datatable
            class="usersList__table"
            ref="users-list-datatable"
            :per-page="perPageInt"
            :columns="columns"
            :data="filteredUsers"
          >
              <template slot-scope="{ row, columns }">
                <tr v-if="row === undefined || loading" class="">
                  <td>
                    <div class="ph-item animated fadeOut infinite animation-3s p-0 d-block">
                      <div class="ph-picture m-auto" style="height: 50px; width: 50px; border-radius: 100%;"></div>
                    </div>
                  </td>
                  <td>
                    <div class="ph-item animated fadeOut infinite animation-3s">
                      <span class="ph-picture" style="height: 20px; width: 60px;"></span>
                    </div>
                  </td>
                  <td>
                    <div class="ph-item animated fadeOut infinite animation-3s">
                      <span class="ph-picture" style="height: 20px; width: 60px;"></span>
                    </div>
                  </td>
                  <td>
                    <div class="ph-item animated fadeOut infinite animation-3s">
                      <span class="ph-picture" style="height: 20px; width: 60px;"></span>
                    </div>
                  </td>
                  <td>
                    <div class="ph-item animated fadeOut infinite animation-3s">
                      <span class="ph-picture" style="height: 20px; width: 60px;"></span>
                    </div>
                  </td>
                  <td>
                    <div class="ph-item animated fadeOut infinite animation-3s">
                      <span class="ph-picture" style="height: 20px; width: 60px;"></span>
                    </div>
                  </td>
                  <td>
                    <div class="ph-item animated fadeOut infinite animation-3s">
                      <span class="ph-picture" style="height: 20px; width: 60px;"></span>
                    </div>
                  </td>
                  <td>
                    <div class="ph-item animated fadeOut infinite animation-3s">
                      <span class="ph-picture" style="height: 20px; width: 100px;"></span>
                    </div>
                  </td>
                </tr>
                <tr v-if="! loading && row !== undefined">
                  <td
                  >
                    <div
                      v-if="!row.background_image && row.background_color"
                      :class="`user__backgroundImage`"
                      :style="`background: #${row.background_color}`"
                    ></div>
                    <img
                      v-if="row.background_image"
                      @error="imageOnError"
                      :src="userPlaceholder"
                      :data-src="`${row.background_image}/mobile`"
                      class="lazy user__backgroundImage"
                    >
                    <div class="user__avatarContainer">
                      <img
                        style="width: 48px; height: 48px;"
                        @error="avatarOnError"
                        :src="userPlaceholder"
                        :data-src="`${isLocal ? '' : window.TwUtils.assetsUrl}avatars/${row.id_str}.png`"
                        :data-tweep-avatar="row.avatar"
                        class="lazy rounded-circle user__avatar"
                      >
                      <span
                        v-if="row.verified"
                        class="verifiedMark"
                      >
                        <span class="oi" data-glyph="check"></span>
                      </span>
                      <span v-if="row.followed_by">
                        <br>
                        <span class="followedByMark">
                          Follows You
                        </span>
                      </span>
                    </div>
                  </td>
                  <td>
                    <a
                      target="_blank"
                      :href="`https://twitter.com/${row.screen_name}`">
                        @{{row.screen_name}}
                    </a>
                  </td>
                  <td :class="`text-${isRtlText(row.name) ? 'right dir-rtl' : 'left'}`">
                    {{`${row.name}`}}
                  </td>
                  <td>
                    <template v-if="task.baseName === 'fetchfollowers'">
                      <template v-if="row.followed_by_me">
                        <span
                          class="d-none"
                        >
                          followed_by_me:Yes
                        </span>
                        <span
                          class="oi"
                          data-glyph="check"
                        ></span>
                      </template>
                      <template v-if="!row.followed_by_me">
                        <span
                          class="d-none"
                        >
                          followed_by_me:No
                        </span>
                        <span
                          class="oi"
                          data-glyph="x"
                        ></span>
                      </template>
                    </template>
                    <template v-if="task.baseName === 'fetchfollowing'">
                      <template v-if="row.followed_by">
                        <span
                          class="d-none"
                        >
                          followed_by:Yes
                        </span>
                        <span
                          class="oi"
                          data-glyph="check"
                        ></span>
                      </template>
                      <template v-if="!row.followed_by">
                        <span
                          class="d-none"
                        >
                          followed_by:No
                        </span>
                        <span
                          class="oi"
                          data-glyph="x"
                        ></span>
                      </template>
                    </template>
                  </td>
                  <td>
                    {{ intlFormat(row.friends_count) }}
                  </td>
                  <td>
                    {{ intlFormat(row.followers_count) }}
                  </td>
                  <td>
                    {{ intlFormat(row.statuses_count) }}
                  </td>
                  <td>
                    <div
                      :class="`pre-line text-${isRtlText(row.description) ? 'right dir-rtl' : 'left'}`"
                      v-html="parseTweetText(row.description)"
                    >
                    </div>
                  </td>
                </tr>
              </template>
          </users-list-datatable>
          <portal-target name="users-list-pager" />
          <portal to="users-list-pager">
            <users-list-datatable-pager
              v-model="page"
              @vue-datatable::set-page="page = $event"
              type="long"
              :per-page="perPageInt"
            ></users-list-datatable-pager>
          </portal>
        </div>
    </div>
  </div>
</template>

<script>
import Vue from 'vue'
import get from 'lodash/get'
import EventBus from '@/EventBus'
import debounce from 'lodash/debounce'
import orderBy from 'lodash/orderBy'
import {VuejsDatatableFactory} from 'vuejs-datatable'
import { searchArrayByFields } from '@/search'

const usersListDatatable = VuejsDatatableFactory.useDefaultType(false).registerTableType(
  `users-list-datatable`,
  tableType => {
    tableType.mergeSettings({
      table: {
        class: `table table-striped usersList__table`,
      },
      pager: {
        classes: {
          li: `page-item`,
          pager: `twutils_pagination pagination text-center m-0 px-3 w-100`,
          selected: `active`,
        },
      },
    })
    return tableType
  }
)

usersListDatatable.install(Vue)

const isRtl = window.TwUtils.locale === `ar`

export default {
  components: {
  },
  props: {
    refineUserFunc: {
      type: Function,
    },
    selectable: {
      type: Boolean,
      default: false,
    },
    task: {
      type: Object,
      default: () => { return {} },
    },
  },
  data () {
    return {
      users: [],

      page: 1,
      perPage: `50`,
      columns: [
        {
          label: (isRtl ? `الصورة الرمزية` : `Avatar`),
          width: `100px`,
          sortField: `following_id`,
        },
        {
          label: (isRtl ? `اسم المستخدم` : `Handler`),
          width: `180px`,
          sortField: `screen_name`,
        },
        {
          label: (isRtl ? `الاسم` : `Name`),
          width: `200px`,
          sortField: `name`,
        },
        {
          label: this.task.baseName === `fetchfollowers` ? (isRtl ? `مُتابَع من قبلي` : `Followed by me`) : (isRtl ? `هل يُتابِعَك` : `Follows You`),
          width: `100px`,
          sortField: this.task.baseName === `fetchfollowers` ? `followed_by_me` : `followed_by`,
        },
        {
          label: (isRtl ? `المُتابَعين` : `Following`),
          width: `100px`,
          sortField: `friends_count`,
        },
        {
          label: (isRtl ? `المُتابِعين` : `Followers`),
          width: `100px`,
          sortField: `followers_count`,
        },
        {
          label: (isRtl ? `التغريدات` : `Tweets`),
          width: `100px`,
          sortField: `statuses_count`,
        },
        {
          label: (isRtl ? `التعريف` : `Bio`),
          width: ``,
          sortField: `description`,
        },
      ],

      loading: true,
      searchKeywords: ``,
      debouncedSearch: null,
      debouncedAfterFiltering: null,
      searchFilter: users => users,
      selectedSorts: {},
      taskView: null,
    }
  },
  watch: {
    searchKeywords (...args) {
      this.$nextTick(this.debouncedSearch)
    },
    page () {
      this.search()
    },
    perPage () {
      this.loading = true
      this.$nextTick(this.debouncedSearch)
    },
    selectedSorts () {
      if (this.taskView) {
        this.debouncedSearch()
      }
    },
  },
  mounted () {
    this.debouncedSearch = debounce(t => {
      return this.search()
    }, 1000)
    this.debouncedAfterFiltering = debounce(t => {
      return this.afterFiltering()
    }, 300)

    if (this.task.baseName === `fetchfollowers`) {
      this.initFollowers()
    } else if (this.task.baseName === `fetchfollowing`) {
      this.initFollowings()
    }

    this.$nextTick(x => {
      $(`img.lazy`).unveil(100)

      this.prepareTableHeaders()
      this.hideLoading()
    })
  },
  methods: {
    get,
    initFollowers () {
      if (this.task.followers) {
        this.users = this.task.followers.map(this.refineUserFunc).reverse()
        this.loading = false
        return
      }

      if (this.task.status === `completed`) {
        return this.fetchUsersFromView()
      }
      this.fetchUsers(1)
    },
    initFollowings () {
      if (this.task.followings) {
        this.users = this.task.followings.map(this.refineUserFunc).reverse()
        this.loading = false
        return
      }

      if (this.task.status === `completed`) {
        return this.fetchUsersFromView()
      }
      this.fetchUsers(1)
    },
    fetchUsersFromView () {
      this.loading = true

      axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.task.id}/view`, {
        params: {
          page: this.page,
          perPage: this.perPage,
          search: this.searchKeywords,
          orderFields: this.orderFields,
          orderDirections: this.orderDirections,
        },
      })
        .then(resp => {
          const currentPage = resp.data.current_page
          const lastPage = resp.data.last_page

          this.taskView = resp.data
          this.users = resp.data.data.map(this.refineUserFunc)

          this.$nextTick(this.debouncedAfterFiltering)

          this.loading = false
        })
    },
    fetchUsers (page) {
      axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.task.id}/data?page=${page}`)
        .then(resp => {
          const currentPage = resp.data.current_page
          const lastPage = resp.data.last_page

          this.users = this.users.concat(resp.data.data.map(this.refineUserFunc))

          if (currentPage === 1) {
            this.$nextTick(this.debouncedAfterFiltering)
          }

          if (currentPage !== lastPage) {
            this.fetchUsers(currentPage + 1)
          }

          if (currentPage === lastPage) {
            this.loading = false
          }
        })
    },
    prepareTableHeaders () {
      const sortFields = this.columns.map(x => x.sortField)
      const widths = this.columns.map(x => x.width)

      const colGroup = document.createElement(`colgroup`)

      widths.map((width, i) => {
        const colEl = document.createElement(`col`)

        if (width) { colEl.width = width }

        colGroup.appendChild(colEl)
      })

      $(this.$refs[`users-list-datatable`].$el)
        .prepend(colGroup)

      $(this.$refs[`users-list-datatable`].$el)
        .find(`thead th`)
        .each((i, th) => {
          if (!sortFields[i]) { return }

          const sortIconElement = document.createElement(`i`)

          sortIconElement.classList.add(`fa`)
          sortIconElement.classList.add(`fa-sort`)

          sortIconElement.sortState = 0
          sortIconElement.sortField = sortFields[i]

          $(th).click((event) => {
            this.sort(sortIconElement, event)
          })

          $(th.querySelector(`span`)).prepend(sortIconElement)
        })
    },
    sort (el, event) {
      if (el.sortState === 1) {
        el.sortState = -1
      } else {
        el.sortState += 1
      }

      this.$delete(this.selectedSorts, el.sortField)

      if (el.sortState !== 0) {
        const newSortObject = {}
        newSortObject[el.sortField] = el.sortState === 1 ? `asc` : `desc`

        this.selectedSorts = Object.assign(newSortObject, this.selectedSorts)
      }

      $(el).removeClass(`fa-sort fa-sort-up fa-sort-down`)

      if (el.sortState === 0) {
        $(el).addClass(`fa-sort`)
      } else if (el.sortState === 1) {
        $(el).addClass(`fa-sort-up`)
      } else {
        $(el).addClass(`fa-sort-down`)
      }

      this.$nextTick(this.debouncedAfterFiltering)
    },
    search () {
      if (this.taskView) {
        this.fetchUsersFromView()

        return
      }
      const searchFields = [`screen_name`, `name`, `description`,]
      this.searchFilter = users => searchArrayByFields(users, this.searchKeywords, searchFields)

      this.$nextTick(this.debouncedAfterFiltering)
    },
    afterFiltering () {
      this.loading = false
      this.$refs[`users-list-datatable`].$el.querySelectorAll(`.user__avatar`).forEach(x => x.isRemote = false)
      $(this.$refs[`users-list-datatable`]).find(`.user__avatar`).attr(`src`, this.userPlaceholder)
      this.$nextTick(x => {
        $(`img.lazy`).unveil(100)
      }, 100)
    },
    avatarOnError (e) {
      const el = e.target

      if (el.isRemote) {
        el.src = this.grayBase64Image
        return
      }

      el.isRemote = true
      el.src = el.dataset.tweepAvatar
    },
    imageOnError (e) {
      const el = e.target
      el.src = this.grayBase64Image
    },
  },
  computed: {
    totalUsers () {
      if (this.taskView) {
        return this.taskView.totalCount
      }
      return this.users.length
    },
    orderFields () {
      return Object.keys(this.selectedSorts)
    },
    orderDirections () {
      return Object.values(this.selectedSorts)
    },
    filteredUsers () {
      if (this.taskView) {
        return (Array((this.taskView.from || 1) - 1)).concat(this.users).concat((Array(this.taskView.total - (this.taskView.to || 0))))
      }

      let users = this.users
      const filters = []

      if (this.searchFilter !== null && this.searchKeywords.length > 0) {
        filters.push(this.searchFilter)
      }

      filters.forEach((filter) => {
        users = filter(users)
      })

      return orderBy(users, this.orderFields, this.orderDirections)
    },
    perPageInt () {
      return parseInt(this.perPage)
    },
    shouldShowExpectedToFinish () {
      return this.task.status == `queued` &&
              [`fetchfollowing`, `fetchfollowers`,].includes(this.task.baseName) &&
              get(this.user, `social_users[0].followers_count`, 0) > 2800 &&
              parseInt(this.taskExpectedToFinishInMins) > 0
    },
    taskExpectedToFinishInMins () {
      if (![`fetchfollowing`, `fetchfollowers`,].includes(this.task.baseName)) {
        return ``
      }

      let expectedToFinish = 0
      let remainingUsers = 0

      if (this.task.baseName === `fetchfollowers`) {
        remainingUsers = get(this.user, `social_users[0].followers_count`, 0) - get(this.task, `followers_count`, 0)
      }

      if (this.task.baseName === `fetchfollowing`) {
        remainingUsers = get(this.user, `social_users[0].friends_count`, 0) - get(this.task, `followings_count`, 0)
      }

      // We can only fetch 2800 user in 15 mins.
      expectedToFinish = Math.round((remainingUsers / 2800 * 15) - 15)

      if (expectedToFinish < 1) {
        return `0`
      }

      return expectedToFinish
    },
  },
}
</script>

<style lang="scss">
.users__container {

  .followedByMark {
    font-size: 12px;
    background: hsla(0, 0%, 90%, 1);
    color: hsla(210, 10%, 38%, 1);
    padding: 0px 5px;
  }

  .verifiedMark {
      position: absolute;
      left: 54px;
      font-size: 14px;
      background: lightskyblue;
      margin-top: 20px;
      border-radius: 50px;
      width: 20px;
      height: 20px;
      padding-left: 2px;
      opacity: 0.5;

      @at-root .rtl & {
        right: 54px;
      }
  }
}
.usersList__controls__container {
  background: whitesmoke;
  border-radius: 1rem;
  margin: 1rem;
  border: 1px solid #ccc;
  border-bottom: none;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
  margin-bottom: 0;
}

.usersList__table {
  table-layout: fixed;

  thead > tr > th, tbody > tr > td {
    font-size: 0.8rem;
    word-break: break-word;
  }

  thead > tr > th > span {
    display: flex;
    justify-content: flex-start;;
    align-items: center;

    i.fa {
      color: #a3a3a3;

      margin-right: 6px;

      @at-root .rtl & {
        margin-right: unset;
        margin-left: 6px;
      }
    }
  }

  thead > tr > th {
    padding: 0.75rem 0.2rem;
    border: 1px solid #eee;
    cursor: pointer;
  }
}
.usersList__searchInfo {
    font-size: 0.7rem;
    background: #eee;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.usersList__sortDescription__container {
    font-size: 0.7rem;
}

.usersList__sortDescription {
    background: #eeeeee;
    padding: 0 0.3rem;
    box-shadow: 1px 1px 1px #ccc;
    display: inline-block;
    margin: 0.3rem 0.2rem;
}

</style>
<template>
  <div class="my-3 row">
    <slot></slot>
    <div :class="`col-12 ${isRtl ? 'rtl':''}`">
      <div class="row usersList__controls__container">
        <div class="col-sm-8 p-0 mh-100 d-flex flex-column justify-content-between" :style="`border-${isRtl ? 'left':'right'}: 1px dashed #ccc;`">
          <div class="usersList__searchInfo__container d-flex justify-content-between" style="border-bottom: 1px solid #ccc;">
            <div class="usersList__searchInfo" style="border-top-left-radius: 1rem; border-right: 1px solid #ccc;">
              {{__('total_users')}}: {{intlFormat(users.length)}}
              <img v-if="loading" style="height: 30px;" :src="loadingGifSrc" class="m-auto loadingGif">
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
            <div class="usersList__searchInfo" style="border-left: 1px solid #ccc;">
              {{__('search_results')}}: {{intlFormat(filteredUsers.length)}}
            </div>
          </div>
          <portal-target class="overflow-auto py-1" name="userslist-pager" />
        </div>
        <div class="col-sm-4 mh-100">
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
    <div :class="`col-12 ${isRtl ? 'rtl':''}`">
        <div class="table-responsive users__container">
          <userslist-datatable
            class="usersList__table"
            ref="userslist-datatable"
            :per-page="perPageInt"
            :columns="columns"
            :data="filteredUsers"
          >
              <template slot-scope="{ row, columns }">
                <tr>
                  <td>
                    <span class="d-none">
                      {{row.following_id.toString().padStart(10, '0')}}
                    </span>
                    <img
                      style="width: 48px;"
                      @error="avatarOnError"
                      :src="userPlaceholder"
                      :data-src="`${isLocal ? '' : window.TwUtils.assetsUrl}avatars/${row.id_str}.png`"
                      :data-tweep-avatar="row.avatar"
                      class="lazy rounded-circle user__avatar"
                    >
                    <span class="d-none">
                      verified:{{row.verified ? 'yes' : 'no'}}
                    </span>
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
                      v-html="parseTweet(row.description)"
                    >
                    </div>
                  </td>
                </tr>
              </template>
          </userslist-datatable>
          <portal-target name="userslist-pager" />
          <portal to="userslist-pager">
            <userslist-datatable-pager
              v-model="page"
              type="long"
              :per-page="perPageInt"
              @change="pageChanged"
            ></userslist-datatable-pager>
          </portal>
        </div>
    </div>
  </div>
</template>

<script>
import Vue from 'vue'
import EventBus from '@/EventBus'
import debounce from 'lodash/debounce'
import orderBy from 'lodash/orderBy'
import DatatableFactory from 'vuejs-datatable/dist/vuejs-datatable.esm.js'
import { searchArrayByFields } from '@/search'

const usersListDatatable = DatatableFactory.useDefaultType(false).registerTableType(
  `userslist-datatable`,
  tableType => {
    tableType.mergeSettings({
      table: {
        class: `table table-hover table-striped usersList__table`,
      },
      pager: {
        classes: {
          li: `page-item`,
          a: `page-link`,
          pager: `pagination text-center m-0 p-3 w-100`,
          selected: `active`,
        },
      },
    })
    return tableType
  }
)

Vue.use(usersListDatatable)

const isRtl = window.TwUtils.locale === `ar`

export default {
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
      perPage: `100`,
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
      searchFilter: users => users,
      selectedSorts: {},
    }
  },
  watch: {
    searchKeywords (...args) {
      this.$nextTick(this.debouncedSearch)
    },
  },
  mounted () {
    this.debouncedSearch = debounce(t => {
      return this.search()
    }, 1000)

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
    initFollowers () {
      if (this.task.followers) {
        this.users = this.task.followers.map(this.refineUserFunc).reverse()
        this.loading = false
        return
      }

      this.fetchUsers(1)
    },
    initFollowings () {
      if (this.task.followings) {
        this.users = this.task.followings.map(this.refineUserFunc).reverse()
        this.loading = false
        return
      }

      this.fetchUsers(1)
    },
    fetchUsers (page) {
      axios.get(`${window.TwUtils.apiBaseUrl}tasks/${this.task.id}/data?page=${page}`)
        .then(resp => {
          const currentPage = resp.data.current_page
          const lastPage = resp.data.last_page

          this.users = this.users.concat(resp.data.data.map(this.refineUserFunc))

          if (currentPage === 1) {
            this.$nextTick(this.pageChanged)
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

      $(this.$refs[`userslist-datatable`].$el)
        .prepend(colGroup)

      $(this.$refs[`userslist-datatable`].$el)
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
      if (el.sortState === 0) { el.sortState = 1 } else if (el.sortState === 1) { el.sortState = -1 } else if (el.sortState === -1) { el.sortState = 0 }

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

      this.$nextTick(this.pageChanged)
    },
    search () {
      const searchFields = [`screen_name`, `name`, `description`,]
      this.searchFilter = users => searchArrayByFields(users, this.searchKeywords, searchFields)

      this.$nextTick(this.pageChanged)
    },
    pageChanged () {
      this.$refs[`userslist-datatable`].$el.querySelectorAll(`.user__avatar`).forEach(x => x.isRemote = false)
      $(this.$refs[`userslist-datatable`]).find(`.user__avatar`).attr(`src`, this.userPlaceholder)
      this.$nextTick(x => {
        $(`img.lazy`).unveil(100)
      }, 100)
    },
    avatarOnError (e) {
      const el = e.target

      if (el.isRemote) {
        el.src = `data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNc+B8AAkcBolySrScAAAAASUVORK5CYII=`
        return
      }

      el.isRemote = true
      el.src = el.dataset.tweepAvatar
    },
  },
  computed: {
    orderFields () {
      return Object.keys(this.selectedSorts)
    },
    orderDirections () {
      return Object.values(this.selectedSorts)
    },
    filteredUsers () {
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
  },
}
</script>

<style lang="sass" scoped>

</style>
<template>
  <div class="container">
    <div class="row">
      <div class="col-12 col-md-8 offset-md-2">
        <slot name="header"></slot>
        <hr>
        <label>
          {{__('options')}}:
        </label>
        <div class="mx-3 d-flex flex-column addTask__option addTask__option--withMedia">
          <div class="form-check" style="min-width: 150px;">
            <input v-model="withMedia" class="form-check-input" id="withMedia" type="checkbox" autocomplete="off">
            <label class="form-check-label" for="withMedia">
              {{__('with_media')}}
            </label>
          </div>
          <div :class="`addTask__optionInfo px-4`">
            <div class="d-flex align-items-center">
              <i class="fa fa-info-circle p-3" aria-hidden="true"></i>
              <span v-if="!isRtl">
                Media option gives you the ability to download the tweets <b>along with it's associated media (Videos, Images, Gifs).</b>
              </span>
              <span v-if="isRtl">
                خيار مع الوسائط يوفّر لك إمكانية تحميل التغريدات <b>بالإضافة إلى الوسائط المرتبطة بها (فيديوهات، صور، صور متحركة Gifs).</b>
              </span>
            </div>
            <div class="d-flex align-items-center">
              <i class="fa fa-info-circle p-3" aria-hidden="true"></i>
              <i v-if="!isRtl">Enabling this option may results in a larger ZIP file</i>
              <i v-if="isRtl">تفعيل هذا الخيار سيجعل ملف التحميل ZIP أكبر حجماً</i>
            </div>
          </div>
        </div>
        <hr>
      </div>
      <div class="col-12 col-md-8 offset-md-2 text-right">
        <button @click="start" class="task__actionButton">
          <span class="oi" data-glyph="bolt"></span>
          {{ actionButton }}
          <small v-if="withMedia">
            ({{__('with_media').toLowerCase()}})
          </small>
        </button>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <span v-if="loading">
          {{ loadingAddMessage }}
        </span>
        <div class="mt-2 alert alert-danger" v-for="error in errors" v-if="errors.length > 0" v-text="error"></div>
      </div>
    </div>
  </div>
</template>

<script>
import clone from 'lodash/clone'

const data = {
  loading: false,
  errors: [],
  withMedia: false,
}

export default {
  props: {
    actionButton: {
      type: String,
    },
    loadingAddMessage: {
      type: String,
    },
    endpoint: {
      type: String,
    },
    withMediaEndpoint: {
      type: String,
    },
    ongoingUserTweets: {
      type: String,
    },
  },
  data () {
    return clone(data)
  },
  mounted () {

  },
  methods: {
    start () {
      let targetedTask = this.endpoint
      if (this.withMedia) {
        targetedTask = this.withMediaEndpoint
      }

      this.loading = true
      this.showLoading()
      axios.post(`${window.TwUtils.baseUrl}api/${targetedTask}`)
        .then((response) => {
          this.hideLoading()
          this.loading = false
          Object.keys(data).forEach(a => this[a] = clone(data[a]))
          if (response.data.ok) {
            this.$router.push(`/`)
          }
        })
        .catch((error) => {
          Object.keys(data).forEach(a => this[a] = clone(data[a]))
          this.hideLoading()
          this.loading = false

          if (error.response.data && Array.isArray(error.response.data.errors) && error.response.data.errors.length > 0) {
            this.errors = error.response.data.errors
          } else if (error.response.status === 422) {
            this.errors.push(this.ongoingUserTweets)
          } else {
            this.errors.push(`There is an error creating this task..`)
          }
        })
    },
  },
}
</script>

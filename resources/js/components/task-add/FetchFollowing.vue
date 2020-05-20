<style lang="sass" scoped>

</style>
<template>
  <div class="container">
    <div class="row">
      <div class="col-8">
        <strong>{{__('fetch_following')}}</strong> {{__('fetch_following_desc')}}
      </div>
      <button @click="start" class="col-4 task__actionButton">
        <span class="oi" data-glyph="bolt"></span>
        {{__('startTask')}}
      </button>
    </div>
    <div class="row">
      <div class="col-12">
        <span v-if="fetchFollowingLoading">
          {{__('adding_fetch_following')}}
        </span>
        <div class="mt-2 alert alert-danger" v-for="error in errors" v-if="errors.length > 0" v-text="error"></div>
      </div>
    </div>
  </div>
</template>

<script>
import clone from 'lodash/clone'

const data = {
  fetchFollowingLoading: false,
  errors: [],
}

export default {
  data () {
    return clone(data)
  },
  mounted () {

  },
  methods: {
    start () {
      Object.keys(data).forEach(a => this[a] = clone(data[a]))
      this.fetchFollowingLoading = true
      this.showLoading()
      axios.get(`${window.TwUtils.baseUrl}api/following`)
        .then((response) => {
          this.hideLoading()
          this.fetchFollowingLoading = false
          if (response.data.ok) {
            this.$router.push(`/`)
          }
        })
        .catch((error) => {
          this.hideLoading()
          this.fetchFollowingLoading = false

          if (error.response.data && Array.isArray(error.response.data.errors) && error.response.data.errors.length > 0) {
            this.errors = error.response.data.errors
          } else if (error.response.status === 422) {
            this.errors.push(this.__(`ongoing_fetch_following`))
          } else {
            this.errors.push(`There is an error creating this task..`)
          }
        })
    },
  },
}
</script>

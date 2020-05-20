<style scoped>

</style>
<template>
  <div :class="`${isRtl?'rtl':'ltr'} alert alert-warning`">
    <div class="text-small">
      {{__('accountToBeRemoved')}} {{ accountRemovalDate }}.
      <a :href="`${TwUtils.baseUrl}/cancelDeleteMe`" class="text-muted ml-5">
        {{__('cancel')}}
      </a>
    </div>
  </div>
</template>

<script>
const isRtl = window.TwUtils.locale === `ar`

let removeDate = null

export default {
  destroyed () {
    clearInterval(this.updateDateInterval)
  },
  data () {
    return {
      isRtl,
      TwUtils: window.TwUtils,
      user: window.TwUtils.user,
      accountRemovalDate: null,
      updateDateInterval: null,
    }
  },
  mounted () {
    removeDate = window.moment(this.user.remove_at).tz(window.guessedTimeZone)
    this.updateDate()
    this.updateDateInterval = setInterval(this.updateDate, 2000)
  },
  methods: {
    updateDate () {
      this.accountRemovalDate = removeDate.fromNow()
    },
  },
}
</script>

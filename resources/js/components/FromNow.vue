<script>
// Credits: Linus Borg https://stackoverflow.com/a/44305575/4330182
export default {
  props: {
    tag: { type: String, default: `span`, },
    value: { type: String, default: () => window.moment().toISOString(), },
    interval: { type: Number, default: 1000, },
    hasPopover: { type: Boolean, default: false, },
    hasTooltip: { type: Boolean, default: false, },
  },
  data () {
    return { fromNow: window.moment(this.value).fromNow(), }
  },
  mounted () {
    this.intervalId = setInterval(this.updateFromNow, this.interval)
    this.$watch(`value`, this.updateFromNow)
    if (this.hasPopover) { this.$nextTick(this.loadPopover) }

    if (this.hasTooltip) { this.$nextTick(this.loadTooltip) }
  },
  beforeDestroy () {
    clearInterval(this.intervalId)

    if (this.hasPopover) { $(this.$el).popover(`dispose`) }

    if (this.hasTooltip) { $(this.$el).tooltip(`dispose`) }
  },
  methods: {
    loadPopover () {
      $(this.$el).popover({ animation: true, })
    },
    loadTooltip () {
      $(this.$el).tooltip({ animation: true, })
    },
    updateFromNow () {
      const newFromNow = window.moment(this.value).fromNow(this.dropFixes)
      if (newFromNow !== this.fromNow) {
        this.fromNow = newFromNow
      }
    },
  },
  render (h) {
    const attrs = {}

    if (this.hasPopover || this.hasTooltip) {
      attrs[`data-placement`] = `bottom`
      attrs[`data-container`] = `body`
      attrs.title = this.title
      attrs[`data-trigger`] = `hover`
    }

    if (this.hasPopover) {
      attrs[`data-toggle`] = `popover`
    }

    if (this.hasTooltip) {
      attrs[`data-toggle`] = `tooltip`
    }

    return h(this.tag, { attrs, innerHTML: this.fromNow, }, this.fromNow)
  },
}
</script>

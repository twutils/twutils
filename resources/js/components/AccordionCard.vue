<style>
.accordionCardChevron {
  position: absolute;
  right: 1rem;
  top: 1rem;
  font-size: 12px;
  opacity: 0.5;
}
.wrappedButtonText {
  white-space: normal;
  word-break: break-word
}
</style>
<template>
<div class="card">
  <div class="card-header" :id="`heading${id}`">
    <h5 class="mb-0">
      <button class="wrappedButtonText btn btn-link collapsed w-100" type="button" data-toggle="collapse" :data-target="`#collapse${id}`" aria-expanded="false" :aria-controls="`collapse${id}`">
        <slot name="header"></slot>
      </button>
      <span :data-glyph="`chevron-${notCollapsed ? 'bottom':'top'}`" class="oi accordionCardChevron"></span>
    </h5>
  </div>
  <div :id="`collapse${id}`" class="collapse" :aria-labelledby="`heading${id}`">
    <div class="card-body">
      <slot name="body"></slot>
    </div>
  </div>
</div>
</template>

<script>
export default {
  data () {
    return {
      notCollapsed: true,
    }
  },
  mounted () {
    $(this.$el).find(`.collapse`).on(`show.bs.collapse`, () => {
      this.notCollapsed = false
    })
    $(this.$el).find(`.collapse`).on(`hide.bs.collapse`, () => {
      this.notCollapsed = true
    })
  },
  computed: {
    id () {
      return this._uid
    },
  },
}
</script>

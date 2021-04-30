import Vue from 'vue'

import PortalVue from 'portal-vue'
Vue.config.productionTip = false
Vue.config.devtools = process.env.NODE_ENV === `development`
require(`@/bootstrap`)

require(`jquery-unveil`)
require(`ekko-lightbox`)

window.Vue = Vue

window.VueRouter = require(`vue-router`)

window.VuePaginate = require(`vue-paginate`)

window.Vue.use(VueRouter)
window.Vue.use(VuePaginate)
window.Vue.use(PortalVue)
window.__ = require(`@/langManager`).default
window.Humanize = require(`humanize-plus`)
window.Vue.mixin(require(`@/mixin`).default)

Vue.component(`main-component`, require(`@/components/MainComponent.vue`).default)
Vue.component(`task`, require(`@/components/Task.vue`).default)
Vue.component(`account-removal`, require(`@/components/AccountRemoval.vue`).default)

if (document.querySelector(`#twutils`) !== null) {
  const twutils = new Vue({
    el: `#twutils`,
  })
}

$(document).ready(() => {
  $(`body`).addClass(`offset-navbar`)
  setTimeout(() => {
    $(document).find(`[data-hide-after]`).each((i, el) => {
      setTimeout(() => {
        $(el).fadeOut()
      }, parseInt(el.dataset.hideAfter))
    })
  }, 1000)
  if (window.preventHideLoadingOnReady === undefined || !window.preventHideLoadingOnReady) { $(`.loading-gif`).fadeOut() }
})

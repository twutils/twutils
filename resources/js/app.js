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

require(`imports-loader?define=>false!datatables.net`)(window, window.$)
require(`imports-loader?define=>false!datatables.net-bs4`)(window, window.$)
require(`imports-loader?define=>false!datatables.net-select`)(window, window.$)
require(`imports-loader?define=>false!datatables.net-select-bs4`)(window, window.$)
require(`imports-loader?define=>false!datatables.net-responsive`)(window, window.$)
require(`imports-loader?define=>false!datatables.net-responsive-bs4`)(window, window.$)

if (window.TwUtils && window.TwUtils.locale != `en`) {
  window.$.extend(window.$.fn.dataTable.defaults, {
    language: require(`@/dataTables.arabic.lang.json`),
  })
}

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

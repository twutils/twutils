import Vue from 'vue'
import PortalVue from 'portal-vue'

Vue.component(`account-removal`, require(`@/components/AccountRemoval.vue`).default)
Vue.component(`contact-form`, require(`@/components/contact-form.vue`).default)

const Turbolinks = require(`turbolinks`)
require(`@/bootstrap`)

Vue.use(PortalVue)

$(window).scroll(() => {
  const offset = $(window).scrollTop()
  $(`body`).toggleClass(`offset-navbar`, offset > 50)
})

const ready = () => {
  $(document).ready(() => {
    setTimeout(() => {
      $(document).find(`[data-hide-after]`).each((i, el) => {
        setTimeout(() => {
          $(el).fadeOut()
        }, parseInt(el.dataset.hideAfter))
      })
    }, 1000)

    if (window.preventHideLoadingOnReady === undefined || !window.preventHideLoadingOnReady) { $(`.loading-gif`).fadeOut() }
    $(`footer`).removeClass(`d-none`)

    new Vue({
      el: `.twutils__container`,
    })
  })
}

document.addEventListener(`turbolinks:load`, ready)
Turbolinks.start()

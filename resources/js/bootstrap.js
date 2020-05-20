window.moment = require(`moment-timezone`)
require(`moment/locale/ar`)
window.moment.locale(`en`)

window.Popper = require(`popper.js`).default

try {
  window.$ = window.jQuery = require(`jquery`)

  require(`bootstrap`)
} catch (e) {}

window.axios = require(`axios`)

window.axios.defaults.headers.common[`X-Requested-With`] = `XMLHttpRequest`

const token = document.head.querySelector(`meta[name="csrf-token"]`) || { content: window.TwUtils.csrfToken, }

if (token) {
  window.axios.defaults.headers.common[`X-CSRF-TOKEN`] = token.content || { content: window.TwUtils.apiToken, }
  window.axios.defaults.headers.common[`Accept-Language`] = window.TwUtils.locale
}

const apiToken = document.head.querySelector(`meta[name="api-token"]`)

if (apiToken) {
  window.axios.defaults.headers.common[`Authorization`] = `Bearer ` + apiToken.content
}

if (window.TwUtils && window.TwUtils.locale != `en`) {
  window.moment.locale(window.TwUtils.locale)
}

window.moment.tz.setDefault(window.TwUtils.timeZone)
window.guessedTimeZone = window.moment.tz.guess()

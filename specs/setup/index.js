import Vue from 'vue'
import EventBus from '@/EventBus'

import axios from 'axios'
import moxios from 'moxios'

Vue.config.productionTip = false
Vue.config.devtools = false

require(`@babel/polyfill/dist/polyfill.min.js`)

// window.Promise = require('promise-polyfill')

Vue.mixin({
  methods: {
    text: function () {
      return this.$el.textContent
    },
    html: function () {
      return this.$el.innerHTML
    },
  },
})

require(`./testUtils`)

window.TestData = require(`./../testData.json`)

window.TwUtils = window.TestData.clientData

require(`@/app`)

window.axios = axios
window.moxios = moxios
window.EventBus = EventBus

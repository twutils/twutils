import { mount, createLocalVue } from '@vue/test-utils'
import VueRouter from 'vue-router'

window.specComponent = window.vm = window.wrapper = null

window.mount = mount

window.createVue = (props = null, component = null) => {
  if (component == null) component = window.specComponent

  const localVue = createLocalVue()
  localVue.use(VueRouter)

  window.wrapper = mount(component, {
    propsData: props,
    localVue,
  })
  window.vm = window.wrapper.vm
}

window.substringCount = (needle, haystack) => {
  return haystack.split(needle).length - 1
}
/* eslint-disable no-console */
/* istanbul ignore next */
window.log = (...args) => {
  console.log(...args)
}

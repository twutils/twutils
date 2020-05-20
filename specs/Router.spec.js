import { shallowMount, mount, createLocalVue } from '@vue/test-utils'
import App from '@/components/MainComponent'
import VueRouter from 'vue-router'
import PortalVue from 'portal-vue'
import tasksView from '@/components/TasksView'
import sidebar from '@/components/Sidebar'
import tasksOverviewItem from '@/components/TasksOverviewItem'
import backupLikes from '@/components/task-add/BackupLikes'
import destroyLikes from '@/components/task-add/DestroyLikes'
import destroyTweets from '@/components/task-add/DestroyTweets'
import fetchFollowers from '@/components/task-add/FetchFollowers'
import fetchFollowing from '@/components/task-add/FetchFollowing'
import userTweets from '@/components/task-add/UserTweets'

const localVue = createLocalVue()
localVue.use(VueRouter)
localVue.use(PortalVue)
localVue.mixin(require(`@/mixin`).default)

/*
 * Purpose of this spec is to make sure components
 * are rendered according to the routes.
 *
 */

describe(`Router`, () => {
  it(`Add Task: backupLikes`, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/addTask/backupLikes`)

    setTimeout(() => {
      expect(wrapper.findComponent(backupLikes).exists()).to.equal(true)
      done()
    }, 40)
  })

  it(`Add Task: destroyLikes`, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/addTask/destroyLikes`)

    setTimeout(() => {
      expect(wrapper.findComponent(destroyLikes).exists()).to.equal(true)
      done()
    }, 40)
  })

  it(`Add Task: destroyTweets`, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/addTask/destroyTweets`)

    setTimeout(() => {
      expect(wrapper.findComponent(destroyTweets).exists()).to.equal(true)
      done()
    }, 40)
  })

  it(`Add Task: fetchFollowers`, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/addTask/fetchFollowers`)

    setTimeout(() => {
      expect(wrapper.findComponent(fetchFollowers).exists()).to.equal(true)
      done()
    }, 40)
  })

  it(`Add Task: fetchFollowing`, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/addTask/fetchFollowing`)

    setTimeout(() => {
      expect(wrapper.findComponent(fetchFollowing).exists()).to.equal(true)
      done()
    }, 40)
  })

  it(`Add Task: userTweets`, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/addTask/userTweets`)

    setTimeout(() => {
      expect(wrapper.findComponent(userTweets).exists()).to.equal(true)
      done()
    }, 40)
  })

  it(`Tasks View`, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/`)

    setTimeout(() => {
      expect(wrapper.findComponent(tasksView).exists()).to.equal(true)
      expect(wrapper.findComponent(sidebar).exists()).to.equal(true)
      expect(wrapper.findComponent(tasksOverviewItem).exists()).to.equal(true)
      done()
    }, 40)
  })

  it.skip(`Task: destroylikes `, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/1`)

    setTimeout(() => {
      expect(wrapper.findComponent().exists()).to.equal(true)
      done()
    }, 40)
  })

  it.skip(`Task: destroytweets `, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/1`)

    setTimeout(() => {
      expect(wrapper.findComponent().exists()).to.equal(true)
      done()
    }, 40)
  })

  it.skip(`Task: fetchfollowers `, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/1`)

    setTimeout(() => {
      expect(wrapper.findComponent().exists()).to.equal(true)
      done()
    }, 40)
  })

  it.skip(`Task: fetchfollowing `, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/1`)

    setTimeout(() => {
      expect(wrapper.findComponent().exists()).to.equal(true)
      done()
    }, 40)
  })

  it.skip(`Task: fetchlikes `, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/1`)

    setTimeout(() => {
      expect(wrapper.findComponent().exists()).to.equal(true)
      done()
    }, 40)
  })

  it.skip(`Task: fetchusertweets `, done => {
    const wrapper = mount(App, { localVue, })

    wrapper.vm.$router.push(`/1`)

    setTimeout(() => {
      expect(wrapper.findComponent().exists()).to.equal(true)
      done()
    }, 40)
  })
})

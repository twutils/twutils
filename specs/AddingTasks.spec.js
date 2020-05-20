import MainComponent from '@/components/MainComponent'

const tasksApiUrl = window.TwUtils.baseUrl + `api/tasks`

describe(`Adding Tasks`, () => {
  beforeEach(() => {
    moxios.install()
    window.specComponent = MainComponent
    window.location.hash = `#/`
    moxios.stubRequest(tasksApiUrl, {
      status: 200,
      response: [],
    })
  })

  afterEach(() => {
    moxios.uninstall()
    EventBus.clearHistory()
    vm.$destroy()
  })

  it(`mounts successfully..`, (done) => {
    createVue()

    moxios.wait(() => {
      expect(vm.$el.textContent)
        .not.to.equal(null)
      done()
    })
  })

  it(`Add Task: Destroy Tweets: Showing no-privilege warning for read-only users`, (done) => {
    window.location.hash = `#/addTask/destroyTweets`
    makeUserWithReadOnlyAccess()

    createVue()

    moxios.wait(() => {
      expect(vm.$el.textContent)
        .to.contain(`Click here to give us this access.`)
      done()
    })
  })

  it(`Add Task: Destroy Tweets: No warning for read-write user`, (done) => {
    window.location.hash = `#/addTask/destroyTweets`
    makeUserWithWriteAccess()

    createVue()

    moxios.wait(() => {
      expect(vm.$el.textContent)
        .to.not.contain(`Click here to give us this access.`)
      done()
    })
  })

  it(`Add Task: Destroy Tweets: Do nothing if there is no completed tasks`, (done) => {
    makeUserWithWriteAccess()

    createVue()
    vm.$router.push(`/addTask/destroyTweets`)

    setTimeout(x => {
      expect(vm.text())
        .to.contains(`Remove your Tweets.`)

      vm.$el.querySelector(`[data-action="startButton"]`)
        .dispatchEvent(new Event(`click`))

      setTimeout(x => {
        expect(vm.$el.querySelector(`.choseTaskBody`))
          .to.equal(null)
        done()
      }, 20)
    }, 50)
  })
})

const userHasWriteAccess = () => {
  return window.TwUtils.user.social_users.map(x => x.scope).reduce((a, b) => a.concat(b)).indexOf(`write`) != -1
}

const makeUserWithReadOnlyAccess = () => {
  if (userHasWriteAccess()) {
    window.TwUtils.user.social_users = window.TwUtils.user.social_users.map(socialUser => {
      return {
        ...socialUser,
        scope: [`read`, ],
      }
    })
  }
}

const makeUserWithWriteAccess = () => {
  if (!userHasWriteAccess()) {
    window.TwUtils.user.social_users = window.TwUtils.user.social_users.map(socialUser => {
      return {
        ...socialUser,
        scope: [`read`, `write`, ],
      }
    })
  }
}

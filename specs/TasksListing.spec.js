import MainComponent from '@/components/MainComponent'

const tasksApiUrl = window.TwUtils.baseUrl + `api/tasks`

describe(`Tasks Listing`, () => {
  beforeEach(() => {
    moxios.install()
    window.specComponent = MainComponent
    window.location.hash = `#/`
  })

  afterEach(() => {
    moxios.uninstall()
    EventBus.clearHistory()
    vm.$destroy()
    window.location.hash = `#/`
  })

  it(`says no tasks if there is no tasks..`, (done) => {
    moxios.stubRequest(tasksApiUrl, {
      status: 200,
      response: [],
    })

    createVue()
    vm.$router.push(`/`)
    moxios.wait(() => {
      expect(vm.$el.textContent)
        .to.contain(`No Previous Tasks`)
      done()
    })
  })

  it(`hides "no tasks" message if there is tasks..`, (done) => {
    moxios.stubRequest(tasksApiUrl, {
      status: 200,
      response: TestData.Tasks.TasksList,
    })

    createVue()
    vm.$router.push(`/`)
    moxios.wait(() => {
      expect(vm.$el.textContent)
        .not.to.contain(`No Previous Tasks`)
      done()
    })
  })

  it(`shows tasks meta info`, (done) => {
    moxios.stubRequest(tasksApiUrl, {
      status: 200,
      response: TestData.Tasks.TasksList,
    })

    createVue()
    vm.$router.push(`/`)
    moxios.wait(() => {
      const expectedTexts = [`10 Following`, `Fetch Following`, `10 Tweet`, `Backup Likes`, ]

      expectedTexts.map(x => {
        expect(vm.$el.textContent).to.contain(x)
      })

      done()
    })
  })
})

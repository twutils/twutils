import MainComponent from '@/components/MainComponent'

const tasksApiUrl = window.TwUtils.baseUrl + `api/tasks`

describe(`Tasks Rendering and Content`, () => {
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

  it(`shows fetch likes task`, (done) => {
    expect(TestData.Tasks.FetchLikes.TaskViewResponse.data.length)
      .to.be.above(5)

    moxios.stubRequest(tasksApiUrl, {
      status: 200,
      response: [TestData.Tasks.FetchLikes.TaskResponse, ],
    })

    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchLikes.TaskResponse.id, {
      status: 200,
      response: TestData.Tasks.FetchLikes.TaskResponse,
    })

    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchLikes.TaskResponse.id + `/view?page=1&perPage=200&searchKeywords=&searchOnlyInMonth=0`, {
      status: 200,
      response: TestData.Tasks.FetchLikes.TaskViewResponse,
    })

    createVue()
    vm.$router.push(`/task/${TestData.Tasks.FetchLikes.TaskResponse.id}`)
    moxios.wait(() => {
      const vmTextContent = vm.text()
      TestData.Tasks.FetchLikes.TaskViewResponse.data.forEach((tweet) => {
        expect(vmTextContent).to.contain(tweet.tweep.screen_name)
        expect(vmTextContent).to.contain(tweet.text)
      })
      done()
    })
  })

  it(`shows fetch user tweets task`, (done) => {
    expect(TestData.Tasks.FetchUserTweets.TaskDataResponse.data.length)
      .to.be.above(5)
    TestData.Tasks.FetchUserTweets.TaskDataResponse.data = TestData.Tasks.FetchUserTweets.TaskDataResponse.data.map((tweet) => {
      return { ...tweet, text: tweet.text + (Math.floor(Math.random() * 100)), }
    })

    moxios.stubRequest(tasksApiUrl, {
      status: 200,
      response: [TestData.Tasks.FetchUserTweets.TaskResponse, ],
    })
    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchUserTweets.TaskResponse.id, {
      status: 200,
      response: TestData.Tasks.FetchUserTweets.TaskResponse,
    })
    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchUserTweets.TaskResponse.id + `/view?page=1&perPage=200&searchKeywords=&searchOnlyInMonth=0`, {
      status: 200,
      response: TestData.Tasks.FetchUserTweets.TaskViewResponse,
    })

    createVue()
    vm.$router.push(`/task/${TestData.Tasks.FetchUserTweets.TaskResponse.id}`)
    moxios.wait(() => {
      const vmTextContent = vm.text()
      TestData.Tasks.FetchUserTweets.TaskViewResponse.data.forEach((tweet) => {
        expect(vmTextContent).to.contain(tweet.tweep.screen_name)
        expect(vmTextContent).to.contain(tweet.text)
      })
      done()
    })
  })

  it(`shows fetch following task`, (done) => {
    expect(TestData.Tasks.FetchFollowing.TaskViewResponse.data.length)
      .to.be.above(5)
    TestData.Tasks.FetchFollowing.TaskViewResponse.data = TestData.Tasks.FetchFollowing.TaskViewResponse.data.map((following) => {
      return { ...following, tweep: { ...following.tweep, screen_name: following.tweep.screen_name + (Math.floor(Math.random() * 100)), }, }
    })

    moxios.stubRequest(tasksApiUrl, {
      status: 200,
      response: [TestData.Tasks.FetchFollowing.TaskResponse, ],
    })
    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchFollowing.TaskResponse.id, {
      status: 200,
      response: TestData.Tasks.FetchFollowing.TaskResponse,
    })
    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchFollowing.TaskResponse.id + `/view?page=1&perPage=100&search=`, {
      status: 200,
      response: TestData.Tasks.FetchFollowing.TaskViewResponse,
    })

    createVue()
    vm.$router.push(`/task/${TestData.Tasks.FetchFollowing.TaskResponse.id}`)
    setTimeout(() => {
      const vmTextContent = vm.text()
      TestData.Tasks.FetchFollowing.TaskViewResponse.data.forEach((following) => {
        expect(vmTextContent).to.contain(following.tweep.screen_name)
        expect(vmTextContent).to.contain(following.tweep.description)
      })
      done()
    }, 100)
  })

  it(`shows fetch followers task`, (done) => {
    expect(TestData.Tasks.FetchFollowers.TaskViewResponse.data.length)
      .to.be.above(5)
    TestData.Tasks.FetchFollowers.TaskViewResponse.data = TestData.Tasks.FetchFollowers.TaskViewResponse.data.map((following) => {
      return { ...following, tweep: { ...following.tweep, screen_name: following.tweep.screen_name + (Math.floor(Math.random() * 100)), }, }
    })

    moxios.stubRequest(tasksApiUrl, {
      status: 200,
      response: [TestData.Tasks.FetchFollowers.TaskResponse, ],
    })
    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchFollowers.TaskResponse.id, {
      status: 200,
      response: TestData.Tasks.FetchFollowers.TaskResponse,
    })

    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchFollowers.TaskResponse.id + `/view?page=1&perPage=100&search=`, {
      status: 200,
      response: TestData.Tasks.FetchFollowers.TaskViewResponse,
    })

    createVue()
    vm.$router.push(`/task/${TestData.Tasks.FetchFollowers.TaskResponse.id}`)

    setTimeout(() => {
      const vmTextContent = vm.text()
      TestData.Tasks.FetchFollowers.TaskViewResponse.data.forEach((following) => {
        expect(vmTextContent).to.contain(following.tweep.screen_name)
        expect(vmTextContent).to.contain(following.tweep.description)
      })
      done()
    }, 100)
  })
})

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
    expect(TestData.Tasks.FetchLikes.TaskDataResponse.data.length)
      .to.be.above(5)

    moxios.stubRequest(tasksApiUrl, {
      status: 200,
      response: [TestData.Tasks.FetchLikes.TaskResponse, ],
    })

    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchLikes.TaskResponse.id, {
      status: 200,
      response: TestData.Tasks.FetchLikes.TaskResponse,
    })

    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchLikes.TaskResponse.id + `/data?page=1`, {
      status: 200,
      response: TestData.Tasks.FetchLikes.TaskDataResponse,
    })

    createVue()
    vm.$router.push(`/task/${TestData.Tasks.FetchLikes.TaskResponse.id}`)
    moxios.wait(() => {
      const vmTextContent = vm.text()
      TestData.Tasks.FetchLikes.TaskDataResponse.data.forEach((tweet) => {
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
    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchUserTweets.TaskResponse.id + `/data?page=1`, {
      status: 200,
      response: TestData.Tasks.FetchUserTweets.TaskDataResponse,
    })

    createVue()
    vm.$router.push(`/task/${TestData.Tasks.FetchUserTweets.TaskResponse.id}`)
    moxios.wait(() => {
      const vmTextContent = vm.text()
      TestData.Tasks.FetchUserTweets.TaskDataResponse.data.forEach((tweet) => {
        expect(vmTextContent).to.contain(tweet.tweep.screen_name)
        expect(vmTextContent).to.contain(tweet.text)
      })
      done()
    })
  })

  it(`shows fetch following task`, (done) => {
    expect(TestData.Tasks.FetchFollowing.TaskDataResponse.data.length)
      .to.be.above(5)
    TestData.Tasks.FetchFollowing.TaskDataResponse.data = TestData.Tasks.FetchFollowing.TaskDataResponse.data.map((following) => {
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
    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchFollowing.TaskResponse.id + `/data?page=1`, {
      status: 200,
      response: TestData.Tasks.FetchFollowing.TaskDataResponse,
    })

    createVue()
    vm.$router.push(`/task/${TestData.Tasks.FetchFollowing.TaskResponse.id}`)
    setTimeout(() => {
      const vmTextContent = vm.text()
      TestData.Tasks.FetchFollowing.TaskDataResponse.data.forEach((following) => {
        expect(vmTextContent).to.contain(following.tweep.screen_name)
        expect(vmTextContent).to.contain(following.tweep.description)
      })
      done()
    }, 100)
  })

  it(`shows fetch followers task`, (done) => {
    expect(TestData.Tasks.FetchFollowers.TaskDataResponse.data.length)
      .to.be.above(5)
    TestData.Tasks.FetchFollowers.TaskDataResponse.data = TestData.Tasks.FetchFollowers.TaskDataResponse.data.map((following) => {
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

    moxios.stubRequest(window.TwUtils.baseUrl + `api/tasks/` + TestData.Tasks.FetchFollowers.TaskResponse.id + `/data?page=1`, {
      status: 200,
      response: TestData.Tasks.FetchFollowers.TaskDataResponse,
    })

    createVue()
    vm.$router.push(`/task/${TestData.Tasks.FetchFollowers.TaskResponse.id}`)

    setTimeout(() => {
      const vmTextContent = vm.text()
      TestData.Tasks.FetchFollowers.TaskDataResponse.data.forEach((following) => {
        expect(vmTextContent).to.contain(following.tweep.screen_name)
        expect(vmTextContent).to.contain(following.tweep.description)
      })
      done()
    }, 100)
  })
})

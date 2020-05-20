import MainComponent from '@/components/task-add/AddDestroyTweetsListOptions'

const testComponent = () => {
  createVue({
    backButtonAction: () => {},
    chosenTaskToRemoveFrom: TestData.Tasks.TasksList.find(x => x.baseName === `fetchusertweets`),
    value: {},
  })
}

describe(`addDestroyTweetsListOptions component`, () => {
  beforeEach(() => {
    window.specComponent = MainComponent
  })

  afterEach(() => {
    vm.$destroy()
  })

  it(`start year < end year: show full months in end date`, (done) => {
    testComponent()

    vm.start_date = { year: `2010`, month: `05`, day: `02`, }

    vm.$nextTick(() => {
      expect(vm.endDateMonthsOptions.includes(`04`))
        .to.equal(true)
      expect(vm.endDateMonthsOptions.length)
        .to.equal(12)
      done()
    })
  })

  it(`start year < end year & end month selected first: show full months in start date`, (done) => {
    testComponent()

    vm.end_date = { year: `2010`, month: `05`, day: `02`, }
    vm.start_date = { year: `2009`, month: ``, day: ``, }

    vm.$nextTick(() => {
      expect(vm.startDateMonthsOptions.includes(`04`))
        .to.equal(true)
      expect(vm.startDateMonthsOptions.length)
        .to.equal(12)
      done()
    })
  })
})

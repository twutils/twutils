<style scoped>
</style>
<template>
<div class="input-group">
  <div class="input-group-prepend">
    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
  </div>
  <select v-model="date.year" class="">
    <option value="" v-if="locale==='en'">Year</option>
    <option value="" v-if="locale==='ar'">سنة</option>
    <option v-for="year in yearOptions" v-text="year" :value="year"></option>
  </select>
  <select v-model="date.month" :disabled="date.year === ''" class="">
    <option value="" v-if="locale==='en'">Month</option>
    <option value="" v-if="locale==='ar'">شهر</option>
    <option v-for="month in monthsOptions" v-text="month" :value="month"></option>
  </select>
  <select v-model="date.day" :disabled="date.month === ''" class="">
    <option value="" v-if="locale==='en'">Day</option>
    <option value="" v-if="locale==='ar'">يوم</option>
    <option v-for="day in daysOptions" v-text="day" :value="day"></option>
  </select>
</div>
</template>
<script>
const years = []
const months = []
const days = []

for (var i = 2006; i <= (new Date()).getFullYear(); i++) {
  years.push(`${i}`)
}

for (var i = 1; i <= 12; i++) {
  months.push(`${i}`.padStart(2, `0`))
}

for (var i = 1; i <= 31; i++) {
  days.push(`${i}`.padStart(2, `0`))
}

const dateOptions = {
  year: ``,
  month: ``,
  day: ``,
}

export default {
  data() {
    return {
      ...dateOptions,
    }
  },
  props: {
    date: {
      type: Object,
    },
    startDate: {
      type: Object,
    },
    endDate: {
      type: Object,
    },
  },
  computed: {
    yearOptions() {
      if (this.startDate)
      {
        return this.endDateYearOptions
      }

      return this.startDateYearOptions
    },
    monthsOptions() {
      if (this.startDate)
      {
        return this.endDateMonthsOptions
      }

      return this.startDateMonthsOptions
    },
    daysOptions() {
      if (this.startDate)
      {
        return this.endDateDaysOptions
      }

      return this.startDateDaysOptions
    },
    startDateYearOptions () {
      if (this.endDate.year === ``) {
        this.endDate.month = ``
        this.endDate.day = ``
        return years
      }
      return years.filter(year => year <= this.endDate.year)
    },
    startDateMonthsOptions () {
      if (this.endDate.month === ``) {
        this.endDate.day = ``
        return months
      }
      return months.filter(month => parseInt(month) <= parseInt(this.endDate.month) || !(parseInt(this.date.year) >= parseInt(this.endDate.year)))
    },
    startDateDaysOptions () {
      if (this.endDate.day === ``) { return days }

      if (this.date.year === this.endDate.year && this.date.month === this.endDate.month) { return days.filter(day => parseInt(day) <= parseInt(this.endDate.day)) }

      return days
    },
    endDateYearOptions () {
      if (this.startDate.year === ``) {
        this.date.month = ``
        this.date.day = ``
        return years
      }
      return years.filter(year => year >= this.startDate.year)
    },
    endDateMonthsOptions () {
      if (this.startDate.month === ``) {
        this.startDate.day = ``
        return months
      }
      return months.filter(month => parseInt(month) >= parseInt(this.startDate.month) || !(parseInt(this.startDate.year) >= parseInt(this.date.year)))
    },
    endDateDaysOptions () {
      if (this.startDate.day === ``) { return days }

      if (this.startDate.year === this.date.year && this.startDate.month === this.date.month) { return days.filter(day => parseInt(day) >= parseInt(this.startDate.day)) }

      return days
    },
  },
}
</script>
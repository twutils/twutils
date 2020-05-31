<style scoped>
</style>
<template>
<div style="border: 1px solid #ccc; padding: 1rem; border-radius: 1rem; margin: 0.8rem 0rem;">
  <ul>
    <li>
      {{__('start_date')}}: <code>{{ getTaskSettingsOption('start_date') }}</code>
    </li>
    <li>
      {{__('end_date')}}: <code>{{ getTaskSettingsOption('end_date') }}</code>
    </li>
  </ul>
</div>
</template>
<script>
import get from 'lodash/get'

export default {
  components: {
  },
  props: {
    task: {
      type: Object,
    },
  },
  data () {
    return {

    }
  },
  mounted () {

  },
  methods: {
    getTaskSettingsOption (option) {
      const optionValue = get(this.task.extra, `settings.` + option)

      if (optionValue === null && option === `start_date`) { return `From the beginning` }

      if (optionValue === null && option === `end_date`) { return `Until the end` }

      return optionValue
    },
  },
}
</script>

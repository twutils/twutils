<style>
</style>
<template>
<div class="container taskExportsDetails">
  <div class="row">
    <div class="col-12">
      <table class="table">
        <colgroup>
          <col width="25%">
          <col width="25%">
          <col width="20%">
          <col width="10%">
          <col width="10%">
          <col width="10%">
        </colgroup>
        <thead class="thead-dark">
          <tr>
            <th>
              Type
            </th>
            <th>
              Status
            </th>
            <th>
              Filename
            </th>
            <th>
              Size
            </th>
            <th>
              
            </th>
            <th>
              
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="systemExport in systemExports"
            :key="systemExport.name"
            :class="[
              'taskExportsDetails__row--' + systemExport.name,
              selectedExportType === systemExport.name ? 'highlightBoxShadowThenLeave' : '',
              ]"
          >
            <td
              :class="`${! systemExport.userExport && ! canAdd(systemExport.name) ? 'taskExportsDetails__notApplicable' : ''}`"
              rowspan="1"
            >
              <span class="taskExportsDetails__type">
                {{__(`exports.${systemExport.name}`)}}
              </span>
              <span class="taskExportsDetails__typeDesc">
                {{__(`exports_desc.${systemExport.name}`)}}
              </span>
            </td>
            <template
              v-if="systemExport.userExport"
            >
              <td>
                <div
                  v-if="systemExport.userExport.created_at"
                  class="taskExportsDetails__statusWrapper"
                >
                <span
                  :class="`taskExportsDetails__status ${systemExport.userExport.status === 'initial' ? 'border-secondary border' : ''}`"
                >
                  Initial
                </span>
                  <div class="taskExportsDetails__date taskExportsDetails__date--started">
                    {{ momentCalendar(systemExport.userExport.created_at) }}
                  </div>
                </div>
                <div
                  v-if="systemExport.userExport.started_at"
                  class="taskExportsDetails__statusWrapper"
                >
                <span
                  :class="`taskExportsDetails__status ${systemExport.userExport.status === 'started' ? 'border-primary border' : ''}`"
                >
                  Started
                </span>
                  <div class="taskExportsDetails__date taskExportsDetails__date--started">
                    {{ momentCalendar(systemExport.userExport.started_at) }}
                  </div>
                </div>
                <div
                  v-if="systemExport.userExport.broken_at"
                  class="taskExportsDetails__statusWrapper"
                >
                <span
                  :class="`taskExportsDetails__status ${systemExport.userExport.status === 'broken' ? 'border-danger border' : ''}`"
                >
                  Broken
                </span>
                  <div class="taskExportsDetails__date taskExportsDetails__date--_broken">
                    {{ momentCalendar(systemExport.userExport.broken_at) }}
                  </div>
                </div>
                <div
                  v-if="systemExport.userExport.success_at"
                  class="taskExportsDetails__statusWrapper"
                >
                <span
                  :class="`taskExportsDetails__status ${systemExport.userExport.status === 'success' ? 'border-success border' : ''}`"
                >
                  Success
                </span>
                  <div class="taskExportsDetails__date taskExportsDetails__date--success">
                    {{ momentCalendar(systemExport.userExport.success_at) }}
                  </div>
                </div>
              </td>
              <td>
                <span
                  class="taskExportsDetails__filename"
                >
                  {{systemExport.userExport.filename}}
                </span>
              </td>
              <td>
                <span
                  class="taskExportsDetails__size"
                >
                  {{ filesize(systemExport.userExport.size, {round: 0}) }}
                </span>
              </td>
            </template>
            <template
              v-if="! systemExport.userExport"
            >
              <td
                :class="`taskExportsDetails__notExist ${canAdd(systemExport.name) ? '' : 'taskExportsDetails__notApplicable'}`"
                colspan="3"
              >
                Doesn't exist
              </td>
            </template>
            <td>
              <button
                @click="download(systemExport)"
                v-if="systemExport.userExport"
                :disabled="! (systemExport.userExport && canDownload(systemExport.userExport))"
                type="button"
                :class="`btn btn-outline-${systemExport.userExport && canDownload(systemExport.userExport) ? 'primary':'disabled disabled'}`"
              >
                <i class="fa fa-download" aria-hidden="true"></i>
              </button>
            </td>
            <td>
              <button
                @click="remove(systemExport)"
                v-if="systemExport.userExport"
                :disabled="! (systemExport.userExport && canRemove(systemExport.userExport))"
                type="button"
                :class="`btn btn-outline-${systemExport.userExport && canRemove(systemExport.userExport) ? 'danger':'disabled disabled'}`"
              >
                <span data-glyph="trash" class="oi"></span>
              </button>              
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="col-12">
      Highlighted Export
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      Logs:
    </div>
  </div>
</div>
</template>
<script>
import EventBus from '@/EventBus'
import filesize from 'filesize'

export default {
  components: {
  },
  props: {
    task: {
      type: Object,
    },
    exports: {
      type: Array,
    },
  },
  data () {
    return {
      selectedExportType: '',
    }
  },
  mounted () {
    EventBus.listen('open-taskExports-modal', type => {
      this.selectedExportType = type
    })
  },
  methods: {
    filesize,
    canAdd(exportType)
    {
      if (exportType === 'htmlEntitites')
      {
        return ! ['fetchfollowing', 'fetchfollowers',].includes(this.task.baseName)
      }

      return false
    },
    canDownload(userExport)
    {
      return userExport.status === 'success'
    },
    canRemove(userExport)
    {
      return true
    },
    download(systemExport) {
      console.log('download')
    },
    remove(systemExport) {
      console.log('remove')
    },
  },
  computed: {
    systemExports() {
      return Object.keys(window.TwUtils.exports)
        .map(x => {
          return {
            name: x,
            userExport: this.exports.find(y => y.type === x)
          }
        })
    },
  },
}
</script>

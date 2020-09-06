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
          <template
            v-for="systemExport in systemExports"
          >
          <tr
            v-for="(userExport, index) in (systemExport.userExports.length == 0 ? [false] : systemExport.userExports)"
            :key="systemExport.name + index"
            :class="[
              'taskExportsDetails__row--' + systemExport.name,
              selectedExportType === systemExport.name ? 'highlightBoxShadowThenLeave' : '',
              ]"
            >
              <td
                :class="`${! userExport && ! canAdd(systemExport.name) ? 'taskExportsDetails__notApplicable' : ''}`"
                :rowspan="(systemExport.userExports.length == 0 ? [false] : systemExport.userExports).length"
                v-if="index === 0"
              >
                <span
                  :class="`oi taskExportsDetails__typeIcon`"
                  :data-glyph="getExportTypeIcon(systemExport.name)"
                ></span>
                <button
                  @click="add(systemExport)"
                  :disabled="! canAdd(systemExport.name)"
                  type="button"
                  :class="`taskExportsDetails__button btn btn-outline-${canAdd(systemExport.name) ? 'primary':'disabled disabled'}`"
                >
                  <span class="oi" data-glyph="plus"></span>
                </button>
                <span class="taskExportsDetails__type">
                  {{__(`exports.${systemExport.name}`)}}
                </span>
                <span class="taskExportsDetails__typeDesc">
                  {{__(`exports_desc.${systemExport.name}`)}}
                </span>
              </td>
              <template
                v-if="userExport"
              >
                <td>
                  <div
                    v-if="userExport.created_at"
                    class="taskExportsDetails__statusWrapper"
                  >
                  <span
                    :class="`taskExportsDetails__status ${userExport.status === 'initial' ? 'border-secondary border' : ''}`"
                  >
                    {{__('initial')}}
                  </span>
                    <div class="taskExportsDetails__date taskExportsDetails__date--started">
                      {{ momentCalendar(userExport.created_at) }}
                    </div>
                  </div>
                  <div
                    v-if="userExport.started_at"
                    class="taskExportsDetails__statusWrapper"
                  >
                  <span
                    :class="`taskExportsDetails__status ${userExport.status === 'started' ? 'border-primary border' : ''}`"
                  >
                    {{__('started')}}
                  </span>
                    <div class="taskExportsDetails__date taskExportsDetails__date--started">
                      {{ momentCalendar(userExport.started_at) }}
                    </div>
                  </div>
                  <div
                    v-if="userExport.broken_at"
                    class="taskExportsDetails__statusWrapper"
                  >
                  <span
                    :class="`taskExportsDetails__status ${userExport.status === 'broken' ? 'border-danger border' : ''}`"
                  >
                    {{__('broken')}}
                  </span>
                    <div class="taskExportsDetails__date taskExportsDetails__date--_broken">
                      {{ momentCalendar(userExport.broken_at) }}
                    </div>
                  </div>
                  <div
                    v-if="userExport.success_at"
                    class="taskExportsDetails__statusWrapper"
                  >
                  <span
                    :class="`taskExportsDetails__status ${userExport.status === 'success' ? 'border-success border' : ''}`"
                  >
                    {{__('success')}}
                  </span>
                    <div class="taskExportsDetails__date taskExportsDetails__date--success">
                      {{ momentCalendar(userExport.success_at) }}
                    </div>
                  </div>
                </td>
                <td>
                  <div class="text-muted text-left dir-ltr">
                    #{{userExport.id}}
                  </div>
                  <code
                    class="taskExportsDetails__filename"
                  >
                    {{userExport.filename}}
                  </code>
                </td>
                <td>
                  <span
                    class="taskExportsDetails__size"
                  >
                    {{ filesize(userExport.size, {round: 0}) }}
                  </span>
                </td>
              </template>
              <template
                v-if="! userExport"
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
                  v-if="userExport"
                  :disabled="! (userExport && canDownload(userExport))"
                  type="button"
                  :class="`btn btn-outline-${userExport && canDownload(userExport) ? 'primary':'disabled disabled'}`"
                >
                  <i class="fa fa-download" aria-hidden="true"></i>
                </button>
              </td>
              <td>
                <button
                  @click="remove(systemExport)"
                  v-if="userExport"
                  :disabled="! (userExport && canRemove(userExport))"
                  type="button"
                  :class="`btn btn-outline-${userExport && canRemove(userExport) ? 'danger':'disabled disabled'}`"
                >
                  <span data-glyph="trash" class="oi"></span>
                </button>              
              </td>
          </tr>
          </template>
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
      if (exportType === window.TwUtils.exports.htmlEntities)
      {
        return ! ['fetchfollowing', 'fetchfollowers',].includes(this.task.baseName)
      }

      return true
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
            userExports: this.exports.filter(y => y.type === x)
          }
        })
    },
  },
}
</script>

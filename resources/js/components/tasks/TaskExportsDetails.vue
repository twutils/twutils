<style>
</style>
<template>
<div class="container taskExportsDetails">
  <div class="row">
    <div class="col-12">
      <div class="table-responsive">
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
                  style="vertical-align: top;"
                >
                  <div class="taskExport__typeContainer">
                    <div class="taskExport__iconWrapper">
                      <span
                        v-for="(icon,index) in getExportTypeIcon(systemExport.name)"
                        :class="`oi taskExport__icon taskExportsDetails__typeIcon index-${index}`"
                        :key="systemExport.name + 'icon' + index"
                        :data-glyph="icon"
                      ></span>
                      <span class="taskExportsDetails__type">
                        {{__(`exports.${systemExport.name}`)}}
                      </span>
                    </div>
                    <span class="taskExportsDetails__typeDesc">
                      {{__(`exports_desc.${systemExport.name}`)}}
                    </span>
                    <button
                      @click="add(systemExport)"
                      :disabled="! canAdd(systemExport.name)"
                      type="button"
                      :class="`taskExportsDetails__button btn btn-outline-${canAdd(systemExport.name) ? 'primary':'disabled disabled'}`"
                    >
                      <template
                        v-if="systemExport.userExports.find(x => x.status === 'initial')"
                      >
                        <span class="oi" data-glyph="media-play"></span>
                        {{__('start')}}
                      </template>
                      <template
                        v-else
                      >
                        <span class="oi" data-glyph="plus"></span>
                        {{__('create')}}
                      </template>
                    </button>
                  </div>
                </td>
                <template
                  v-if="userExport"
                >
                  <td>
                    <div class="taskExportsDetails__statusesWrapper">
                      <div
                        v-if="userExport.success_at"
                        class="taskExportsDetails__statusWrapper"
                      >
                      <span
                        :class="`taskExportsDetails__status ${userExport.status === 'success' ? 'current bg-success' : ''}`"
                      >
                        {{__('success')}}
                      </span>
                        <div class="taskExportsDetails__date taskExportsDetails__date--success">
                          {{ momentCalendar(userExport.success_at) }}
                        </div>
                      </div>
                      <div
                        v-if="userExport.broken_at"
                        class="taskExportsDetails__statusWrapper"
                      >
                      <span
                        :class="`taskExportsDetails__status ${userExport.status === 'broken' ? 'current bg-danger' : ''}`"
                      >
                        {{__('broken')}}
                      </span>
                        <div class="taskExportsDetails__date taskExportsDetails__date--_broken">
                          {{ momentCalendar(userExport.broken_at) }}
                        </div>
                      </div>
                      <div
                        v-if="userExport.started_at"
                        class="taskExportsDetails__statusWrapper"
                      >
                      <span
                        :class="`taskExportsDetails__status ${userExport.status === 'started' ? 'current bg-primary' : ''}`"
                      >
                        {{__('started')}}
                      </span>
                        <div class="taskExportsDetails__date taskExportsDetails__date--started">
                          {{ momentCalendar(userExport.started_at) }}
                        </div>
                        <div
                          v-if="userExport.status === 'started' && userExport.progress_end && userExport.type === TwUtils.exports.htmlEntities"
                          class="progress"
                        >
                          <div
                            class="progress-bar progress-bar-striped bg-info"
                            role="progressbar"
                            :style="`width: ${(userExport.progress === userExport.progress_end ? userExport.progress_end : userExport.progress) * 100 / userExport.progress_end}%`"
                            :aria-valuenow="userExport.progress === userExport.progress_end ? userExport.progress_end : userExport.progress"
                            aria-valuemin="0"
                            :aria-valuemax="userExport.progress_end"
                          ></div>
                        </div>
                      </div>
                      <div
                        v-if="userExport.created_at"
                        class="taskExportsDetails__statusWrapper"
                      >
                      <span
                        :class="`taskExportsDetails__status ${userExport.status === 'initial' ? 'current bg-secondary' : ''}`"
                      >
                        {{__('initial')}}
                      </span>
                        <div class="taskExportsDetails__date taskExportsDetails__date--started">
                          {{ momentCalendar(userExport.created_at) }}
                        </div>
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
                <td
                  v-if="!(userExport && confirmRemoveMode === userExport.id)"
                >
                  <button
                    @click="download(userExport)"
                    v-if="userExport"
                    :disabled="! (userExport && canDownload(userExport))"
                    type="button"
                    :class="`btn btn-outline-${userExport && canDownload(userExport) ? 'primary':'disabled disabled'}`"
                  >
                    <i class="fa fa-download" aria-hidden="true"></i>
                  </button>
                </td>
                <td
                  :colspan="userExport && confirmRemoveMode === userExport.id ? 2 : 1"
                >
                  <template
                    v-if="userExport && confirmRemoveMode === userExport.id"
                  >
                    <div>
                      {{__('confirmRemoveExport')}}
                    </div>
                    <button
                      @click="confirmRemoveMode = false"
                      type="button"
                      :class="`btn btn-outline-primary btn-sm animated fadeIn`"
                    >
                      {{__('cancel')}}
                    </button>
                    <button
                      @click="doRemove(userExport)"
                      type="button"
                      :class="`btn btn-outline-danger btn-sm animated fadeIn`"
                    >
                      {{__('remove')}}
                    </button>
                  </template>
                  <button
                    @click="remove(userExport)"
                    v-if="userExport && confirmRemoveMode !== userExport.id"
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
    </div>
  </div>
</div>
</template>
<script>
import EventBus from '@/EventBus'
import filesize from 'filesize'

export default {
  beforeDestroy () {
    EventBus.$off(`refresh-task`)
  },
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
      selectedExportType: ``,
      confirmRemoveMode: false,
    }
  },
  mounted () {
    EventBus.listen(`open-taskExports-modal`, type => {
      this.selectedExportType = type
    })
  },
  methods: {
    filesize,
    canAdd (exportType) {
      if (exportType === window.TwUtils.exports.htmlEntities) {
        return ![`fetchfollowing`, `fetchfollowers`,].includes(this.task.baseName)
      }

      return true
    },
    canDownload (userExport) {
      return userExport.status === `success`
    },
    canRemove (userExport) {
      return true
    },
    add (systemExport) {
      axios.post(
        `${window.TwUtils.apiBaseUrl}exports/${this.task.id}/${systemExport.name}`,
        {
          type: systemExport.name,
        }
      )
        .then(response => {
          EventBus.fire(`refresh-task`)
        })
    },
    download (userExport) {
      window.location.href = `${window.TwUtils.baseUrl}task/${userExport.task_id}/export/${userExport.id}`
    },
    remove (userExport) {
      this.confirmRemoveMode = userExport.id
    },
    doRemove (userExport) {
      axios.delete(`${window.TwUtils.apiBaseUrl}exports/${userExport.id}`)
        .then(response => {
          EventBus.fire(`refresh-task`)
        })
    },
  },
  computed: {
    systemExports () {
      return Object.keys(window.TwUtils.exports)
        .map(x => {
          return {
            name: x,
            userExports: this.exports.filter(y => y.type === x),
          }
        })
    },
  },
}
</script>

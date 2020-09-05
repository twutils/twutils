<style scoped lang="scss">
.taskExportsDetails
{

}
.taskExportsDetails__type
{
  font-weight: 600;
  display: block;
  text-align: center;
  font-size: 1rem;
}

.taskExportsDetails__typeDesc
{
  font-weight: 100;
  font-size: 80%;
  display: block;
}
.taskExportsDetails__date
{padding-left: 1rem;margin-bottom: 0.5rem;}
.taskExportsDetails__date--started
{

}
.taskExportsDetails__date--_broken
{

}
.taskExportsDetails__date--success
{

}
.taskExportsDetails__filename
{
  border: 1px solid #eee;
  display: block;
  width: 100%;
  font-size: 13px;
  font-weight: 100;
  padding: 8px;
  word-break: break-all;
  font-family: "Roboto Mono", monospace;
}

.taskExportsDetails__size
{

}

.taskExportsDetails__row--excel
{
    background: linear-gradient(-45deg, #f3fff3, transparent);
}
.taskExportsDetails__row--html
{
    background: hsl(210 100% 98% / 1);
}
.taskExportsDetails__row--htmlEntities
{
    background: linear-gradient(45deg, hsl(12deg 100% 98%), hsl(208deg 100% 98%));
}

.taskExportsDetails td
{
    padding: 0.5rem;
    padding-bottom: 2px;
    font-size: 0.8rem;
    vertical-align: middle;
    min-width: 70px;
    max-width: 200px;
    height: 100px;
}

.taskExportsDetails__status
{
    display: block;
    font-weight: 500;
    font-size: 1rem;
    box-shadow: 0 0 5px #dad7d7;
    text-align: center;
    margin-bottom: 1rem;
}

.taskExportsDetails__notApplicable {
  filter: blur(2px);
}
</style>
<template>
<div class="container taskExportsDetails">
  <div class="row">
    <div class="col-12">
      <table class="table">
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
              Action
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="systemExport in systemExports"
            :key="systemExport.name"
            :class="`taskExportsDetails__row--${systemExport.name}`"
          >
            <td
              :class="`${! systemExport.userExport && ! canAdd(systemExport.name) ? 'taskExportsDetails__notApplicable' : ''}`"
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
                <span :class="`taskExportsDetails__status ${systemExport.userExport.status === 'success' ? 'border-success border' : ''}`">
                  {{__(systemExport.userExport.status)}}
                </span>
                <div
                  v-if="systemExport.userExport.started_at"
                >
                  <span class="text-muted">
                    Started at:
                  </span>
                  <div class="taskExportsDetails__date taskExportsDetails__date--started">
                    {{ momentCalendar(systemExport.userExport.started_at) }}
                  </div>
                </div>
                <div
                  v-if="systemExport.userExport.broken_at"
                >
                  <span class="text-muted">
                    Broken at:
                  </span>
                  <div class="taskExportsDetails__date taskExportsDetails__date--_broken">
                    {{ momentCalendar(systemExport.userExport.broken_at) }}
                  </div>
                </div>
                <div
                  v-if="systemExport.userExport.success_at"
                >
                  <span class="text-muted">
                    Success at:
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
              Action
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
    }
  },
  mounted () {
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
    }
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

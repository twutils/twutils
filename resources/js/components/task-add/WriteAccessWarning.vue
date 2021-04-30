<style lang="scss" scoped>
.writeAccessWarning {
  position: absolute;
  z-index: 1;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 80px;
  font-size: 1.3rem;

  .exploringMode__link {
    font-size: 1rem;
    border-bottom: 1px solid black;
    padding: 0.5rem;
  }
}
</style>
<template>
<div class="writeAccessWarning">
  <div v-if="locale==='en'" class="alert-warning alert text-dark">
    <p>
      Before you get to the delete options page, In order to remove your tweets, we need to have what Twitter calls "Write Access" to your account.
    </p>
    <p>
      <a :href="routes['twitter.rw.login']" class="btn btn-secondary">Click here</a> to give us this access.
    </p>
    <small class="removeAccessReminder">Remember that you can always revoke applications' access to your account from your <a href="https://twitter.com/settings/applications" target="_blank" rel="noopener">twitter settings</a>.</small>
  </div>
  <div v-if="locale==='ar'" class="alert-warning alert">
    <p>
      من أجل حذف تغريداتك، نحتاج ما يُطلِق عليه تويتر "صلاحية الكتابة" على حسابك.
    </p>
    <p>
      <a :href="routes['twitter.rw.login']" class="btn btn-secondary">اضغط هنا</a> لتُعطي تويتلز هذه الصلاحية.
    </p>
    <small class="removeAccessReminder">
      تذكَّر أن بإمكانَكَ دائماً أن تحذف صلاحيات التطبيقات التي تستطيع الوصول إلى حسابك عبر
      <a href="https://twitter.com/settings/applications" target="_blank" rel="noopener">صفحة الإعدادات الخاصة بك على تويتر</a>.
    </small>
  </div>
  <div :class="`text-${isRtl?'left':'right'}`">
    <a class="exploringMode__link" href="javascript:void(0);" @click="$emit('activateExploringMode')">
      {{__('exploringـmode')}}
    </a>
  </div>
</div>
</template>

<script>
export default {
  components: {
  },
  props: {
  },
  data () {
    return {

    }
  },
  mounted () {
  },
  methods: {
  },
}
</script>

<style scoped>

</style>
<template>
  <div>
    <div class="d-flex flex-column" v-if="videoVariants.length > 0 && !isChild && isLocal && get(tweet, videoSrcArrayPath)">
      <video :width="width" :height="height" :poster="localVideoPoster" :src="localVideoSrc" controls preload>
        <source :src="localVideoSrc">
      </video>
      <a :href="localVideoSrc" target="_blank" download>
        Download
      </a>
    </div>
    <div class="d-flex flex-column" v-if="videoVariants.length > 0 && !isLocal">
      <video :width="width" :height="height" :poster="media.media_url_https" :src="videoDownloadUrl" controls preload>
        <source v-for="source in videoVariants" :src="source.url" :type="source.content_type">
      </video>
      <a :href="videoDownloadUrl" target="_blank" download>
        Download
      </a>
    </div>
    <a
      v-if="media.type !== 'video'"
      data-toggle="lightbox"
      data-type="image"
      :data-title="`@${tweet.tweep.screen_name}`"
      :data-footer="`<a href='${imgSrc}' download target='_blank'>Download</a><span class='tweetImageCaption__text text-${isRtlText(tweet.text) ? 'right dir-rtl' : 'left'}'>${tweet.text}</span>`"
      :data-gallery="tweet.id_str"
      :href="imgSrc"
      target="_blank"
    >
      <img style="max-width: 150px; width: 150px;" class="tweetImage__thumb" :src="`${imgSrc}${isLocal ? '' : ':thumb'}`" :alt="tweet.text">
    </a>
  </div>
</template>

<script>
import get from 'lodash/get'
import maxBy from 'lodash/maxBy'

const videoSrcArrayPath = `pivot.attachments.paths[0][1]`

export default {
  props: {
    media: {
      default: () => { return {} },
      type: Object,
    },
    tweet: {
      default: () => { return {} },
      type: Object,
    },
    index: {
      default: 0,
      type: Number,
    },
    isChild: {
      default: false,
      type: Boolean,
    },
  },
  data () {
    return {
      get,
      videoSrcArrayPath,
      imageSrcArrayPath: ``,
      videoVariants: [],
      videoDownloadUrl: `#`,
    }
  },
  mounted () {
    this.imageSrcArrayPath = `pivot.attachments.paths[${this.index}][0]`

    if (this.media.type === `video` && this.media.video_info) {
      this.videoVariants = this.media.video_info.variants.reverse()

      const maxBitrate = maxBy(this.videoVariants, `bitrate`)

      if (maxBitrate != null) { this.videoDownloadUrl = maxBitrate.url } else { this.videoDownloadUrl = this.videoVariants[0].url }
    }
  },
  computed: {
    localVideoPoster () {
      if (!get(this.tweet, this.imageSrcArrayPath)) { return null }

      return `media/` + get(this.tweet, this.imageSrcArrayPath)
    },
    localVideoSrc () {
      if (!get(this.tweet, videoSrcArrayPath)) { return null }

      return `media/` + get(this.tweet, videoSrcArrayPath)
    },
    imgSrc () {
      if (this.media.type === `video`) { return null }

      if (!this.isChild && this.isLocal && this.media.type === `photo` && get(this.tweet, this.imageSrcArrayPath)) { return `media/` + get(this.tweet, this.imageSrcArrayPath) }

      return this.media.media_url_https
    },
    width () {
      if (this.media.type !== `video` || !this.media.video_info || !this.media.video_info.aspect_ratio) { return 150 }

      const [width, height, ] = this.media.video_info.aspect_ratio

      if (width > height) { return 300 }

      return (width * 300) / (height)
    },
    height () {
      if (this.media.type !== `video` || !this.media.video_info || !this.media.video_info.aspect_ratio) { return 150 }

      const [width, height, ] = this.media.video_info.aspect_ratio

      if (height > width) { return 300 }

      return (height * 300) / (width)
    },
  },
}
</script>

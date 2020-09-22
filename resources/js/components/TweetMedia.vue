<style scoped>

</style>
<template>
  <div>
    <a
      v-if="media.type === 'photo'"
      data-toggle="lightbox"
      data-type="image"
      :data-title="`@${tweet.tweep.screen_name}`"
      :data-footer="`<a href='${imgSrc}' download target='_blank'>${__('download')}</a><span class='tweetImageCaption__text text-${isRtlText(tweet.text) ? 'right dir-rtl' : 'left'}'>${tweet.text}</span>`"
      :data-gallery="tweet.id_str"
      :href="imgSrc"
      target="_blank"
    >
      <img style="max-width: 150px; width: 150px;" class="tweetImage__thumb" :src="`${imgSrc}${isLocal ? '' : ':thumb'}`" :alt="tweet.text">
    </a>
    <div
      v-if="[`video`, `animated_gif`].includes(media.type)"
      class="d-flex flex-column"
    >
      <video :width="width" :height="height" :poster="imgSrc" controls preload="metadata" :autoplay="media.type === `animated_gif`">
        <source v-for="source in videoVariants" :src="source.url" :type="source.content_type">
      </video>
      <a :href="videoDownloadUrl" target="_blank" download>
        Download
      </a>
    </div>
  </div>
</template>

<script>
import get from 'lodash/get'
import maxBy from 'lodash/maxBy'

const mime = require('mime/lite');


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
      videoVariants: [],
      videoDownloadUrl: '#',
      width: 150,
      height: 150,
    }
  },
  mounted () {
    if ([`video`, `animated_gif`].includes(this.media.type))
    {
      this.setVideoSrcAttributes()
      this.setVideoDimensionsAttributes()
    }
  },
  methods: {
    setVideoSrcAttributes() {
      if (this.isLocal)
      {
        let media = this.media.media_files[1]
        this.videoVariants = [
          {
            url: `media/` + media.mediaPath,
            content_type: mime.getType(media.extension),
          }
        ]

        this.videoDownloadUrl = this.videoVariants[0].url

        return ;
      }

      this.videoVariants = get(this.media.raw, 'video_info.variants') || []

      const maxBitrate = maxBy(this.videoVariants, `bitrate`)

      if (maxBitrate != null)
      {
        this.videoDownloadUrl = maxBitrate.url
      } else {
        this.videoDownloadUrl = this.videoVariants[0].url
      }
    },
    setVideoDimensionsAttributes() {
      const aspectRatio = get(this.media.raw, 'video_info.aspect_ratio')

      if (! aspectRatio)
        return ;

      const [width, height, ] = aspectRatio

      if (width > height) {
        this.width = 300
        this.height = (height * 300) / (width)
      } else {
        this.width = (width * 300) / (height)
        this.height = 300
      }
    }
  },
  computed: {
    imgSrc () {
      if (
        !this.isChild &&
        this.isLocal &&
        this.media.media_files[0].mediaPath
      )
      {
        return `media/` + this.media.media_files[0].mediaPath
      }

      return this.media.raw.media_url_https
    },
  },
}
</script>

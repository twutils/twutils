<style lang="scss">

.contactInfo {
    height: 100%;
    max-height: 260px;

  display: flex;
    align-items: center;
    justify-content: space-around;
    flex-direction: column;
}

.contactForm__formLabel {
  width: 100%;
}

.contactInfo, .contactForm__formGroup {
  box-shadow: 0px 0px 4px #cecece;
    padding: 1rem;
}

.purposeOption__container {
  border: 1px solid #ccc;
  padding: 0 6px;
  border-radius: 1rem;
  margin: 0.5rem 0;
  min-width: 170px;
}

.formLabel__required {
  right: 0px;

  @at-root .locale-ar & {
    right: initial;
    left: 0px;
  }
}

.contactForm__responseWrapper {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 2;
    display: flex;
    justify-content: center;
    align-items: center;
  background: rgba(155, 248, 158, 0.6);

  .contactForm__responseContainer {
      width: 100%;
      height: 300px;

      display: flex;
      align-items: center;
      justify-content: center;

      color: black;
      border-radius: 1rem;

      margin: 1rem;
      padding: 1rem;

      box-shadow: 0px 0px 5px #ccc;

    &--success {
        background: rgba(255, 255, 255, 1);
    }
  }
}
.contactForm__formInputs {
  position: relative;
}
</style>
<template>
<div class="row">
  <div class="col-md-4">
    <div class="h-100 d-flex flex-column">
      <div class="contactInfo">
        <span style="font-size: 4rem;" class="oi" data-glyph="envelope-open"></span>
        <h2>
          {{__('contact_us')}}
        </h2>
        <small>
          {{__('contact_us_desc')}}
        </small>
      </div>
      <div class="text-justify text-muted small">
        {{__('contact_us_additional_channels')}}

        <ul class="">
          <li class="">
            {{__('twitter')}}:
            <a href="https://twitter.com/TwUtils">
                @TwUtils
            </a>
          </li>
          <li class="">
            {{__('email')}}:
            <a href="mailto:support@twutils.com">
              support@twutils.com
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <form @submit.prevent="submit" class="contactForm">
      <div class="contactForm__formInputs">
        <div v-if="mode === 'success'" class="contactForm__responseWrapper animated bounceInDown">
          <div :class="`contactForm__responseContainer contactForm__responseContainer--success text-${isRtl ? 'right': 'left'}`">
            <div v-if="!isRtl">
              <h4>
                <strong>Thank You, {{form.name}} !</strong> .. We received your message.
              </h4>
              <p>
                We will check it and reply back to your email address:
                <i>{{ form.email }}</i>
              </p>
            </div>
            <div v-if="isRtl">
              <h4>
                <strong>شكراً، {{form.name}} !</strong> .. لقد تلقّينا رسالتك.
              </h4>
              <p>
                سنراجعها ونقوم بالرد على البريد الالكتروني الخاص بك:
                <i>{{ form.email }}</i>
              </p>
            </div>
          </div>
        </div>
        <div class="form-group contactForm__formGroup d-flex my-3">
          <label class="control-label col-sm-3 d-flex align-items-center justify-content-end font-size-08" for="name">
            {{__('name')}}
            <span class="text-danger position-absolute formLabel__required">*</span>
          </label>
          <div class="col-sm-9">
          <input @input="delete errors['name']" required v-model="form.name" type="text" class="form-control" id="name" placeholder="" name="name" autocomplete="name">
          <div
            v-if="('name' in errors) && errors['name'].length > 0"
            v-for="error in errors['name']"
            v-text="error"
            class="text-danger font-size-08"
          ></div>
          </div>
        </div>
        <div class="form-group contactForm__formGroup d-flex my-3">
          <label class="control-label col-sm-3 d-flex align-items-center justify-content-end font-size-08" for="email">
            {{__('email')}}
            <span class="text-danger position-absolute formLabel__required">*</span>
          </label>
          <div class="col-sm-9">
          <input @input="delete errors['email']" required v-model="form.email" type="email" class="form-control" id="email" placeholder="" name="email" autocomplete="email">
          <div
            v-if="('email' in errors) && errors['email'].length > 0"
            v-for="error in errors['email']"
            v-text="error"
            class="text-danger font-size-08"
          ></div>
          </div>
        </div>
        <div class="form-group contactForm__formGroup d-flex my-3">
          <label class="control-label col-sm-3 d-flex align-items-center justify-content-end font-size-08" for="purpose">
            {{__('purpose')}}
            <span class="text-danger position-absolute formLabel__required">*</span>
          </label>
          <div class="col-sm-9 font-size-08">
          <div class="form-check-inline purposeOption__container">
            <label class="form-check-label contactForm__formLabel">
              <input required v-model="form.purpose" value="feedback" type="radio" class="form-check-input" name="purpose">
              {{__('feedback')}}
            </label>
          </div>
          <div class="form-check-inline purposeOption__container">
            <label class="form-check-label contactForm__formLabel">
              <input v-model="form.purpose" value="suggestion" type="radio" class="form-check-input" name="purpose">
              {{__('suggestion')}}
            </label>
          </div>
          <div class="form-check-inline purposeOption__container">
            <label class="form-check-label contactForm__formLabel">
              <input v-model="form.purpose" value="ux-improvement" type="radio" class="form-check-input" name="purpose">
              {{__('ux_improvement')}}
            </label>
          </div>
          <div class="form-check-inline purposeOption__container">
            <label class="form-check-label contactForm__formLabel">
              <input v-model="form.purpose" value="bug" type="radio" class="form-check-input" name="purpose">
              {{__('report_a_bug')}}
            </label>
          </div>
          <div class="form-check-inline purposeOption__container">
            <label class="form-check-label contactForm__formLabel">
              <input v-model="form.purpose" value="support" type="radio" class="form-check-input" name="purpose">
              {{__('support')}}
            </label>
          </div>
          <div class="form-check-inline purposeOption__container">
            <label class="form-check-label contactForm__formLabel">
              <input v-model="form.purpose" value="other" type="radio" class="form-check-input" name="purpose">
              {{__('other')}}
            </label>
          </div>
          <div
            v-if="('purpose' in errors) && errors['purpose'].length > 0"
            v-for="error in errors['purpose']"
            v-text="error"
            class="text-danger font-size-08"
          ></div>
          </div>
        </div>
        <div class="form-group contactForm__formGroup d-flex my-3">
          <label class="control-label col-sm-3 d-flex align-items-center justify-content-end font-size-08" for="message">
            {{__('message')}}
            <span class="text-danger position-absolute formLabel__required">*</span>
          </label>
          <div class="col-sm-9">
          <textarea @input="delete errors['message']" required v-model="form.message" class="form-control" rows="5" id="message"></textarea>
          <div
            v-if="('message' in errors) && errors['message'].length > 0"
            v-for="error in errors['message']"
            v-text="error"
            class="text-danger font-size-08"
          ></div>
          <div
            v-if="('unknown' in errors) && errors['unknown'].length > 0"
            v-for="error in errors['unknown']"
            v-text="error"
            class="text-danger font-size-08"
          ></div>
          </div>
        </div>
      </div>
      <div v-if="mode === 'form'" class="form-group">
        <div :class="`col-sm-offset-2 col-sm-10 text-${isRtl ? 'left': 'right'}`">
        <button type="submit" class="btn btn-primary">
          {{__('send')}}
        </button>
        </div>
      </div>
    </form>
  </div>
</div>
</template>

<script>
import get from 'lodash/get'
const __ = require(`@/langManager`).default

const defaultForm = {
  name: get(window.TwUtils.user, `name`),
  email: get(window.TwUtils.user, `email`),
  purpose: `feedback`,
  message: ``,
}

export default {
  data () {
    return {
      mode: `form`,
      form: JSON.parse(JSON.stringify(defaultForm)),
      errors: {},
      isRtl: window.TwUtils.locale === `ar`,
    }
  },
  mounted () {
  },
  methods: {
    __,
    submit () {
      axios.post(`${window.TwUtils.apiBaseUrl}contact`, this.form)
        .then(resp => {
          this.errors = {}
          this.mode = `success`

          setTimeout(() => {
            this.form = JSON.parse(JSON.stringify(defaultForm))
            this.mode = `form`
          }, 6000)
        })
        .catch(err => {
          if (get(err, `response.status`) === 422 && get(err, `response.data.errors`)) {
            this.errors = get(err, `response.data.errors`)

            return
          }

          this.errors = {
            ...this.errors,
            unknown: [`Unknown Error..`, get(err, `response.data.message`), ],
          }
        })
    },
  },
}
</script>

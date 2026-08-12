<template>
  <section class="section is-fullheight is-error-section">
      <div class="container">
            <router-view />
      </div>
    <div class="hero-foot has-text-centered">
      <div class="logo" />
    </div>
  </section>
</template>

<script>
import { defineComponent } from 'vue'
import { setFullPage } from '@/store/layout'

export default defineComponent({
  name: 'FullPageView',
  created () {
    setFullPage(true)
  },
  beforeUnmount () {
    setFullPage(false)
  }
})
</script>

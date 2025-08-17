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

export default defineComponent({
  name: 'FullPageView',
  created () {
    this.$store.dispatch('toggleFullPage', true)
  },
    beforeUnmount () {
    this.$store.dispatch('toggleFullPage', false)
  }
})
</script>

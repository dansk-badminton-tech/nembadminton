<template>
  <section class="section hero is-fullheight is-error-section">
    <div class="hero-body">
      <div class="container">
        <div class="columns is-centered">
          <div class="column is-full">
            <router-view />
          </div>
        </div>
      </div>
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
  beforeDestroy () {
    this.$store.dispatch('toggleFullPage', false)
  }
})
</script>

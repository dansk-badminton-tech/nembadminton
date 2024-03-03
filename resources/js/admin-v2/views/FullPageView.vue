<template>
  <section class="section hero is-fullheight is-error-section">
    <div class="hero-body">
      <div class="container">
        <div class="columns is-centered">
          <div class="column is-two-fifths">
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

<style scoped>
.hero {
    background: linear-gradient(rgba(31, 44, 108, 0.65), rgba(31, 44, 108, 0.65)), rgba(0, 0, 0, 0.55) url("../../../assets/badminton-concept-with-shuttlecock-racket-1080.jpg") no-repeat;
    background-size: cover;
    background-position: center;
}
</style>

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

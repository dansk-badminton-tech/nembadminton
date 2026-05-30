<template>
  <section class="hero is-hero-bar">
    <div class="hero-body">
      <div class="level">
        <div class="level-left">
          <div class="level-item">
            <div>
              <h1 class="title">
                <slot />
              </h1>
              <div
                v-if="$slots.subtitle"
                class="subtitle is-6"
              >
                <slot name="subtitle" />
              </div>
            </div>
          </div>
        </div>
        <div
          v-show="hasRightVisible"
          class="level-right"
        >
          <div class="level-item">
            <slot name="right" />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'HeroBar',
  props: {
    hasRightVisible: {
      type: Boolean,
      default: true
    }
  }
})
</script>

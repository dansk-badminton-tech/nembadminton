<template>
  <div class="card">
    <header
      v-if="title"
      class="card-header"
    >
      <p class="card-header-title">
        <b-icon
          v-if="icon"
          :icon="icon"
          custom-size="default"
        />
        {{ title }}
      </p>
      <a
        v-if="headerIcon"
        href="#"
        class="card-header-icon"
        aria-label="more options"
        @click.prevent="headerIconClick"
      >
        <b-icon
          :icon="headerIcon"
          custom-size="default"
        />
      </a>
        <slot name="header" />
    </header>
    <div class="card-content">
      <slot />
    </div>
      <slot name="footer" />
  </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'CardComponent',
  props: {
    title: {
      type: String,
      default: null
    },
    icon: {
      type: String,
      default: null
    },
    headerIcon: {
      type: String,
      default: null
    }
  },
  emits: ['header-icon-click'],
  methods: {
    headerIconClick () {
      this.$emit('header-icon-click')
    }
  }
})
</script>

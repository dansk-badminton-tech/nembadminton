<template>
  <li :class="{ 'is-active': isDropdownActive }">
    <component
      :is="componentIs"
      :to="item.to"
      :href="item.href"
      :target="item.target"
      exact-active-class="is-active"
      :class="{ 'has-icon': !!item.icon, 'has-dropdown-icon': hasDropdown }"
      @click="menuClick"
    >
      <b-icon
        v-if="item.icon"
        :icon="item.icon"
        :class="{ 'has-update-mark': item.updateMark }"
        custom-size="default"
      />
      <span
        v-if="item.label"
        :class="{ 'menu-item-label': !!item.icon }"
      >
        {{ item.label }}
      </span>
      <div
        v-if="hasDropdown"
        class="dropdown-icon"
      >
        <b-icon
          :icon="dropdownIcon"
          custom-size="default"
        />
      </div>
    </component>
    <aside-menu-list
      v-if="hasDropdown"
      :menu="item.menu"
      is-submenu-list
    />
  </li>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'AsideMenuItem',
  components: {
      AsideMenuList: () => import('@/components/AsideMenuList.vue')
  },
  props: {
    item: {
      type: Object,
      required: true
    }
  },
  emits: ['menu-click'],
  data () {
    return {
      isDropdownActive: false
    }
  },
  computed: {
    componentIs () {
      return this.item.to ? 'router-link' : 'a'
    },
    hasDropdown () {
      return !!this.item.menu
    },
    dropdownIcon () {
      return this.isDropdownActive ? 'minus' : 'plus'
    }
  },
  methods: {
    menuClick () {
      this.$emit('menu-click', this.item)

      if (this.hasDropdown) {
        this.isDropdownActive = !this.isDropdownActive
      }
    }
  }
})
</script>

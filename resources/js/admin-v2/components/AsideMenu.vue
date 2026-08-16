<template>
  <aside
    v-show="isAsideVisible"
    class="aside is-placed-left"
  >
    <div class="aside-tools">
      <a
        class="navbar-item is-hidden-touch is-hidden-widescreen is-desktop-icon-only"
        @click="asideToggleDesktopOnly"
      >
        <b-icon pack="mdi" icon="backburger" />
      </a>
      <div class="aside-tools-label">
        <span><b>{{title}}</b></span>
      </div>
    </div>
    <div class="menu is-menu-main">
      <template v-for="(menuGroup, index) in menu">
        <p
          v-if="typeof menuGroup === 'string'"
          :key="`label-${index}`"
          class="menu-label"
        >
          {{ menuGroup }}
        </p>
        <aside-menu-list
          v-else
          :key="`menu-${index}`"
          :menu="menuGroup"
          @menu-click="menuClick"
        />
      </template>
    </div>
  </aside>
</template>

<style>
.menu-list a {
    background-color: #2e323a
}
</style>

<script>
import { defineComponent } from 'vue'
import { layout, toggleAsideDesktopOnly } from '@/store/layout'
import AsideMenuList from '@/components/AsideMenuList.vue'

export default defineComponent({
  name: 'AsideMenu',
  components: { AsideMenuList },
  props: {
      title: String,
    menu: {
      type: Array,
      default: () => []
    }
  },
  computed: {
    isAsideVisible () {
      return layout.isAsideVisible
    }
  },
  methods: {
    asideToggleDesktopOnly () {
      toggleAsideDesktopOnly()
    },
    menuClick (item) {
      //
    }
  }
})
</script>

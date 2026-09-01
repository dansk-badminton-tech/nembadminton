<script>
import { defineComponent, h, Comment } from 'vue'
import chunk from 'lodash/chunk'

export default defineComponent({
  name: 'TilesBlock',
  props: {
    maxPerRow: {
      type: Number,
      default: 5
    }
  },
  render () {
    const renderAncestor = elements => h(
      'div',
      { class: 'tile is-ancestor' },
      elements.map((element) => {
        return h('div', { class: 'tile is-parent' }, [element])
      })
    )

    const defaultSlot = this.$slots.default
      ? this.$slots.default().filter((element) => element.type !== Comment)
      : []

    if (defaultSlot.length <= this.maxPerRow) {
      return renderAncestor(defaultSlot)
    } else {
      return h(
        'div',
        { class: 'is-tiles-wrapper' },
        chunk(defaultSlot, this.maxPerRow).map((group) => {
          return renderAncestor(group)
        })
      )
    }
  }
})
</script>

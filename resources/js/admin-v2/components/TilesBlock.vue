<script>
import { defineComponent, h } from 'vue'
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
          if(element.context === undefined){
              return;
          }
        return h('div', { class: 'tile is-parent' }, [
          element
        ])
      })
    )

    if (this.$slots.default.length <= this.maxPerRow) {
      return renderAncestor(this.$slots.default)
    } else {
      return h(
        'div',
        { class: 'is-tiles-wrapper' },
        chunk(this.$slots.default, this.maxPerRow).map((group) => {
          return renderAncestor(group)
        })
      )
    }
  }
})
</script>

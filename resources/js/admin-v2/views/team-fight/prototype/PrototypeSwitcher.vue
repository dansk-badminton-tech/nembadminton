<template>
    <div class="prototype-floating-switcher">
        <button class="switcher-arrow" @click="prev" title="Forrige variant (venstre piltast)">
            <b-icon icon="chevron-left" size="is-small"></b-icon>
        </button>
        <div class="switcher-content">
            <span class="switcher-tag">PROTOTYPE</span>
            <span class="switcher-label">{{ currentLabel }}</span>
        </div>
        <button class="switcher-arrow" @click="next" title="Næste variant (højre piltast)">
            <b-icon icon="chevron-right" size="is-small"></b-icon>
        </button>
    </div>
</template>

<script>
export default {
    name: 'PrototypeSwitcher',
    props: {
        variants: {
            type: Array,
            default: () => ['A', 'B', 'C']
        },
        current: {
            type: String,
            default: 'A'
        },
        variantNames: {
            type: Object,
            default: () => ({
                A: 'Variant A: Faneblade & Aktiv-markør (Named Tabs)',
                B: 'Variant B: Sandkasse-tilstand (Staging Drawer)',
                C: 'Variant C: Dropdown & Diff-oversigt (Diff Inspector)'
            })
        }
    },
    computed: {
        currentLabel() {
            return this.variantNames[this.current] || `Variant ${this.current}`
        }
    },
    mounted() {
        window.addEventListener('keydown', this.handleKeydown)
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.handleKeydown)
    },
    methods: {
        handleKeydown(e) {
            // Don't intercept if typing in inputs
            const tag = (e.target?.tagName || '').toLowerCase()
            if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target?.isContentEditable) {
                return
            }
            if (e.key === 'ArrowLeft') {
                e.preventDefault()
                this.prev()
            } else if (e.key === 'ArrowRight') {
                e.preventDefault()
                this.next()
            }
        },
        setVariant(variant) {
            this.$emit('change', variant)
            const query = { ...this.$route.query, variant }
            this.$router.replace({ query }).catch(() => {})
        },
        prev() {
            const idx = this.variants.indexOf(this.current)
            const prevIdx = idx <= 0 ? this.variants.length - 1 : idx - 1
            this.setVariant(this.variants[prevIdx])
        },
        next() {
            const idx = this.variants.indexOf(this.current)
            const nextIdx = idx >= this.variants.length - 1 ? 0 : idx + 1
            this.setVariant(this.variants[nextIdx])
        }
    }
}
</script>

<style scoped>
.prototype-floating-switcher {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9999;
    display: inline-flex;
    align-items: center;
    background-color: #1e2530;
    color: #ffffff;
    border-radius: 9999px;
    padding: 6px 10px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.15);
    font-size: 13px;
    font-weight: 500;
    user-select: none;
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.prototype-floating-switcher:hover {
    transform: translateX(-50%) translateY(-2px);
}

.switcher-arrow {
    background: transparent;
    border: none;
    color: #a0aec0;
    cursor: pointer;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s, color 0.15s;
    outline: none;
}

.switcher-arrow:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.switcher-content {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
}

.switcher-tag {
    background-color: #485fc7;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.05em;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
}

.switcher-label {
    white-space: nowrap;
}
</style>

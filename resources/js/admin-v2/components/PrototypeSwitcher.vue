<template>
    <!-- PROTOTYPE: floating variant switcher. Cycles ?variant= with the arrows or ← / →. Never ship. -->
    <div class="prototype-switcher">
        <button class="button is-small is-dark is-rounded" @click="step(-1)" aria-label="Forrige variant">‹</button>
        <span class="px-3">{{ current.key }} ({{ current.name }})</span>
        <button class="button is-small is-dark is-rounded" @click="step(1)" aria-label="Næste variant">›</button>
    </div>
</template>
<script>
export default {
    name: 'PrototypeSwitcher',
    props: {variants: Array},
    computed: {
        index() {
            return Math.max(0, this.variants.findIndex(variant => variant.key === this.$route.query.variant))
        },
        current() {
            return this.variants[this.index]
        }
    },
    mounted() {
        window.addEventListener('keydown', this.onKey)
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.onKey)
    },
    methods: {
        step(delta) {
            const next = this.variants[(this.index + delta + this.variants.length) % this.variants.length]
            this.$router.replace({query: {...this.$route.query, variant: next.key}})
        },
        onKey(event) {
            if (event.target.closest('input, textarea, select, [contenteditable]')) return
            if (event.key === 'ArrowLeft') this.step(-1)
            if (event.key === 'ArrowRight') this.step(1)
        }
    }
}
</script>
<style scoped>
.prototype-switcher {
    position: fixed;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 100;
    display: flex;
    align-items: center;
    background: #2b1a3d;
    color: #fff;
    border-radius: 999px;
    padding: 0.35rem 0.5rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
    font-size: 0.85rem;
}
</style>

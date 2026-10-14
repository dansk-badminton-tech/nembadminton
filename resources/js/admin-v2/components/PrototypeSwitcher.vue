<template>
    <!-- PROTOTYPE switcher: only shown while a ?variant= is in the URL. -->
    <div v-if="current" class="prototype-switcher" dusk="prototype-switcher">
        <button type="button" aria-label="Forrige variant" @click="step(-1)">‹</button>
        <span class="label">PROTOTYPE · {{ current }} ({{ variants[current] }})</span>
        <button type="button" aria-label="Næste variant" @click="step(1)">›</button>
    </div>
</template>
<script>
export default {
    name: 'PrototypeSwitcher',
    props: {
        // {key: name}
        variants: {type: Object, required: true}
    },
    computed: {
        keys() {
            return Object.keys(this.variants)
        },
        current() {
            const variant = this.$route.query.variant
            return this.keys.includes(variant) ? variant : null
        }
    },
    mounted() {
        this.onKey = event => {
            const target = event.target
            if (target.closest?.('input, textarea, select, [contenteditable]')) return
            if (event.key === 'ArrowLeft') this.step(-1)
            if (event.key === 'ArrowRight') this.step(1)
        }
        window.addEventListener('keydown', this.onKey)
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.onKey)
    },
    methods: {
        step(delta) {
            if (!this.current) return
            const index = (this.keys.indexOf(this.current) + delta + this.keys.length) % this.keys.length
            this.$router.replace({query: {...this.$route.query, variant: this.keys[index]}})
        }
    }
}
</script>
<style scoped>
.prototype-switcher {
    position: fixed;
    bottom: 1.25rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.4rem 0.6rem;
    border-radius: 999px;
    background: #1f1235;
    color: #fff;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    font-size: 0.9rem;
}
.prototype-switcher button {
    border: 0;
    background: #6d28d9;
    color: #fff;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    font-size: 1.2rem;
    cursor: pointer;
}
.prototype-switcher .label {
    white-space: nowrap;
    color: #fff;
    font-weight: 600;
}
</style>

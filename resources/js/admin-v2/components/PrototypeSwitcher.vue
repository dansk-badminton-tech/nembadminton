<!-- PROTOTYPE switcher: throwaway, do not merge to master. Only rendered when ?variant= is in the URL or in dev. -->
<template>
    <div class="prototype-switcher" dusk="prototype-switcher">
        <button type="button" @click="step(-1)" aria-label="Forrige variant">&larr;</button>
        <span class="label">{{ current }} <small>({{ variants[current] }})</small></span>
        <button type="button" @click="step(1)" aria-label="Næste variant">&rarr;</button>
        <label class="toggle" v-if="toggleParam">
            <input type="checkbox" :checked="$route.query[toggleParam] !== undefined" @change="toggle">
            {{ toggleLabel }}
        </label>
    </div>
</template>
<script>
export default {
    name: 'PrototypeSwitcher',
    props: {
        variants: {type: Object, required: true},
        current: {type: String, required: true},
        toggleParam: {type: String, default: null},
        toggleLabel: {type: String, default: null},
    },
    mounted() {
        window.addEventListener('keydown', this.onKey)
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.onKey)
    },
    methods: {
        step(direction) {
            const keys = Object.keys(this.variants)
            const index = (keys.indexOf(this.current) + direction + keys.length) % keys.length
            this.$router.replace({query: {...this.$route.query, variant: keys[index]}})
        },
        toggle(evt) {
            const query = {...this.$route.query}
            if (evt.target.checked) {
                query[this.toggleParam] = '1'
            } else {
                delete query[this.toggleParam]
            }
            this.$router.replace({query})
        },
        onKey(evt) {
            const target = evt.target
            if (target.closest && target.closest('input, textarea, select, [contenteditable]')) {
                return
            }
            if (evt.key === 'ArrowLeft') this.step(-1)
            if (evt.key === 'ArrowRight') this.step(1)
        }
    }
}
</script>
<style scoped>
.prototype-switcher {
    position: fixed;
    bottom: 16px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 100;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 12px;
    border-radius: 999px;
    background: #111;
    color: #fff;
    box-shadow: 0 4px 16px rgba(0, 0, 0, .35);
    font: 14px/1.2 system-ui, sans-serif;
    white-space: nowrap;
}
.prototype-switcher button {
    background: #333;
    color: #fff;
    border: 0;
    border-radius: 999px;
    width: 28px;
    height: 28px;
    cursor: pointer;
}
.prototype-switcher .label,
.prototype-switcher .toggle { color: #fff !important; }
.prototype-switcher .label small { opacity: .7; }
.prototype-switcher .toggle { font-size: 12px; opacity: .85; cursor: pointer; }
</style>

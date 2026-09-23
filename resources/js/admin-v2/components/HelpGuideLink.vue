<template>
    <a v-if="newTab" :href="$router.resolve(path).href" target="_blank" rel="noopener" class="help-guide-link">
        <b-icon icon="help-circle-outline" size="is-small"></b-icon>
        <span><slot/></span>
    </a>
    <router-link v-else :to="path" class="help-guide-link">
        <b-icon icon="help-circle-outline" size="is-small"></b-icon>
        <span><slot/></span>
    </router-link>
</template>

<script>
// A task-named link from a product screen to the User Guide that owns the task.
// Use newTab where following the link would interrupt work in progress.
export default {
    name: 'HelpGuideLink',
    props: {
        guide: {type: String, required: true},
        newTab: {type: Boolean, default: false}
    },
    computed: {
        path() {
            return `/help/guides/${this.guide}`
        }
    }
}
</script>

<style scoped>
.help-guide-link {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}
</style>

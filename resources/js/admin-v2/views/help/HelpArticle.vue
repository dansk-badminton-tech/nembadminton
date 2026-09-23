<template>
    <article v-if="document" dusk="help-article" class="help-article">
        <p class="help-article__back"><router-link :to="backPath">{{ backLabel }}</router-link> /</p>
        <header>
            <p v-if="document.published" class="help-article__date">{{ formatDate(document.published) }}</p>
            <h1>{{ document.title }}</h1>
            <p>{{ document.summary }}</p>
        </header>
        <div class="help-prose" v-html="document.html"></div>
        <router-link v-if="linkedGuide" class="help-guide-link" :to="`/help/guides/${linkedGuide.slug}`">
            <span>Læs vejledningen</span>
            <strong>{{ linkedGuide.title }} &rarr;</strong>
        </router-link>
    </article>
    <section v-else class="help-article help-article--missing">
        <h1>Siden blev ikke fundet</h1>
        <router-link to="/help">Gå til Hjælp</router-link>
    </section>
</template>

<script>
import {findAnnouncement, findGuide} from '@/help/documents'

export default {
    name: 'HelpArticle',
    props: {kind: {type: String, required: true}},
    computed: {
        document() {
            return this.kind === 'guide' ? findGuide(this.$route.params.slug) : findAnnouncement(this.$route.params.slug)
        },
        linkedGuide() {
            return this.document?.guide ? findGuide(this.document.guide) : null
        },
        backPath() {
            return this.kind === 'guide' ? '/help/guides' : '/help/news'
        },
        backLabel() {
            return this.kind === 'guide' ? 'Vejledninger' : 'Nyheder'
        },
    },
    watch: {
        document: {
            immediate: true,
            handler(document) {
                if (document) {
                    window.document.title = `${document.title} — Nembadminton`
                }
            },
        },
    },
    methods: {
        formatDate(date) {
            return new Intl.DateTimeFormat('da-DK', {dateStyle: 'long'}).format(new Date(`${date}T00:00:00`))
        },
    },
}
</script>

<style scoped>
.help-guide-link { margin-top: 3rem; padding: 1.25rem; display: flex; flex-direction: column; color: white; background: var(--help-ink); }
.help-guide-link span { color: #c8d0d6; }
.help-guide-link strong { margin-top: .25rem; font-size: 1.25rem; }
.help-article--missing { min-height: 60vh; }
</style>

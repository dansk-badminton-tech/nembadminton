<template>
    <help-article-content v-if="document" :document="document" />
    <section v-else class="help-article help-article--missing">
        <h1>Siden blev ikke fundet</h1>
        <router-link to="/help">Gå til Hjælp</router-link>
    </section>
</template>

<script>
import HelpArticleContent from './HelpArticleContent.vue'
import {pages} from '@/help/documents'

export default {
    name: 'HelpPage',
    components: {HelpArticleContent},
    props: {page: {type: String, required: true}},
    computed: {document() { return pages[this.page] }},
}
</script>

<template>
    <article class="help-article">
        <p class="help-article__back"><router-link to="/help">Hjælp</router-link> /</p>
        <header><h1>{{ document.title }}</h1><p>{{ document.summary }}</p></header>
        <div class="help-prose" v-html="document.html"></div>
    </article>
</template>

<script>
export default {name: 'HelpArticleContent', props: {document: {type: Object, required: true}}}
</script>

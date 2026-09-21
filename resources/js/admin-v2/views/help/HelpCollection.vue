<template>
    <section class="help-page">
        <p class="help-page__back"><router-link to="/help">Hjælp</router-link> /</p>
        <header class="help-page__header">
            <p class="help-page__eyebrow">{{ eyebrow }}</p>
            <h1>{{ title }}</h1>
            <p>{{ introduction }}</p>
        </header>

        <div v-if="items.length" class="help-list">
            <router-link v-for="item in items" :key="item.slug" :to="`${basePath}/${item.slug}`" class="help-list__item">
                <time v-if="item.published" :datetime="item.published">{{ formatDate(item.published) }}</time>
                <div><h2>{{ item.title }}</h2><p>{{ item.summary }}</p></div>
                <span aria-hidden="true">&rarr;</span>
            </router-link>
        </div>
        <p v-else class="help-empty">{{ emptyText }}</p>
    </section>
</template>

<script>
export default {
    name: 'HelpCollection',
    props: {
        title: {type: String, required: true},
        eyebrow: {type: String, required: true},
        introduction: {type: String, required: true},
        emptyText: {type: String, required: true},
        basePath: {type: String, required: true},
        items: {type: Array, required: true},
    },
    methods: {
        formatDate(date) {
            return new Intl.DateTimeFormat('da-DK', {dateStyle: 'long'}).format(new Date(`${date}T00:00:00`))
        },
    },
}
</script>

<style scoped>
.help-page { max-width: 900px; padding: 3rem 0 6rem; }
.help-page__back { color: var(--help-muted); }
.help-page__back a { color: var(--help-accent); }
.help-page__header { max-width: 720px; padding: 3rem 0; }
.help-page__eyebrow { color: var(--help-accent); font-size: .78rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.help-page__header h1 { margin: .5rem 0 1rem; font-size: clamp(2.75rem, 7vw, 5rem); line-height: .95; letter-spacing: -.055em; }
.help-page__header > p:last-child { color: var(--help-muted); font-size: 1.15rem; }
.help-list { border-top: 1px solid var(--help-line); }
.help-list__item { display: grid; grid-template-columns: 9rem 1fr auto; align-items: center; gap: 1.5rem; padding: 1.5rem 0; color: var(--help-ink); border-bottom: 1px solid var(--help-line); }
.help-list__item:hover h2 { color: var(--help-accent); }
.help-list__item h2 { margin: 0 0 .35rem; font-size: 1.4rem; }
.help-list__item p, .help-list__item time { margin: 0; color: var(--help-muted); }
.help-list__item > span { font-size: 1.5rem; }
.help-empty { padding: 2rem; color: var(--help-muted); border: 1px dashed var(--help-line); }
@media (max-width: 640px) {
    .help-list__item { grid-template-columns: 1fr auto; }
    .help-list__item time { grid-column: 1 / -1; }
}
</style>

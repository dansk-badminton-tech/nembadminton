<template>
    <div>
        <section class="help-intro">
            <p class="help-eyebrow">Nembadminton Hjælp</p>
            <h1>Find svaret. Kom videre.</h1>
            <p>Vejledninger og svar til dig, der bruger Nembadminton.</p>
        </section>

        <section class="help-grid" aria-label="Hjælpemner">
            <router-link class="help-tile help-tile--wide" to="/help/guides">
                <span>01</span><h2>Vejledninger</h2><p>Trin for trin gennem funktionerne.</p>
            </router-link>
            <router-link class="help-tile" to="/help/news">
                <span>02</span><h2>Nyheder</h2><p>Se, hvad der har ændret sig.</p>
            </router-link>
            <router-link class="help-tile" to="/help/faq">
                <span>03</span><h2>Ofte stillede spørgsmål</h2><p>Korte svar på almindelige spørgsmål.</p>
            </router-link>
            <router-link class="help-tile" to="/help/about">
                <span>04</span><h2>Om Nembadminton</h2><p>Visionen og personerne bag.</p>
            </router-link>
        </section>

        <section v-if="news.length" class="help-latest">
            <div><p class="help-eyebrow">Seneste nyt</p><h2>Nye ændringer i Nembadminton</h2></div>
            <article v-for="announcement in news.slice(0, 3)" :key="announcement.slug">
                <time :datetime="announcement.published">{{ formatDate(announcement.published) }}</time>
                <router-link :to="`/help/news/${announcement.slug}`">{{ announcement.title }}</router-link>
            </article>
        </section>
    </div>
</template>

<script>
import {news} from '@/help/documents'

export default {
    name: 'HelpHome',
    data: () => ({news}),
    methods: {
        formatDate(date) {
            return new Intl.DateTimeFormat('da-DK', {dateStyle: 'long'}).format(new Date(`${date}T00:00:00`))
        },
    },
}
</script>

<style scoped>
.help-intro { padding: clamp(4rem, 10vw, 8rem) 0 3rem; max-width: 780px; }
.help-eyebrow { margin-bottom: .75rem; color: var(--help-accent); font-size: .78rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.help-intro h1 { max-width: 700px; margin: 0; font-size: clamp(3rem, 8vw, 6.5rem); line-height: .92; letter-spacing: -.065em; }
.help-intro > p:last-child { margin-top: 1.5rem; color: var(--help-muted); font-size: 1.25rem; }
.help-grid { display: grid; grid-template-columns: repeat(3, 1fr); border-top: 1px solid var(--help-line); border-left: 1px solid var(--help-line); }
.help-tile { min-height: 220px; padding: 1.5rem; display: flex; flex-direction: column; color: var(--help-ink); border-right: 1px solid var(--help-line); border-bottom: 1px solid var(--help-line); transition: background-color .2s, color .2s; }
.help-tile--wide { grid-column: span 3; min-height: 180px; }
.help-tile:hover { color: white; background: var(--help-ink); }
.help-tile span { color: var(--help-accent); font-weight: 800; }
.help-tile h2 { margin: auto 0 .5rem; font-size: clamp(1.45rem, 3vw, 2.1rem); line-height: 1.05; }
.help-tile p { margin: 0; color: var(--help-muted); }
.help-tile:hover p { color: #c8d0d6; }
.help-latest { margin-top: 5rem; display: grid; grid-template-columns: 1.2fr 2fr; gap: 3rem; }
.help-latest h2 { font-size: 2rem; }
.help-latest article { grid-column: 2; display: grid; grid-template-columns: 9rem 1fr; gap: 1rem; padding: 1rem 0; border-top: 1px solid var(--help-line); }
.help-latest article a { color: var(--help-ink); font-weight: 700; }
.help-latest time { color: var(--help-muted); }
@media (max-width: 768px) {
    .help-grid { grid-template-columns: 1fr; }
    .help-tile--wide { grid-column: auto; }
    .help-tile { min-height: 180px; }
    .help-latest { grid-template-columns: 1fr; gap: 1rem; }
    .help-latest article { grid-column: 1; grid-template-columns: 1fr; gap: .25rem; }
}
</style>

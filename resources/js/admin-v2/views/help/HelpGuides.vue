<template>
    <section class="help-page">
        <p class="help-page__back"><router-link to="/help">Hjælp</router-link> /</p>
        <header class="help-page__header">
            <p class="help-page__eyebrow">Vejledninger · Fra start til deling</p>
            <h1>Gør holdrunden klar, ét skridt ad gangen</h1>
            <p>Følg hele forløbet, eller gå direkte til den opgave, du står med.</p>
            <router-link v-if="overview.firstStep" class="help-journey__start" :to="guidePath(overview.firstStep)">Start med første trin</router-link>
        </header>

        <ol v-if="overview.stages.length" class="help-journey" aria-label="Forløbet i en holdrunde">
            <li v-for="stage in overview.stages" :key="stage.key" class="help-journey__stage">
                <span class="help-journey__number" aria-hidden="true">{{ stage.number }}</span>
                <div>
                    <p class="help-journey__label">{{ stage.label }}</p>
                    <router-link v-if="stage.step" :to="guidePath(stage.step)" class="help-journey__guide">
                        <h2>{{ stage.step.title }}</h2>
                        <p>{{ stage.step.summary }}</p>
                        <strong>Åbn vejledning <span aria-hidden="true">&rarr;</span></strong>
                    </router-link>
                    <router-link v-for="{guide, label} in stage.branches" :key="guide.slug" :to="guidePath(guide)" class="help-journey__branch">
                        <small>{{ label }}</small>
                        <h3>{{ guide.title }}</h3>
                        <p>{{ guide.summary }}</p>
                    </router-link>
                </div>
            </li>
        </ol>

        <aside v-if="overview.troubleshooting.length" class="help-troubleshooting">
            <div>
                <p class="help-page__eyebrow">Når noget driller</p>
                <h2>Genveje til problemer undervejs</h2>
            </div>
            <router-link v-for="{guide, stageLabel} in overview.troubleshooting" :key="guide.slug" :to="guidePath(guide)">
                <small>{{ stageLabel }}</small>
                <strong>{{ guide.title }} <span aria-hidden="true">&rarr;</span></strong>
            </router-link>
        </aside>

        <section v-if="overview.other.length" class="help-other">
            <h2>Andre vejledninger</h2>
            <div class="help-list">
                <router-link v-for="guide in overview.other" :key="guide.slug" :to="guidePath(guide)" class="help-list__item">
                    <div><h3>{{ guide.title }}</h3><p>{{ guide.summary }}</p></div>
                    <span aria-hidden="true">&rarr;</span>
                </router-link>
            </div>
        </section>

        <p v-if="!guides.length" class="help-empty">Der er endnu ikke udgivet nogen vejledninger.</p>
    </section>
</template>

<script>
import {guideOverview, guides} from '@/help/documents'

export default {
    name: 'HelpGuides',
    data: () => ({guides, overview: guideOverview}),
    methods: {
        guidePath(guide) {
            return `/help/guides/${guide.slug}`
        },
    },
}
</script>

<style scoped>
.help-page { max-width: 900px; padding: 3rem 0 6rem; }
.help-page__back { color: var(--help-muted); }
.help-page__back a { color: var(--help-accent); }
.help-page__header { max-width: 780px; padding: 3rem 0; }
.help-page__eyebrow { margin: 0 0 .6rem; color: var(--help-accent); font-size: .78rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.help-page__header h1 { margin: .5rem 0 1rem; font-size: clamp(2.75rem, 7vw, 5rem); line-height: .95; letter-spacing: -.055em; }
.help-page__header > p:not(.help-page__eyebrow) { color: var(--help-muted); font-size: 1.15rem; }
.help-journey__start { display: inline-block; margin-top: 1.5rem; padding: .8rem 1.2rem; color: white; background: var(--help-ink); }
.help-journey__start:hover { color: white; background: var(--help-accent); }

.help-journey { margin: 0; padding: 0; list-style: none; border-top: 1px solid var(--help-line); }
.help-journey__stage { display: grid; grid-template-columns: 5rem 1fr; gap: 1.5rem; padding: 2rem 0; border-bottom: 1px solid var(--help-line); }
.help-journey__number { color: var(--help-accent); font-size: 2rem; font-weight: 800; line-height: 1; }
.help-journey__label, .help-journey__branch small, .help-troubleshooting small { margin: 0; color: var(--help-muted); font-size: .78rem; letter-spacing: .08em; text-transform: uppercase; }
.help-journey__guide { display: block; color: var(--help-ink); }
.help-journey__guide h2 { margin: .25rem 0; font-size: clamp(1.5rem, 3vw, 2.2rem); line-height: 1.1; }
.help-journey__guide p { margin: .3rem 0 1rem; color: var(--help-muted); }
.help-journey__guide:hover h2, .help-journey__branch:hover h3, .help-list__item:hover h3 { color: var(--help-accent); }
.help-journey__branch { display: block; margin-top: 1rem; padding: .25rem 0 .25rem 1.5rem; color: var(--help-ink); border-left: 2px dotted var(--help-line); }
.help-journey__branch h3 { margin: .2rem 0; font-size: 1.25rem; }
.help-journey__branch p { margin: 0; color: var(--help-muted); }

.help-troubleshooting { display: grid; grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); gap: 1px; margin-top: 4rem; background: var(--help-line); border: 1px solid var(--help-line); }
.help-troubleshooting > * { padding: 1.5rem; background: #fffdf8; }
.help-troubleshooting h2 { margin: 0; font-size: 1.5rem; }
.help-troubleshooting a { display: flex; flex-direction: column; gap: .5rem; color: var(--help-ink); }
.help-troubleshooting a:hover strong { color: var(--help-accent); }

.help-other { margin-top: 4rem; }
.help-other h2 { margin-bottom: 1rem; font-size: 1.5rem; }
.help-list { border-top: 1px solid var(--help-line); }
.help-list__item { display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 1.5rem; padding: 1.5rem 0; color: var(--help-ink); border-bottom: 1px solid var(--help-line); }
.help-list__item h3 { margin: 0 0 .35rem; font-size: 1.4rem; }
.help-list__item p { margin: 0; color: var(--help-muted); }
.help-list__item > span { font-size: 1.5rem; }
.help-empty { padding: 2rem; color: var(--help-muted); border: 1px dashed var(--help-line); }

@media (max-width: 640px) {
    .help-journey__stage { grid-template-columns: 2.5rem 1fr; gap: .5rem; }
    .help-journey__number { font-size: 1.5rem; }
    .help-journey__branch { padding-left: 1rem; }
}
</style>

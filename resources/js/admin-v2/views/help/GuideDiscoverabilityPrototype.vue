<template>
    <section class="prototype">
        <div class="prototype__notice">
            <strong>Prototype</strong>
            <span>Tre modeller for, hvordan vejledninger findes og hænger sammen. Links udfører ingen handling.</span>
        </div>

        <template v-if="variant === 'journey'">
            <header class="journey-hero">
                <p class="eyebrow">Vejledninger · Fra start til deling</p>
                <h1>Gør holdrunden klar, ét skridt ad gangen</h1>
                <p>Følg hele forløbet, eller gå direkte til den opgave, du står med.</p>
                <div class="journey-hero__actions">
                    <button type="button" @click="selectGuide('Opret og klargør en holdrunde')">Start med at oprette</button>
                    <a href="#journey-troubleshooting">Jeg skal løse et problem</a>
                </div>
            </header>

            <div class="journey-progress" aria-label="Forløb gennem en holdrunde">
                <article v-for="(step, index) in journey" :key="step.title" :class="{'journey-progress__step--optional': step.optional}">
                    <span>{{ index + 1 }}</span>
                    <div>
                        <small>{{ step.phase }}<template v-if="step.optional"> · Valgfrit</template></small>
                        <h2>{{ step.title }}</h2>
                        <p>{{ step.summary }}</p>
                        <button type="button" @click="selectGuide(step.title)">Åbn vejledning <span aria-hidden="true">→</span></button>
                    </div>
                </article>
            </div>

            <aside id="journey-troubleshooting" class="journey-aside">
                <div>
                    <p class="eyebrow">Når planen ændrer sig</p>
                    <h2>Genveje til problemer undervejs</h2>
                </div>
                <button type="button" @click="selectGuide('Håndtér afbud før og under en holdrunde')">En spiller melder afbud <span>→</span></button>
                <button type="button" @click="selectGuide('Opret en spiller, der mangler i Nembadminton')">Jeg kan ikke finde en spiller <span>→</span></button>
            </aside>

            <div class="placement-note">
                <strong>I produktet:</strong> Vis “Se næste trin” efter oprettelse og et diskret “Vejledning til denne side” på holdrunde-, scenarie- og sendesiderne.
            </div>
        </template>

        <template v-else-if="variant === 'tasks'">
            <header class="tasks-header">
                <div>
                    <p class="eyebrow">Vejledninger</p>
                    <h1>Hvad vil du gøre?</h1>
                    <p>Find den korteste vej til dit mål. Du behøver ikke læse vejledningerne i rækkefølge.</p>
                </div>
                <label>
                    <span>Søg efter en opgave</span>
                    <input v-model="search" type="search" placeholder="Fx afbud, dele eller spiller">
                </label>
            </header>

            <div class="task-layout">
                <nav aria-label="Emner">
                    <p>Gå til</p>
                    <a v-for="group in filteredTaskGroups" :key="group.phase" :href="`#${group.id}`">{{ group.phase }}</a>
                    <a href="#task-map">Se hele forløbet</a>
                </nav>
                <div class="task-groups">
                    <section v-for="group in filteredTaskGroups" :id="group.id" :key="group.phase">
                        <header><span>{{ group.number }}</span><h2>{{ group.phase }}</h2></header>
                        <button v-for="guide in group.guides" :key="guide.title" type="button" @click="selectGuide(guide.title)">
                            <span><strong>{{ guide.title }}</strong><small>{{ guide.summary }}</small></span>
                            <span aria-hidden="true">→</span>
                        </button>
                    </section>
                    <p v-if="!filteredTaskGroups.length" class="tasks-empty">Ingen vejledninger matcher din søgning.</p>
                </div>
            </div>

            <section id="task-map" class="task-map">
                <div><p class="eyebrow">Overblik</p><h2>Sådan hænger opgaverne sammen</h2></div>
                <ol>
                    <li>Opret og klargør</li><li>Håndtér afbud</li><li>Lav holdopstillingen</li><li>Del eller send</li>
                </ol>
                <p>Hver vejledning slutter med “Før du går videre” og højst to relevante links: næste normale trin og en mulig problemløsning.</p>
            </section>

            <div class="placement-note">
                <strong>I produktet:</strong> Brug præcise tekstlinks ved fejl og tomme tilstande, fx “Kan du ikke finde spilleren? Se hvordan du opretter en spiller”. Ingen generel hjælpeknap på alle skærme.
            </div>
        </template>

        <template v-else>
            <header class="context-header">
                <p class="eyebrow">Holdrunder / Runde 4</p>
                <div><h1>Runde 4 · 10. oktober</h1><button type="button">Del</button><button type="button">Send hold til spillere</button></div>
            </header>

            <div class="context-layout">
                <section class="context-workspace">
                    <div class="context-search">
                        <h2>Find spillere</h2>
                        <div class="fake-input">Søg efter navn eller kategori</div>
                        <div class="fake-player">Mangler den spiller, du leder efter?</div>
                        <button type="button" @click="selectGuide('Opret en spiller, der mangler i Nembadminton')">Se, hvad du gør <span>→</span></button>
                    </div>
                    <div class="context-team">
                        <div><small>Officiel holdopstilling</small><h2>Serie 1</h2></div>
                        <div class="fake-court"><span>1. herresingle</span><strong>Mikkel Hansen</strong></div>
                        <div class="fake-court fake-court--warning"><span>1. damesingle</span><strong>Ikke udfyldt</strong></div>
                        <button class="context-inline-help" type="button" @click="selectGuide('Lav og kontrollér den officielle holdopstilling')">Sådan laver og kontrollerer du holdopstillingen →</button>
                    </div>
                </section>

                <aside class="context-guide">
                    <button class="context-guide__close" type="button" aria-label="Luk">×</button>
                    <p class="eyebrow">Hjælp til denne side</p>
                    <h2>Gør holdopstillingen klar</h2>
                    <p>Du er ved trin 3 af 4 i den normale arbejdsgang.</p>
                    <ol>
                        <li class="is-done"><span>✓</span> Opret og klargør holdrunden</li>
                        <li class="is-current"><span>3</span> Lav og kontrollér holdopstillingen</li>
                        <li><span>4</span> Del den officielle holdopstilling</li>
                    </ol>
                    <button class="context-guide__primary" type="button" @click="selectGuide('Lav og kontrollér den officielle holdopstilling')">Åbn vejledningen</button>
                    <div class="context-related">
                        <strong>Relevant lige nu</strong>
                        <button type="button" @click="selectGuide('Arbejd med scenarier i en holdrunde')">Sammenlign scenarier →</button>
                        <button type="button" @click="selectGuide('Håndtér afbud før og under en holdrunde')">Registrér et afbud →</button>
                    </div>
                    <a href="/help/guides" @click.prevent>Se alle vejledninger</a>
                </aside>
            </div>

            <div class="placement-note placement-note--context">
                <strong>I Hjælp:</strong> Bevar en fuld, opgaveopdelt vejledningsoversigt. I produktet åbner “Hjælp til denne side” et panel med nuværende trin, næste trin og kun de relevante afstikkere.
            </div>
        </template>

        <div v-if="selectedGuide" class="prototype-toast" role="status">
            <span>Eksempel: “{{ selectedGuide }}” ville åbne som en rigtig vejledning.</span>
            <button type="button" aria-label="Luk besked" @click="selectedGuide = ''">×</button>
        </div>

        <nav v-if="!isProduction" class="prototype-switcher" aria-label="Skift prototype">
            <button type="button" aria-label="Forrige variant" @click="cycle(-1)">←</button>
            <div><small>Variant {{ currentIndex + 1 }} af {{ variants.length }}</small><strong>{{ currentVariant.label }}</strong></div>
            <button type="button" aria-label="Næste variant" @click="cycle(1)">→</button>
        </nav>
    </section>
</template>

<script>
const variants = [
    {key: 'journey', label: 'Forløbet først'},
    {key: 'tasks', label: 'Opgaven først'},
    {key: 'context', label: 'Hjælp i arbejdet'},
]

const guides = {
    setup: {title: 'Opret og klargør en holdrunde', summary: 'Vælg hold, datoer, rangliste og praktiske oplysninger.'},
    cancellation: {title: 'Håndtér afbud før og under en holdrunde', summary: 'Indsaml afbud og forstå, hvordan de påvirker spillerlisten.'},
    lineup: {title: 'Lav og kontrollér den officielle holdopstilling', summary: 'Placér spillerne, forstå advarslerne og kontrollér resultatet.'},
    scenarios: {title: 'Arbejd med scenarier i en holdrunde', summary: 'Sammenlign alternativer uden at ændre den officielle holdopstilling.'},
    share: {title: 'Del den officielle holdopstilling', summary: 'Vælg mellem et levende link og en statisk CSV-fil.'},
    notify: {title: 'Send holdrunden til spillerne', summary: 'Vælg modtagere, skriv beskeden og kontrollér afsendelsen.'},
    missing: {title: 'Opret en spiller, der mangler i Nembadminton', summary: 'Kom videre, når en spiller ikke findes via den normale søgning.'},
}

export default {
    name: 'GuideDiscoverabilityPrototype',
    data() {
        const requestedVariant = this.$route.query.variant
        return {
            variants,
            variant: variants.some(item => item.key === requestedVariant) ? requestedVariant : variants[0].key,
            search: '',
            selectedGuide: '',
            journey: [
                {...guides.setup, phase: 'Grundlag'},
                {...guides.cancellation, phase: 'Tilgængelighed'},
                {...guides.lineup, phase: 'Holdopstilling'},
                {...guides.scenarios, phase: 'Alternativer', optional: true},
                {...guides.share, phase: 'Kommunikation'},
                {...guides.notify, phase: 'Alternativ', optional: true},
            ],
            taskGroups: [
                {id: 'task-prepare', number: '01', phase: 'Forbered holdrunden', guides: [guides.setup, guides.cancellation]},
                {id: 'task-lineup', number: '02', phase: 'Lav holdopstillingen', guides: [guides.lineup, guides.scenarios, guides.missing]},
                {id: 'task-share', number: '03', phase: 'Del med spillerne', guides: [guides.share, guides.notify]},
            ],
        }
    },
    computed: {
        currentIndex() {
            return this.variants.findIndex(item => item.key === this.variant)
        },
        currentVariant() {
            return this.variants[this.currentIndex]
        },
        filteredTaskGroups() {
            const query = this.search.trim().toLocaleLowerCase('da-DK')
            if (!query) return this.taskGroups

            return this.taskGroups.map(group => ({
                ...group,
                guides: group.guides.filter(guide => `${guide.title} ${guide.summary}`.toLocaleLowerCase('da-DK').includes(query)),
            })).filter(group => group.guides.length)
        },
        isProduction() {
            return import.meta.env.PROD
        },
    },
    watch: {
        '$route.query.variant'(variant) {
            if (this.variants.some(item => item.key === variant)) this.variant = variant
        },
    },
    mounted() {
        window.addEventListener('keydown', this.handleKeydown)
        if (this.$route.query.variant !== this.variant) this.replaceVariant(this.variant)
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.handleKeydown)
    },
    methods: {
        cycle(direction) {
            const nextIndex = (this.currentIndex + direction + this.variants.length) % this.variants.length
            this.replaceVariant(this.variants[nextIndex].key)
        },
        replaceVariant(variant) {
            this.$router.replace({query: {...this.$route.query, variant}})
        },
        handleKeydown(event) {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return
            if (event.target.matches('input, textarea, [contenteditable]')) return
            this.cycle(event.key === 'ArrowLeft' ? -1 : 1)
        },
        selectGuide(title) {
            this.selectedGuide = title
        },
    },
}
</script>

<style scoped>
.prototype { padding: 2rem 0 8rem; }
.prototype button, .prototype input { font: inherit; }
.prototype button { cursor: pointer; }
.prototype__notice { display: flex; gap: .8rem; margin-bottom: 2rem; padding: .8rem 1rem; color: #533719; background: #f5e8cc; border: 1px solid #dec89b; }
.prototype__notice strong { text-transform: uppercase; letter-spacing: .08em; }
.eyebrow { margin: 0 0 .6rem; color: var(--help-accent); font-size: .75rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.placement-note { margin-top: 2rem; padding: 1.2rem 1.4rem; color: var(--help-muted); background: #fffdf8; border-left: 4px solid var(--help-accent); }
.placement-note strong { color: var(--help-ink); }

.journey-hero { max-width: 850px; padding: 3rem 0 4rem; }
.journey-hero h1 { max-width: 780px; margin: 0; font-size: clamp(3rem, 8vw, 6rem); line-height: .92; letter-spacing: -.065em; }
.journey-hero > p:not(.eyebrow) { max-width: 650px; margin: 1.5rem 0; color: var(--help-muted); font-size: 1.2rem; }
.journey-hero__actions { display: flex; align-items: center; gap: 1.4rem; }
.journey-hero__actions button { padding: .8rem 1.2rem; color: white; background: var(--help-ink); border: 0; }
.journey-hero__actions a { color: var(--help-ink); text-decoration: underline; text-underline-offset: 4px; }
.journey-progress { border-top: 1px solid var(--help-line); }
.journey-progress article { display: grid; grid-template-columns: 5rem 1fr; gap: 1.5rem; padding: 2rem 0; border-bottom: 1px solid var(--help-line); }
.journey-progress article > span { font-size: 2rem; font-weight: 800; color: var(--help-accent); }
.journey-progress small { color: var(--help-muted); text-transform: uppercase; letter-spacing: .08em; }
.journey-progress h2 { margin: .25rem 0; font-size: clamp(1.5rem, 3vw, 2.2rem); }
.journey-progress p { margin: .3rem 0 1rem; color: var(--help-muted); }
.journey-progress button { padding: 0; color: var(--help-ink); background: none; border: 0; font-weight: 700; }
.journey-progress__step--optional { margin-left: 5rem; padding-left: 1.5rem !important; border-left: 2px dotted var(--help-line); }
.journey-aside { display: grid; grid-template-columns: 1.3fr 1fr 1fr; gap: 1px; margin-top: 4rem; background: var(--help-line); border: 1px solid var(--help-line); }
.journey-aside > * { padding: 1.5rem; background: #fffdf8; }
.journey-aside h2 { margin: 0; }
.journey-aside button { display: flex; justify-content: space-between; text-align: left; color: var(--help-ink); border: 0; }

.tasks-header { display: grid; grid-template-columns: 1.5fr 1fr; align-items: end; gap: 4rem; padding: 3rem 0; border-bottom: 1px solid var(--help-line); }
.tasks-header h1 { margin: 0; font-size: clamp(3rem, 7vw, 5.5rem); line-height: .95; letter-spacing: -.055em; }
.tasks-header p:not(.eyebrow) { color: var(--help-muted); font-size: 1.1rem; }
.tasks-header label span { display: block; margin-bottom: .5rem; font-weight: 700; }
.tasks-header input { width: 100%; padding: .9rem 1rem; color: var(--help-ink); background: #fffdf8; border: 1px solid var(--help-ink); }
.task-layout { display: grid; grid-template-columns: 14rem 1fr; gap: 4rem; padding: 3rem 0; }
.task-layout nav { position: sticky; top: 1rem; height: max-content; display: flex; flex-direction: column; }
.task-layout nav p { margin: 0 0 .6rem; color: var(--help-muted); font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; }
.task-layout nav a { padding: .65rem 0; color: var(--help-ink); border-bottom: 1px solid var(--help-line); }
.task-groups { display: grid; gap: 3rem; }
.task-groups section header { display: flex; align-items: baseline; gap: 1rem; margin-bottom: .8rem; }
.task-groups section header span { color: var(--help-accent); font-weight: 800; }
.task-groups h2 { margin: 0; font-size: 1.8rem; }
.task-groups section > button { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.2rem 0; text-align: left; color: var(--help-ink); background: transparent; border: 0; border-top: 1px solid var(--help-line); }
.task-groups button strong, .task-groups button small { display: block; }
.task-groups button strong { margin-bottom: .25rem; font-size: 1.1rem; }
.task-groups button small { color: var(--help-muted); }
.tasks-empty { padding: 2rem; border: 1px dashed var(--help-line); }
.task-map { display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; padding: 2rem; color: white; background: var(--help-ink); }
.task-map h2 { margin: 0; font-size: 2rem; }
.task-map ol { display: flex; flex-wrap: wrap; gap: .6rem; margin: 0; padding: 0; list-style: none; }
.task-map li { padding: .55rem .75rem; border: 1px solid #60717f; }
.task-map > p { grid-column: 2; margin: 0; color: #c8d0d6; }

.context-header { margin-top: 1rem; padding: 1.5rem; color: white; background: #263948; }
.context-header > div { display: flex; align-items: center; gap: .7rem; }
.context-header h1 { margin: 0 auto 0 0; font-size: 1.7rem; }
.context-header button { padding: .6rem .9rem; color: white; background: transparent; border: 1px solid #91a2ae; }
.context-header button:last-child { color: #263948; background: white; }
.context-layout { display: grid; grid-template-columns: 1fr 21rem; min-height: 600px; background: #eef1f3; border: 1px solid #ccd3d8; border-top: 0; }
.context-workspace { display: grid; grid-template-columns: minmax(13rem, .8fr) 1.2fr; gap: 1rem; padding: 1rem; }
.context-search, .context-team { padding: 1.2rem; background: white; box-shadow: 0 1px 2px #b9c1c7; }
.context-search h2, .context-team h2 { margin: 0 0 1rem; }
.fake-input { padding: .7rem; color: #7d8991; border: 1px solid #b9c1c7; }
.fake-player { margin-top: 2rem; padding: 1rem; color: #7d8991; background: #f5f6f7; }
.context-search button, .context-inline-help { padding: .7rem 0; color: #24627f; background: transparent; border: 0; text-align: left; }
.fake-court { display: flex; justify-content: space-between; gap: 1rem; margin: .7rem 0; padding: 1rem; border: 1px solid #d5dce0; }
.fake-court--warning { background: #fff6dd; border-color: #e1bf62; }
.context-guide { position: relative; padding: 1.5rem; background: #fffdf8; border-left: 1px solid #ccd3d8; box-shadow: -5px 0 18px rgb(38 57 72 / 10%); }
.context-guide__close { position: absolute; top: 1rem; right: 1rem; border: 0; background: transparent; font-size: 1.5rem; }
.context-guide h2 { margin: .3rem 0; font-size: 1.8rem; }
.context-guide > p:not(.eyebrow) { color: var(--help-muted); }
.context-guide ol { margin: 1.5rem 0; padding: 0; list-style: none; }
.context-guide li { display: grid; grid-template-columns: 1.8rem 1fr; gap: .6rem; padding: .7rem 0; color: var(--help-muted); border-bottom: 1px solid var(--help-line); }
.context-guide li span { width: 1.6rem; height: 1.6rem; display: grid; place-items: center; border: 1px solid var(--help-line); border-radius: 50%; }
.context-guide li.is-done span { color: white; background: #507765; }
.context-guide li.is-current { color: var(--help-ink); font-weight: 700; }
.context-guide li.is-current span { color: white; background: var(--help-accent); border-color: var(--help-accent); }
.context-guide__primary { width: 100%; padding: .8rem; color: white; background: var(--help-ink); border: 0; }
.context-related { margin: 1.5rem 0; padding-top: 1rem; border-top: 1px solid var(--help-line); }
.context-related strong, .context-related button { display: block; }
.context-related button { padding: .45rem 0; color: var(--help-ink); background: transparent; border: 0; text-align: left; }
.context-guide > a { color: var(--help-accent); text-decoration: underline; }
.placement-note--context { margin-top: 1rem; }

.prototype-toast { position: fixed; right: 1rem; bottom: 6rem; z-index: 20; max-width: 420px; display: flex; gap: 1rem; padding: 1rem; color: white; background: #263948; box-shadow: 0 8px 24px rgb(0 0 0 / 25%); }
.prototype-toast button { color: white; background: transparent; border: 0; font-size: 1.3rem; }
.prototype-switcher { position: fixed; left: 50%; bottom: 1rem; z-index: 30; display: grid; grid-template-columns: 3rem minmax(12rem, auto) 3rem; align-items: center; transform: translateX(-50%); color: white; background: #111820; border: 1px solid #52616d; border-radius: 999px; box-shadow: 0 8px 24px rgb(0 0 0 / 30%); overflow: hidden; }
.prototype-switcher button { height: 3rem; color: white; background: transparent; border: 0; font-size: 1.2rem; }
.prototype-switcher button:hover { background: #293641; }
.prototype-switcher div { padding: .45rem 1rem; text-align: center; border-right: 1px solid #52616d; border-left: 1px solid #52616d; }
.prototype-switcher small, .prototype-switcher strong { display: block; }
.prototype-switcher small { color: #aeb9c1; font-size: .65rem; text-transform: uppercase; letter-spacing: .08em; }

@media (max-width: 768px) {
    .prototype__notice, .journey-hero__actions { align-items: flex-start; flex-direction: column; }
    .journey-progress article { grid-template-columns: 2.5rem 1fr; gap: .5rem; }
    .journey-progress__step--optional { margin-left: 1rem; }
    .journey-aside, .tasks-header, .task-layout, .task-map, .context-layout, .context-workspace { grid-template-columns: 1fr; }
    .journey-aside { gap: 1px; }
    .tasks-header { gap: 1rem; }
    .task-layout { gap: 2rem; }
    .task-layout nav { position: static; }
    .task-map > p { grid-column: 1; }
    .context-header > div { align-items: stretch; flex-direction: column; }
    .context-header h1 { margin: 0 0 .5rem; }
    .context-guide { border-top: 1px solid #ccd3d8; border-left: 0; }
    .prototype-switcher { width: calc(100% - 2rem); grid-template-columns: 3rem 1fr 3rem; }
}
</style>

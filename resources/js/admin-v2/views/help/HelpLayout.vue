<template>
    <div class="help-shell">
        <header class="help-header">
            <div class="help-header__inner">
                <router-link class="help-brand" to="/help">
                    <span class="help-brand__mark">NB</span>
                    <span>
                        <strong>NemBadminton</strong>
                        <small>Hjælp og vejledninger</small>
                    </span>
                </router-link>

                <button class="help-menu-button" type="button" :aria-expanded="menuOpen" aria-label="Vis menu" @click="menuOpen = !menuOpen">
                    <span></span><span></span><span></span>
                </button>

                <nav class="help-nav" :class="{'help-nav--open': menuOpen}" aria-label="Hjælp">
                    <router-link to="/help/guides">Vejledninger</router-link>
                    <router-link to="/help/news">Nyheder</router-link>
                    <router-link to="/help/faq">FAQ</router-link>
                    <router-link to="/help/about">Om</router-link>
                    <router-link class="help-nav__app-link" to="/home-redirect">Gå til NemBadminton</router-link>
                </nav>
            </div>
        </header>

        <main class="help-main">
            <router-view />
        </main>

        <footer class="help-footer">
            <span>&copy; {{ year }} NemBadminton</span>
            <a href="/privatlivspolitik">Privatlivspolitik</a>
        </footer>
    </div>
</template>

<script>
import {setFullPage} from '@/store/layout'

export default {
    name: 'HelpLayout',
    data() {
        return {
            menuOpen: false,
            year: new Date().getFullYear(),
        }
    },
    created() {
        setFullPage(true)
    },
    beforeUnmount() {
        setFullPage(false)
    },
    watch: {
        $route() {
            this.menuOpen = false
        },
    },
}
</script>

<style>
.help-shell {
    --help-ink: #16212b;
    --help-muted: #5a6875;
    --help-paper: #f7f5ef;
    --help-line: #d9d5ca;
    --help-accent: #d6532d;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    color: var(--help-ink);
    background: var(--help-paper);
}

.help-header {
    border-bottom: 1px solid var(--help-line);
    background: #fffdf8;
}

.help-header__inner {
    width: min(1120px, calc(100% - 2rem));
    min-height: 76px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
}

.help-brand {
    display: inline-flex;
    align-items: center;
    gap: .75rem;
    color: var(--help-ink);
}

.help-brand:hover { color: var(--help-ink); }
.help-brand strong, .help-brand small { display: block; }
.help-brand strong { font-size: 1.05rem; letter-spacing: -.02em; }
.help-brand small { color: var(--help-muted); }

.help-brand__mark {
    width: 42px;
    height: 42px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    color: white;
    background: var(--help-ink);
    font-weight: 800;
    letter-spacing: -.05em;
}

.help-nav { display: flex; align-items: center; gap: 1.4rem; }
.help-nav a { color: var(--help-ink); font-weight: 600; }
.help-nav a.router-link-active:not(.help-nav__app-link) { color: var(--help-accent); }
.help-nav__app-link { padding: .65rem 1rem; border: 1px solid var(--help-ink); border-radius: 4px; }
.help-nav__app-link:hover { color: white; background: var(--help-ink); }

.help-menu-button {
    display: none;
    width: 42px;
    height: 42px;
    padding: 9px;
    border: 1px solid var(--help-line);
    background: transparent;
}

.help-menu-button span { display: block; height: 2px; margin: 4px 0; background: var(--help-ink); }
.help-main { width: min(1120px, calc(100% - 2rem)); margin: 0 auto; flex: 1; }
.help-footer { width: min(1120px, calc(100% - 2rem)); margin: 3rem auto 0; padding: 1.5rem 0; display: flex; gap: 1.5rem; border-top: 1px solid var(--help-line); color: var(--help-muted); }
.help-footer a { color: inherit; text-decoration: underline; }
.help-article { max-width: 760px; padding: 3rem 0 6rem; }
.help-article__back, .help-article__date { color: var(--help-muted); }
.help-article__back a { color: var(--help-accent); }
.help-article header { padding: 3rem 0; border-bottom: 1px solid var(--help-line); }
.help-article header h1 { margin: .5rem 0 1rem; font-size: clamp(2.6rem, 7vw, 5rem); line-height: .98; letter-spacing: -.055em; }
.help-article header > p:last-child { color: var(--help-muted); font-size: 1.2rem; }
.help-prose { padding-top: 2rem; font-size: 1.08rem; line-height: 1.75; }
.help-prose h2 { margin: 2.5rem 0 .75rem; font-size: 1.7rem; }
.help-prose h3 { margin: 2rem 0 .5rem; font-size: 1.3rem; }
.help-prose a { color: var(--help-accent); text-decoration: underline; text-underline-offset: 3px; }
.help-prose ul, .help-prose ol { margin: 1rem 0 1rem 1.5rem; }
.help-prose blockquote { margin: 1.5rem 0; padding: .75rem 1.25rem; border-left: 4px solid var(--help-accent); background: #fffdf8; }
.help-prose table { width: 100%; margin: 1.5rem 0; }
.help-prose th, .help-prose td { padding: .6rem; text-align: left; border-bottom: 1px solid var(--help-line); }

@media (max-width: 768px) {
    .help-header__inner { min-height: 68px; flex-wrap: wrap; padding: .75rem 0; }
    .help-menu-button { display: block; }
    .help-nav { width: 100%; display: none; align-items: stretch; flex-direction: column; gap: 0; padding-bottom: .5rem; }
    .help-nav--open { display: flex; }
    .help-nav a { padding: .75rem 0; }
    .help-nav__app-link { margin-top: .5rem; padding: .65rem 1rem !important; text-align: center; }
}
</style>

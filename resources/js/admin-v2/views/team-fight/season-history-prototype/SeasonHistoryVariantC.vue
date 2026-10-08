<!-- PROTOTYPE (issue #254) variant C: side panel with a vertical timeline, opened from a history icon. Throwaway. -->
<template>
    <b-button size="is-small" title="Opstillet tidligere i sæsonen" icon-right="history" dusk="season-history-trigger" @click="open = true"></b-button>
    <b-sidebar v-model="open" class="season-history-c" position="fixed" right overlay fullheight :mobile="'fullwidth'" :can-cancel="['escape', 'outside']">
        <div class="panel-body">
            <div class="is-flex is-justify-content-space-between is-align-items-start mb-4">
                <div>
                    <p class="heading mb-0">{{ TITLE }}</p>
                    <p class="title is-5">{{ player.name }}</p>
                </div>
                <button class="delete" aria-label="Luk" @click="open = false"></button>
            </div>
            <p class="is-size-7 has-text-grey mb-4">
                Klubbens seneste holdrunder før denne. Viser hvor spilleren blev opstillet, ikke kampe spillet.
            </p>
            <p v-if="history.length === 0" class="has-text-grey">{{ EMPTY_TEXT }}</p>
            <ol class="timeline">
                <li v-for="entry in history" :key="entry.teamRoundId" :class="{'not-placed': !entry.placed}">
                    <p class="has-text-weight-semibold">{{ entry.name }}</p>
                    <p class="is-size-7 has-text-grey mb-1">{{ formatDate(entry.gameDate) }}</p>
                    <p v-if="!entry.placed" class="has-text-grey"><em>{{ NOT_PLACED_TEXT }}</em></p>
                    <div v-for="p in entry.placements" :key="p.category" class="placement">
                        <span class="tag is-info is-light mr-2">{{ p.position }}. {{ p.category }}</span>
                        <span>{{ [p.teamName, p.tier].filter(Boolean).join(' · ') }}</span>
                    </div>
                </li>
            </ol>
        </div>
    </b-sidebar>
</template>
<script>
import {seasonHistoryFor, formatDate, EMPTY_TEXT, NOT_PLACED_TEXT, TITLE} from './stub.js'

export default {
    name: 'SeasonHistoryVariantC',
    props: {player: Object, empty: Boolean},
    data: () => ({open: false, EMPTY_TEXT, NOT_PLACED_TEXT, TITLE}),
    computed: {
        history() {
            return seasonHistoryFor(this.player, {empty: this.empty})
        }
    },
    methods: {formatDate}
}
</script>
<style scoped>
.panel-body {
    padding: 1.5rem;
}
.timeline {
    list-style: none;
    margin: 0;
    padding-left: 1.25rem;
    border-left: 2px solid #dbdbdb;
}
.timeline li {
    position: relative;
    padding-bottom: 1.25rem;
}
.timeline li::before {
    content: '';
    position: absolute;
    left: calc(-1.25rem - 7px);
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #3e8ed0;
}
.timeline li.not-placed::before {
    background: #fff;
    border: 2px solid #b5b5b5;
}
.placement {
    margin-top: 0.25rem;
}
</style>
<style>
.season-history-c .sidebar-content {
    width: 380px;
    max-width: 100vw;
}
</style>

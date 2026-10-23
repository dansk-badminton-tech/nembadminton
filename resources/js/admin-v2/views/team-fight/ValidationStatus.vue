<template>
    <div class="validation-status-container">
        <div class="field is-grouped is-grouped-multiline">
            <div class="control" v-show="!hideIncompleteTeam">
                <b-tooltip
                    type="is-info"
                    :label="incompleteTeamTip"
                    multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">Hold fuldendt</span>
                        <span
                            dusk="validation-incomplete-team"
                            :class="incompleteStatusClass"
                            class="tag is-medium">
                            <b-icon :icon="incompleteStatusIcon" size="is-small" class="mr-1"></b-icon>
                            {{ incompleteStatusText }}
                        </span>
                        <span class="tag is-medium is-white">
                            <b-switch
                                dusk="toggle-incomplete-team-check"
                                size="is-small"
                                :model-value="!ignoreIncompleteTeam"
                                @update:modelValue="$emit('update:ignoreIncompleteTeam', !$event)">
                            </b-switch>
                        </span>
                    </div>
                </b-tooltip>
            </div>
            <div class="control">
                <b-tooltip
                    type="is-info"
                    :label="invalidLevelTip"
                    multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">Niveauvalidering</span>
                        <span
                            dusk="validation-invalid-level"
                            :class="statusClass(invalidLevel)"
                            class="tag is-medium">
                            <b-icon :icon="statusIcon(invalidLevel)" size="is-small" class="mr-1"></b-icon>
                            {{ statusText(invalidLevel) }}
                        </span>
                    </div>
                </b-tooltip>
            </div>
            <div class="control">
                <b-tooltip
                    type="is-info"
                    :label="invalidCategoryTip"
                    multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">Kategorivalidering</span>
                        <span
                            dusk="validation-invalid-category"
                            :class="statusClass(invalidCategory)"
                            class="tag is-medium">
                            <b-icon :icon="statusIcon(invalidCategory)" size="is-small" class="mr-1"></b-icon>
                            {{ statusText(invalidCategory) }}
                        </span>
                    </div>
                </b-tooltip>
            </div>
            <div class="control" v-if="showReserves">
                <b-tooltip
                    type="is-info"
                    :label="reserveTip"
                    multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">Reserver (§43)</span>
                        <span
                            dusk="validation-reserves"
                            :class="reserveStatusClass"
                            class="tag is-medium">
                            <b-icon :icon="reserveStatusIcon" size="is-small" class="mr-1"></b-icon>
                            {{ reserveStatusText }}
                        </span>
                    </div>
                </b-tooltip>
            </div>
            <div class="control" v-if="showPaperSquads">
                <b-tooltip
                    type="is-info"
                    :label="paperSquadTip"
                    multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">Papirhold (§38 stk. 7)</span>
                        <span
                            dusk="validation-paper-squads"
                            :class="paperSquadStatusClass"
                            class="tag is-medium">
                            <b-icon :icon="paperSquadStatusIcon" size="is-small" class="mr-1"></b-icon>
                            {{ paperSquadStatusText }}
                        </span>
                    </div>
                </b-tooltip>
            </div>
        </div>

        <article v-if="reserveMessages.length" class="message is-warning is-small mt-3" dusk="reserve-warnings">
            <div class="message-body">
                <ul>
                    <li v-for="message in reserveMessages" :key="message">{{ message }}</li>
                </ul>
            </div>
        </article>

        <article v-if="finalRoundWarnings.length" class="message is-warning is-small mt-3" dusk="final-round-warnings">
            <div class="message-body">
                <ul>
                    <li v-for="player in finalRoundWarnings" :key="`${player.squadIndex}-${player.refId}`"
                        :dusk="`final-round-finding-${player.squadIndex}-${player.refId}`">
                        Hold {{ player.squadIndex + 1 }}: {{ player.name }}
                        <template v-if="player.status === 'REQUIREMENT_NOT_MET'">har ikke stået på samme eller et lavere hold tidligere i sæsonen (§38 stk. 8).</template>
                        <template v-else>
                            — spilleberettigelse kan ikke verificeres fuldt ud (§38 stk. 8).
                            <ul v-if="finalRoundContextGuidance(player).length">
                                <li v-for="guidance in finalRoundContextGuidance(player)" :key="guidance">{{ guidance }}</li>
                            </ul>
                            <template v-if="player.uncountedRounds.length">
                                Tidligere opstillinger kan ikke sammenlignes. Tilknyt det aktuelle hold til den tilsvarende holdopstilling i hver af disse holdrunder via <strong>Rediger holdet → Tilknyt hold</strong>:
                                <ul>
                                    <li v-for="round in player.uncountedRounds" :key="round.teamRoundId">
                                        <router-link :to="{name: 'team-fight-edit', params: {clubhouseId: $route.params.clubhouseId, teamUUID: round.teamRoundId}}"
                                                     target="_blank" rel="noopener" :dusk="`final-round-correction-${round.teamRoundId}`">{{ round.name }}<template v-if="round.round !== null"> (runde {{ round.round }})</template></router-link>
                                    </li>
                                </ul>
                            </template>
                        </template>
                    </li>
                </ul>
                <p v-if="finalRoundWarnings.some(player => player.uncountedRounds.length)" class="mt-2">
                    <router-link :to="{name: 'help-guide', params: {slug: teamLinkGuide}}" target="_blank" dusk="final-round-team-link-guide">Sådan tilknytter du holdet i tidligere runder</router-link>
                </p>
                <p class="mt-2">Kontrollen bygger på tidligere officielle holdopstillinger i sæsonen. Advarslen blokerer ikke redigering eller gemning.</p>
            </div>
        </article>

        <article v-if="showPaperSquads && paperSquadMessages.length" class="message is-warning is-small mt-3" dusk="paper-squad-warnings">
            <div class="message-body">
                <ul>
                    <li v-for="message in paperSquadMessages" :key="message">{{ message }}</li>
                </ul>
                <p v-if="showsTeamLinkGuide" class="mt-2">
                    <router-link :to="{name: 'help-guide', params: {slug: teamLinkGuide}}" target="_blank" dusk="paper-squad-team-link-guide">Sådan tilknytter du holdet i tidligere runder</router-link>
                </p>
                <p v-if="paperSquadValidation && paperSquadValidation.players.length" class="mt-2">{{ paperSquadAggravation }}</p>
            </div>
        </article>

        <div v-if="hasErrors" class="mt-3">
            <b-collapse
                aria-id="contentIdForA11y1"
                class="panel is-danger"
                animation="slide"
                v-model="isOpen">
                <template #trigger>
                    <div
                        class="panel-heading"
                        role="button"
                        aria-controls="contentIdForA11y1"
                        :aria-expanded="isOpen">
                        <div class="is-flex is-justify-content-space-between">
                            <span>
                                <b-icon icon="alert-circle" size="is-small" class="mr-1"></b-icon>
                                <span>Detaljer om fejl</span>
                            </span>
                            <b-icon :icon="isOpen ? 'menu-up' : 'menu-down'"></b-icon>
                        </div>
                    </div>
                </template>
                <div class="panel-block">
                    <div class="content is-small">
                        <ul>
                            <li v-for="(error, index) in allErrors" :key="index" class="has-text-danger">
                                {{ error }}
                            </li>
                        </ul>
                    </div>
                </div>
            </b-collapse>
        </div>
    </div>
</template>
<script>
import {buildValidationErrors, invalidLevelTip} from './validation-status.js'
import {reserveFindingMessages} from './reserves.js'
import {PAPER_SQUAD_AGGRAVATION, TEAM_LINK_GUIDE, paperSquadMessages, showsTeamLinkGuide} from './paper-squads.js'

export default {
    name: 'ValidationStatus',
    props: {
        finalRoundEligibility: {
            type: Array,
            default: null
        },
        incompleteTeam: Boolean,
        invalidCategory: Boolean,
        invalidLevel: Boolean,
        hideIncompleteTeam: {
            type: Boolean,
            default: false
        },
        ignoreIncompleteTeam: {
            type: Boolean,
            default: false
        },
        basicSquads: {
            type: Array,
            default: () => []
        },
        invalidCategoryList: {
            type: Array,
            default: () => []
        },
        invalidLevelList: {
            type: Array,
            default: () => []
        },
        // §43 stk. 3 findings from validateReserves
        reserveFindings: {
            type: Array,
            default: () => []
        },
        // Show the §43 status, when the lineup has reserves
        showReserves: {
            type: Boolean,
            default: false
        },
        // §38 stk. 7 findings from validatePaperSquads; null until it has run
        paperSquadValidation: {
            type: Object,
            default: null
        },
        // Show the §38 stk. 7 status, when the lineup has a Paper Squad
        showPaperSquads: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            isOpen: false,
            paperSquadAggravation: PAPER_SQUAD_AGGRAVATION,
            teamLinkGuide: TEAM_LINK_GUIDE
        }
    },
    methods: {
        finalRoundContextGuidance(player) {
            const guidance = {
                MISSING_ROUND: 'Udfyld Runde nr. i holdrundens indstillinger.',
                UNKNOWN_TIER: 'Angiv holdets række under Rediger holdet. Er et hold tilknyttet, skal rækken rettes på sæsonens hold.',
                MISSING_TEAM: 'Tilknyt sæsonens hold via Rediger holdet → Tilknyt hold i denne holdrunde.',
                MISSING_SEASON: 'Holdrunden mangler sæson. Kontakt support for at få holdrundens sæson rettet; sæsonen kan ikke ændres i indstillingerne.',
                MISSING_GAME_DATE: 'Angiv datoen i holdrundens indstillinger, så tidligere holdrunder kan findes.'
            }
            return (player.incompleteReasons ?? []).map(reason => guidance[reason]).filter(Boolean)
        },
        statusClass(status) {
            return {
                'is-light': status === null,
                'is-danger': status === true,
                'is-success': status === false
            }
        },
        statusIcon(status) {
            if (status === null) return 'minus-circle-outline'
            return status ? 'alert-circle' : 'check-circle'
        },
        statusText(status) {
            if (status === null) return 'Afventer'
            return status ? 'Fejl' : 'OK'
        }
    },
    computed: {
        finalRoundWarnings() {
            return (this.finalRoundEligibility ?? []).filter(player =>
                ['REQUIREMENT_NOT_MET', 'CANNOT_VERIFY'].includes(player.status))
        },
        incompleteStatusClass() {
            if (this.ignoreIncompleteTeam) return 'is-warning'
            return this.statusClass(this.incompleteTeam)
        },
        incompleteStatusIcon() {
            if (this.ignoreIncompleteTeam) return 'cancel'
            return this.statusIcon(this.incompleteTeam)
        },
        incompleteStatusText() {
            if (this.ignoreIncompleteTeam) return 'Deaktiveret'
            return this.statusText(this.incompleteTeam)
        },
        hasErrors() {
            return this.incompleteTeam === true || this.invalidCategory === true || this.invalidLevel === true;
        },
        allErrors() {
            return buildValidationErrors(this)
        },
        reserveMessages() {
            return reserveFindingMessages(this.reserveFindings)
        },
        reserveStatusClass() {
            return this.reserveMessages.length ? 'is-warning' : 'is-success'
        },
        reserveStatusIcon() {
            return this.reserveMessages.length ? 'alert' : 'check-circle'
        },
        reserveStatusText() {
            return this.reserveMessages.length ? 'Advarsel' : 'OK'
        },
        reserveTip() {
            if (this.reserveMessages.length) return 'En reserve har flere point end spilleren, vedkommende erstatter. Holdet må ikke blive stærkere end planlagt (§43 stk. 3); vurder selv, om det er tilfældet.'
            return 'Ingen reserve har flere point end spilleren, vedkommende erstatter (§43 stk. 3).'
        },
        paperSquadMessages() {
            return paperSquadMessages(this.paperSquadValidation)
        },
        showsTeamLinkGuide() {
            return showsTeamLinkGuide(this.paperSquadValidation)
        },
        paperSquadStatusClass() {
            if (this.paperSquadValidation === null) return 'is-light'
            return this.paperSquadMessages.length ? 'is-warning' : 'is-success'
        },
        paperSquadStatusIcon() {
            if (this.paperSquadValidation === null) return 'minus-circle-outline'
            return this.paperSquadMessages.length ? 'alert' : 'check-circle'
        },
        paperSquadStatusText() {
            if (this.paperSquadValidation === null) return 'Afventer'
            return this.paperSquadMessages.length ? 'Advarsel' : 'OK'
        },
        paperSquadTip() {
            return 'På et papirhold må en spiller kun stå i kategorier, de har spillet i sæsonen, og skal have stået på samme eller et lavere hold før: mindst 1 gang i grundspillet fra runde 2 og mindst 2 gange i slutspillet (§38 stk. 7). Rangordenen på papirholdet og styrkeordenen mod højere hold kontrolleres ikke.'
        },
        incompleteTeamTip() {
            if (this.ignoreIncompleteTeam) return 'Tjekket er slået fra. Hold valideres uden krav om fuld besætning. Slå til igen via kontakten.'
            if (this.incompleteTeam === null) return 'Afventer validering'
            return this.incompleteTeam ? 'Der findes et eller flere hold der mangler spillere. Dette skal rettes før øvrige tjek kan gennemføres.' : 'Alle hold er fuldt besat.'
        },
        invalidCategoryTip() {
            if (this.invalidCategory === null) return 'Deaktiveret indtil alle hold er fuldt besat'
            return this.invalidCategory ? 'En eller flere spillere spiller for højt i deres kategori (Bryder § 38. stk. 2 og 3).' : 'Kategoriordningen overholdes (§ 38. stk. 2 og 3).'
        },
        invalidLevelTip() {
            return invalidLevelTip(this.invalidLevel)
        }
    }
}
</script>

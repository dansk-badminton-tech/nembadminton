<template>
    <div>
        <div class="field is-grouped is-grouped-multiline">
            <div class="control" dusk="qualification-validation-control">
                <b-tooltip type="is-info" label="Kontrollerer tidligere officielle holdopstillinger efter rundenummer, ikke kalenderårsalternativet (§27 stk. 5)." multilined>
                    <div class="tags has-addons">
                        <span class="tag is-dark is-medium">Kvalifikation (§27 stk. 5)</span>
                        <span dusk="validation-qualification" class="tag is-medium" :class="statusClass" aria-live="polite">
                            <b-icon :icon="statusIcon" size="is-small" class="mr-1" />{{ statusText }}
                        </span>
                        <span class="tag is-medium is-white">
                            <b-button size="is-small" icon-left="refresh" aria-label="Kontrollér kvalifikationskrav igen"
                                      title="Kontrollér kvalifikationskrav igen" dusk="revalidate-qualification"
                                      :disabled="!validation.enabled" :loading="validation.status === 'pending'"
                                      @click="$emit('revalidate')" />
                            <b-switch size="is-small" class="ml-2" dusk="toggle-qualification-check"
                                      aria-label="Kontrollér kvalifikationskrav" :model-value="validation.enabled"
                                      @update:modelValue="$emit('enabled-change', $event)" />
                        </span>
                    </div>
                </b-tooltip>
            </div>
        </div>
        <article v-if="warnings.length" class="message is-warning is-small mt-3" dusk="qualification-warnings">
            <div class="message-body">
                <ul>
                    <li v-for="player in warnings" :key="`${player.squadIndex}-${player.refId}`">
                        Hold {{ player.squadIndex + 1 }}: {{ player.name }}
                        <template v-if="player.status === 'REQUIREMENT_NOT_MET'">opfylder ikke de kontrollerede rundenummerkrav (§27 stk. 5); Nembadminton vurderer ikke kalenderårsalternativet.</template>
                        <template v-else>
                            — kvalifikationskravet kan ikke verificeres fuldt ud (§27 stk. 5); Nembadminton vurderer ikke kalenderårsalternativet.
                            <ul>
                                <li v-for="reason in player.incompleteReasons" :key="reason">{{ guidance[reason] }}</li>
                                <li v-for="round in player.uncountedRounds" :key="round.teamRoundId">
                                    <router-link :to="{name: 'team-fight-edit', params: {clubhouseId: $route.params.clubhouseId, teamUUID: round.teamRoundId}}"
                                                 target="_blank" rel="noopener" :dusk="`qualification-correction-${round.teamRoundId}`">{{ round.name }} (runde {{ round.round }})</router-link>
                                </li>
                            </ul>
                        </template>
                    </li>
                </ul>
                <router-link :to="{name: 'help-guide', params: {slug: 'kontroller-spillerkrav-i-kvalifikation'}}" target="_blank" rel="noopener">Læs mere</router-link>
            </div>
        </article>
    </div>
</template>

<script>
export default {
    props: {
        validation: {type: Object, required: true}
    },
    emits: ['revalidate', 'enabled-change'],
    data() {
        return {
            guidance: {
                MISSING_ROUND: 'Udfyld Runde nr. i holdrundens indstillinger.',
                UNKNOWN_TIER: 'Angiv holdets oprindelige række på sæsonens hold.',
                MISSING_TEAM: 'Tilknyt sæsonens hold via Rediger holdet → Tilknyt hold.',
                MISSING_SEASON: 'Kontakt support for at få holdrundens sæson rettet.',
                INCOMPARABLE_HISTORY: 'Tilknyt det aktuelle hold til den tilsvarende holdopstilling i disse tidligere holdrunder via Rediger holdet → Tilknyt hold.'
            }
        }
    },
    computed: {
        warnings() {
            if (!this.validation.enabled || this.validation.status !== 'complete') return []
            return (this.validation.findings ?? []).filter(player => ['REQUIREMENT_NOT_MET', 'CANNOT_VERIFY'].includes(player.status))
        },
        statusText() {
            if (!this.validation.enabled) return 'Deaktiveret'
            if (this.validation.status === 'failed') return 'Fejlet'
            if (this.validation.status !== 'complete') return 'Afventer'
            return this.warnings.length ? 'Advarsel' : 'OK'
        },
        statusClass() {
            if (!this.validation.enabled || this.validation.status === 'failed') return 'is-warning'
            if (this.validation.status !== 'complete') return 'is-light'
            return this.warnings.length ? 'is-warning' : 'is-success'
        },
        statusIcon() {
            if (!this.validation.enabled) return 'cancel'
            if (this.validation.status === 'failed') return 'alert'
            if (this.validation.status !== 'complete') return 'minus-circle-outline'
            return this.warnings.length ? 'alert' : 'check-circle'
        }
    }
}
</script>

<template>
    <form @submit.prevent="save">
        <div class="modal-card" style="width: auto">
            <header class="modal-card-head">
                <p class="modal-card-title">Indstillinger for holdrunde</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <div class="columns">
                    <div class="column">
                        <b-field label="Runde nr.">
                            <b-numberinput
                                dusk="team-fight-round-input"
                                v-model="round"
                                :min="1"
                                controls-position="compact"
                                expanded
                            >
                            </b-numberinput>
                        </b-field>
                    </div>
                    <div class="column">
                        <b-field>
                            <template v-slot:label>
                                Runde spilledato
                                <b-tooltip type="is-light" position="is-left" multilined>
                                    <template v-slot:content>
                                        <div class="has-text-centered">
                                            <img src="@/views/team-fight/tooltip-team-round-date.png" style="width: 300px;">
                                            <p>Holdrunde dato kan findes under selve holdkampen på badmintonplayer (oftes spille dato'en)</p>
                                        </div>
                                    </template>
                                    <b-icon icon="help-circle-outline" class="ml-2" />
                                </b-tooltip>
                            </template>
                            <b-datepicker
                                append-to-body
                                v-model="gameDate"
                                icon="calendar"
                                locale="da-DK"
                                placeholder="Klik for at vælge dato..."
                                :first-day-of-week="1"
                                trap-focus>
                            </b-datepicker>
                        </b-field>
                    </div>
                </div>
                <div class="columns">
                    <div class="column">
                        <b-field label="Rangliste">
                            <RankingVersionSelect @focus="oldVersion = version"
                                                  v-model="version" expanded></RankingVersionSelect>
                        </b-field>
                    </div>
                    <div class="column">
                        <b-field label="Navn (Valgfrit)">
                            <b-input v-model="name"></b-input>
                        </b-field>
                    </div>
                </div>
            </section>
            <footer class="modal-card-foot">
                <b-button
                    :loading="saving"
                    native-type="submit"
                    type="is-primary"
                    label="Gem"/>
                <b-button
                    @click="$emit('close')"
                    label="Luk"/>
            </footer>
        </div>
    </form>
</template>

<script>
import gql from 'graphql-tag'
import RankingVersionSelect from "../common/RankingVersionSelect.vue";

export default {
    name: "TeamRoundSettingsModal",
    components: {RankingVersionSelect},
    props: {
        teamRound: Object
    },
    data() {
        return {
            saving: false,
            name: this.teamRound.name,
            round: this.teamRound.round,
            gameDate: new Date(this.teamRound.gameDate),
            version: this.teamRound.version,
            oldVersion: this.teamRound.version
        }
    },
    methods: {
        confirmChangeOfRankingList(newVersion) {
            this.$buefy.dialog.confirm(
                {
                    message: 'Du er ved at skifte rangliste. Alle spillere på holdene vil bliver opdateret til den nye rangliste. Hold med en specifik rangliste vil ikke blive opdateret',
                    confirmText: 'Skift og opdater spillere',
                    onConfirm: () => {

                    },
                    onCancel: () => {
                        this.version = this.oldVersion
                    }
                })
        },
        updateToRankingList() {
            this.saving = true;
            this.$apollo.mutate({
                mutation: gql`
                    mutation updatePointsTeamRound($id: ID!, $version: String!){
                        updatePointsTeamRound(id: $id, version: $version){
                            id
                            version
                            squads{
                                id
                                categories{
                                    id
                                    players{
                                        id
                                        points{
                                            id
                                            category
                                            points
                                            position
                                            version
                                        }
                                    }
                                }
                            }
                        }
                    }
                `,
                variables: {
                    id: this.teamRound.id,
                    version: this.version
                }
            }).then(() => {
                this.$buefy.snackbar.open({
                    duration: 4000,
                    type: 'is-success',
                    message: `Ranglisten er opdateret`
                })
                this.oldVersion = this.version;
            }).catch((error) => {
                this.$buefy.snackbar.open({
                    duration: 4000,
                    type: 'is-danger',
                    message: `Kunne ikke opdater rangliste :(`
                })
                this.version = this.oldVersion;
            }).finally(() => {
                this.saving = false;
            })
        },
        save() {
            this.saving = true;
            this.$apollo.mutate({
                mutation: gql`
                    mutation updateTeamRound($input: UpdateTeamRoundInput!){
                      updateTeamRound(input: $input){
                        id
                        name
                        gameDate
                        version
                        round
                      }
                    }
                `,
                variables: {
                    input: {
                        id: this.teamRound.id,
                        name: this.name,
                        version: this.version,
                        gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
                        round: this.round
                    }
                }
            })
            .then(() => {
                this.$buefy.snackbar.open({
                    duration: 2000,
                    type: 'is-success',
                    message: `Holdrunden er gemt`
                })
                this.$emit('save')
                this.$emit('close')
            })
            .catch((error) => {
                this.$buefy.snackbar.open({
                    duration: 2000,
                    type: 'is-danger',
                    message: `Kunne ikke gemme holdrunden :(`
                })
            })
            .finally(() => {
                this.saving = false;
            })
        }
    }
}
</script>

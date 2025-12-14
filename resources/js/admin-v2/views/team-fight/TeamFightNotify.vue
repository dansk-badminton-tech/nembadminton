<script>

import {defineComponent} from "vue";
import HeroBar from "@/components/HeroBar.vue";
import TitleBar from "@/components/TitleBar.vue";
import {uniqBy} from "lodash/array.js";
import TeamQuery from "../../../queries/team.graphql";
import gql from "graphql-tag";
import {extractErrorMessages} from "@/helpers.js";

export default {
    components: {TitleBar, HeroBar},
    inject: ['clubhouseId'],
    props: {
        teamFightId: String
    },
    computed: {
        squadMembers() {
            const allMembers = uniqBy(this.team.squads
                .flatMap(squad => squad.categories)
                .flatMap(category => category.players), 'refId');
            return allMembers
        },
        squadMembersWithUserCount(){
            return this.squadMembers.filter(player => player.user !== null).length;
        },
        squadMembersWithoutAttachedUser(){
            return this.squadMembers.filter(player => player.user === null);
        },
        // Added: Disable publish when no recipients
        cannotPublish() {
            return this.squadMembersWithUserCount === 0 || this.loading;
        }
    },
    data() {
        return {
            titleStack: ['Admin', 'Notifikation'],
            loading: false,
            message: '',
            team: {
                squads: []
            }
        }
    },
    apollo: {
        team: {
            query: TeamQuery,
            variables: function () {
                return {
                    id: this.teamFightId
                }
            }
        },
    },
    methods: {
        publish(){
            // Added: loading and guard
            if (this.cannotPublish) return;
            this.loading = true;
            this.$apollo.mutate({
                mutation: gql`
                    mutation publishTeam($id: ID!, $message: String){
                        publishTeam(id: $id, message: $message){
                            id
                            message
                        }
                    }
                `,
                variables: {
                    id: this.teamFightId,
                    message: this.message
                }
            }).then(({data}) => {
                this.$buefy.snackbar.open({
                    duration: 4000,
                    type: 'is-success',
                    message: 'Holdet er nu publiceret'
                })
                // Close modal and inform parent
                this.$emit('published', data?.publishTeam);
                this.$emit('close');
            }).catch(({graphQLErrors}) => {
                const errorMessages = extractErrorMessages(graphQLErrors);
                this.$buefy.snackbar.open({
                    duration: 5000,
                    type: 'is-danger',
                    message: 'Kunne ikke publicere holdet: ' + errorMessages.join(', ')
                })
            }).finally(() => {
                this.loading = false;
            })
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Dashboard
        </hero-bar>
        <div class="section">
            <div class="columns">
                <div class="column is-half">
                    <form @submit.prevent="publish">
                            <b-field label="Besked til spillerne (valgfrit)">
                                <b-input
                                    type="textarea"
                                    v-model="message"
                                    placeholder="Skriv en besked til spillerne"
                                    rows="4"
                                    expanded
                                    maxlength="500"
                                    has-counter />
                            </b-field>

                            <b-notification type="is-info" aria-close-label="Luk" :closable="false" class="mb-3">
                                <strong>{{ squadMembersWithUserCount }}</strong> ud af <strong>{{ squadMembers.length }}</strong> spillere vil modtage en mail.
                            </b-notification>

                            <b-notification v-if="squadMembersWithUserCount === 0" type="is-warning" aria-close-label="Luk" :closable="false" class="mb-3">
                                Ingen spillere vil modtage mailen, fordi ingen spillere er tilknyttet en bruger endnu.
                            </b-notification>

                            <strong>Spillere tilknytning til klubben</strong>
                            <p class="is-size-7 has-text-grey">Se hvilke spiller som mangler at tilknytte sig klubben.</p>
                            <hr/>
                            <b-table
                                :data="squadMembers"
                                per-page="10"
                                paginated
                                :mobile-cards="true"
                                :row-class="(row, index) => (row.user !== null ? 'has-background-success-light' : '')"
                            >
                                <b-table-column field="name" label="First Name" v-slot="props">
                                    {{ props.row.name }}
                                </b-table-column>
                                <b-table-column label="Har email?" v-slot="props">
                                    <b-icon
                                        :type="props.row.user ? 'is-success' : 'is-danger'"
                                        :icon="props.row.user ? 'check' : 'close'"
                                    ></b-icon>
                                </b-table-column>
                                <template v-slot:empty>
                                    <section class="section has-text-centered">
                                        <p>Alle spillere er tilknyttet — godt gået!</p>
                                    </section>
                                </template>
                            </b-table>
                            <div class="buttons">
                                <b-tooltip :label="squadMembersWithUserCount === 0 ? 'Ingen modtagere' : null" :active="squadMembersWithUserCount === 0">
                                    <b-button
                                        :loading="loading"
                                        :disabled="cannotPublish"
                                        native-type="submit"
                                        type="is-info"
                                        label="Send notification"/>
                                </b-tooltip>
                            </div>
                        </form>
                </div>
                <div class="column is-half"></div>
            </div>

        </div>
    </div>
</template>

<style scoped>

</style>

<template>
    <form @submit.prevent="onSubmit">
        <div class="modal-card" style="max-width: 540px;">
            <header class="modal-card-head">
                <p class="modal-card-title">{{ isEdit ? 'Rediger hold' : 'Opret hold' }}</p>
            </header>
            <section class="modal-card-body">
                <b-field label="Navn" :type="nameError ? 'is-danger' : ''" :message="nameError">
                    <b-input
                        v-model="form.name"
                        dusk="team-name-input"
                        placeholder="Fx 1. holdet"
                        autofocus
                        required>
                    </b-input>
                </b-field>

                <b-field label="Sæson" :type="seasonError ? 'is-danger' : ''" :message="seasonError">
                    <b-select
                        v-model="form.seasonId"
                        dusk="team-season-select"
                        :loading="$apollo.queries.seasons.loading"
                        placeholder="Vælg sæson"
                        expanded
                        required>
                        <option
                            v-for="season in seasons"
                            :key="season.id"
                            :value="season.id">
                            {{ season.seasonName }}
                        </option>
                    </b-select>
                </b-field>

                <b-field label="Niveau" message="Vælg et eksisterende eller skriv et nyt.">
                    <b-autocomplete
                        v-model="tierInput"
                        dusk="team-tier-input"
                        :data="filteredTierOptions"
                        :loading="$apollo.queries.tournamentTiers.loading"
                        placeholder="Fx 1. division"
                        field="tierName"
                        clearable
                        keep-first
                        open-on-focus
                        @select="onTierSelect">
                    </b-autocomplete>
                </b-field>

                <b-field label="Gruppe (valgfri)">
                    <b-input
                        v-model="form.groupName"
                        dusk="team-group-input"
                        placeholder="Fx Pulje 1">
                    </b-input>
                </b-field>
            </section>
            <footer class="modal-card-foot">
                <b-button @click="$emit('cancel')">Annuller</b-button>
                <b-button
                    dusk="team-submit"
                    type="is-link"
                    native-type="submit"
                    :loading="submitting"
                    :disabled="!form.name">
                    {{ isEdit ? 'Gem' : 'Opret' }}
                </b-button>
            </footer>
        </div>
    </form>
</template>

<script>
import gql from "graphql-tag";
import TournamentTiersQuery from "../../../queries/tournamentTiers.graphql";
import SeasonsQuery from "../../../queries/seasons.graphql";
import { getCurrentSeason } from "../../helpers";

export default {
    name: "TeamForm",
    props: {
        team: {
            type: Object,
            default: null
        },
        clubhouseId: {
            type: [String, Number],
            required: true
        }
    },
    data() {
        return {
            submitting: false,
            tournamentTiers: [],
            seasons: [],
            tierInput: this.team?.tier?.tierName || this.team?.customTierName || '',
            form: {
                name: this.team?.name || '',
                seasonId: this.team?.season?.id ?? getCurrentSeason(),
                tierId: this.team?.tier?.id || null,
                customTierName: this.team?.customTierName || null,
                groupName: this.team?.groupName || null
            },
            nameError: '',
            seasonError: ''
        }
    },
    apollo: {
        tournamentTiers: {
            query: TournamentTiersQuery
        },
        seasons: {
            query: SeasonsQuery
        }
    },
    computed: {
        isEdit() {
            return Boolean(this.team?.id);
        },
        filteredTierOptions() {
            const query = (this.tierInput || '').toLowerCase();
            if (query === '') {
                return this.tournamentTiers;
            }
            return this.tournamentTiers.filter(t => t.tierName.toLowerCase().includes(query));
        }
    },
    watch: {
        tierInput(value) {
            const match = this.tournamentTiers.find(t => t.tierName === value);
            if (match) {
                this.form.tierId = match.id;
                this.form.customTierName = null;
            } else {
                this.form.tierId = null;
                this.form.customTierName = value && value.trim() !== '' ? value.trim() : null;
            }
        }
    },
    methods: {
        onTierSelect(option) {
            if (option && typeof option.tierName === 'string') {
                this.tierInput = option.tierName;
            }
        },
        onSubmit() {
            if (!this.form.name || this.form.name.trim() === '') {
                this.nameError = 'Navn er påkrævet.';
                return;
            }
            this.nameError = '';
            if (!this.form.seasonId) {
                this.seasonError = 'Sæson er påkrævet.';
                return;
            }
            this.seasonError = '';
            this.submitting = true;
            const mutationPromise = this.isEdit
                ? this.updateTeam()
                : this.createTeam();

            mutationPromise
                .then(() => this.$emit('saved'))
                .catch((err) => {
                    this.$buefy.toast.open({
                        message: this.isEdit ? 'Kunne ikke gemme holdet' : 'Kunne ikke oprette holdet',
                        type: 'is-danger',
                        duration: 5000
                    });
                    console.error(err);
                })
                .finally(() => {
                    this.submitting = false;
                });
        },
        createTeam() {
            return this.$apollo.mutate({
                mutation: gql`
                    mutation($input: CreateTeamInput!) {
                        createTeam(input: $input) {
                            id
                            name
                            groupName
                            customTierName
                            season { id seasonName }
                            tier { id tierName }
                        }
                    }
                `,
                variables: {
                    input: {
                        name: this.form.name.trim(),
                        seasonId: this.form.seasonId,
                        tierId: this.form.tierId,
                        customTierName: this.form.customTierName,
                        groupName: this.form.groupName
                    }
                }
            });
        },
        updateTeam() {
            return this.$apollo.mutate({
                mutation: gql`
                    mutation($input: UpdateTeamInput!) {
                        updateTeam(input: $input) {
                            id
                            name
                            groupName
                            customTierName
                            season { id seasonName }
                            tier { id tierName }
                        }
                    }
                `,
                variables: {
                    input: {
                        id: this.team.id,
                        name: this.form.name.trim(),
                        seasonId: this.form.seasonId,
                        tierId: this.form.tierId,
                        customTierName: this.form.customTierName,
                        groupName: this.form.groupName
                    }
                }
            });
        }
    }
}
</script>

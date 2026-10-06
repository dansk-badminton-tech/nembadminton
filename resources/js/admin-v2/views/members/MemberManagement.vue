<script>
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import CardComponent from "@/components/CardComponent.vue";
import gql from "graphql-tag";
import {debounce} from "@/helpers.js";

const GENDERS = ['MEN', 'WOMEN'];

export default {
    name: "MemberManagement",
    components: {CardComponent, HeroBar, TitleBar},
    inject: ['clubhouseId'],
    data() {
        const query = this.$route.query;
        const searchName = typeof query.q === 'string' ? query.q : '';
        const hidden = typeof query.skjul === 'string' ? query.skjul.split(',') : [];

        return {
            titleStack: ['Admin', 'Spillere'],
            searchName,
            appliedSearchName: searchName,
            selectedGender: GENDERS.includes(query.gender) ? query.gender : null,
            showInactive: !hidden.includes('inaktive'),
            showPermanentCancellations: !hidden.includes('afbud'),
            currentPage: 1,
            perPage: 20,
            isTogglingInactive: false,
            isResettingOverride: false,
            isTogglingPlayable: false,
            members: null
        }
    },
    computed: {
        genderOptions() {
            return [
                {value: null, label: 'Alle'},
                {value: 'MEN', label: 'Herre'},
                {value: 'WOMEN', label: 'Dame'}
            ]
        },
        hiddenGroups() {
            const hidden = [];

            if (!this.showInactive) {
                hidden.push('inaktive');
            }

            if (!this.showPermanentCancellations) {
                hidden.push('afbud');
            }

            return hidden;
        },
        hasNarrowingFilters() {
            return this.hiddenGroups.length > 0 || this.selectedGender !== null || this.appliedSearchName.trim() !== '';
        },
        emptyMessage() {
            const searchName = this.appliedSearchName.trim();

            return searchName !== '' ? `Ingen spillere matcher "${searchName}"` : 'Ingen spillere';
        },
        emptyHints() {
            const hints = [];
            const hiddenLabels = {inaktive: 'inaktive spillere', afbud: 'spillere med permanent afbud'};

            if (this.hiddenGroups.length > 0) {
                const hidden = this.hiddenGroups.map(group => hiddenLabels[group]).join(' og ');
                hints.push(`${hidden.charAt(0).toUpperCase()}${hidden.slice(1)} er skjult.`);
            }

            if (this.selectedGender) {
                hints.push(`Kun ${this.getGenderLabel(this.selectedGender).toLowerCase()}spillere vises.`);
            }

            return hints;
        },
        membersList() {
            return this.members?.membersSearch?.data ?? [];
        },
        paginatorInfo() {
            return this.members?.membersSearch?.paginatorInfo ?? {total: 0, count: 0, currentPage: 1};
        }
    },
    apollo: {
        members: {
            query: gql`
                query membersSearch($clubhouse: Int!, $name: String, $gender: [Gender!], $inactive: Boolean, $excludePermanentCancellations: Boolean, $page: Int!, $first: Int!) {
                    membersSearch(
                        clubhouse: $clubhouse
                        name: $name
                        gender: $gender
                        inactive: $inactive
                        excludePermanentCancellations: $excludePermanentCancellations
                        page: $page
                        first: $first
                    ) {
                        data {
                            id
                            refId
                            name
                            gender
                            vintage
                            birthday
                            playable
                            inactive
                            overrideInactive
                        }
                        paginatorInfo {
                            total
                            count
                            currentPage
                            lastPage
                        }
                    }
                }
            `,
            update: data => data,
            variables() {
                const vars = {
                    clubhouse: this.clubhouseId,
                    page: this.currentPage,
                    first: this.perPage
                };

                if (this.appliedSearchName.trim() !== '') {
                    vars.name = `%${this.appliedSearchName.trim()}%`;
                }

                if (this.selectedGender) {
                    vars.gender = [this.selectedGender];
                }

                if (!this.showInactive) {
                    vars.inactive = false;
                }

                if (!this.showPermanentCancellations) {
                    vars.excludePermanentCancellations = true;
                }

                return vars;
            },
            skip() {
                return !this.clubhouseId
            },
            fetchPolicy: "network-only",
            error(error) {
                this.$buefy.snackbar.open({
                    message: 'Kunne ikke hente spillere',
                    type: 'is-danger',
                    duration: 5000
                });
            }
        }
    },
    watch: {
        searchName() {
            this.applySearchName();
        },
        showInactive() {
            this.filtersChanged();
        },
        showPermanentCancellations() {
            this.filtersChanged();
        },
        selectedGender() {
            this.filtersChanged();
        }
    },
    methods: {
        applySearchName: debounce(function () {
            this.appliedSearchName = this.searchName;
            this.filtersChanged();
        }, 300),
        filtersChanged() {
            this.currentPage = 1;

            // Keep the filters in the URL, so a refresh or a shared link shows the same list.
            const {q, skjul, gender, ...query} = this.$route.query;
            const searchName = this.appliedSearchName.trim();

            if (searchName !== '') {
                query.q = searchName;
            }

            if (this.hiddenGroups.length > 0) {
                query.skjul = this.hiddenGroups.join(',');
            }

            if (this.selectedGender) {
                query.gender = this.selectedGender;
            }

            this.$router.replace({query});
        },
        showAllMembers() {
            this.searchName = '';
            this.appliedSearchName = '';
            this.selectedGender = null;
            this.showInactive = true;
            this.showPermanentCancellations = true;
        },
        onPageChange(page) {
            this.currentPage = page;
        },
        toggleInactiveStatus(member) {
            this.isTogglingInactive = true;
            const targetMode = member.inactive ? 'FORCE_ACTIVE' : 'FORCE_INACTIVE';

            this.$apollo.mutate({
                mutation: gql`
                    mutation setMemberInactiveOverride($id: ID!, $mode: InactiveOverrideMode!) {
                        setMemberInactiveOverride(id: $id, mode: $mode) {
                            id
                            inactive
                            overrideInactive
                        }
                    }
                `,
                variables: {
                    id: member.id,
                    mode: targetMode
                }
            }).then(() => {
                this.$buefy.snackbar.open({
                    message: targetMode === 'FORCE_INACTIVE' ? 'Spiller markeret som inaktiv' : 'Spiller markeret som aktiv',
                    type: 'is-success',
                    duration: 3000
                });
                this.$apollo.queries.members.refetch();
            }).catch(() => {
                this.$buefy.snackbar.open({
                    message: 'Kunne ikke opdatere spiller status',
                    type: 'is-danger',
                    duration: 5000
                });
            }).finally(() => {
                this.isTogglingInactive = false;
            });
        },
        resetInactiveOverride(member) {
            this.isResettingOverride = true;

            this.$apollo.mutate({
                mutation: gql`
                    mutation setMemberInactiveOverride($id: ID!, $mode: InactiveOverrideMode!) {
                        setMemberInactiveOverride(id: $id, mode: $mode) {
                            id
                            inactive
                            overrideInactive
                        }
                    }
                `,
                variables: {
                    id: member.id,
                    mode: 'AUTO'
                }
            }).then(() => {
                this.$buefy.snackbar.open({
                    message: 'Status nulstillet til automatisk',
                    type: 'is-success',
                    duration: 3000
                });
                this.$apollo.queries.members.refetch();
            }).catch(() => {
                this.$buefy.snackbar.open({
                    message: 'Kunne ikke nulstille spiller status',
                    type: 'is-danger',
                    duration: 5000
                });
            }).finally(() => {
                this.isResettingOverride = false;
            });
        },
        togglePlayableStatus(member) {
            this.isTogglingPlayable = true;
            const newPlayableStatus = !member.playable;

            this.$apollo.mutate({
                mutation: gql`
                    mutation updateMember($input: CreateMemberInput!) {
                        updateMember(input: $input) {
                            id
                            playable
                        }
                    }
                `,
                variables: {
                    input: {
                        id: member.id,
                        playable: newPlayableStatus
                    }
                }
            }).then(() => {
                this.$buefy.snackbar.open({
                    message: newPlayableStatus ? 'Permanent afbud annulleret' : 'Permanent afbud registreret',
                    type: 'is-success',
                    duration: 3000
                });
                this.$apollo.queries.members.refetch();
            }).catch(() => {
                this.$buefy.snackbar.open({
                    message: 'Kunne ikke opdatere spiller status',
                    type: 'is-danger',
                    duration: 5000
                });
            }).finally(() => {
                this.isTogglingPlayable = false;
            });
        },
        getGenderLabel(gender) {
            return gender === 'MEN' ? 'Herre' : 'Dame';
        },
        getStatusClass(member) {
            if (member.inactive) return 'has-text-danger';
            if (!member.playable) return 'has-text-warning';
            return 'has-text-success';
        },
        getStatusTagType(member) {
            if (member.inactive) return 'is-danger';
            if (!member.playable) return 'is-warning';
            return 'is-success';
        },
        getStatusLabel(member) {
            if (member.inactive) return 'Inaktiv';
            if (!member.playable) return 'Permanent afbud';
            return 'Aktiv';
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Spillere
        </hero-bar>
        <section class="section is-main-section">
            <b-message type="is-info" has-icon dusk="info-message">
                <p class="mb-2"><strong>Om spillere:</strong></p>
                <p class="mb-2">Spillere er badmintonspillere importeret fra badmintonplayer.dk API. Systemet importerer automatisk alle spillere der har spillet i klubben, inklusiv spillere der er stoppet.</p>
                <p class="mb-2"><strong>Forskel på "Inaktiv" og "Permanent afbud":</strong></p>
                <ul class="ml-4">
                    <li><strong>Inaktiv:</strong> Spilleren har ikke spillet 4 kampe inden for en kategori, de sidste 12 måneder. Denne status er styret af Badmintonplayer, men kan tilsidesættes manuelt.</li>
                    <li><strong>Permanent afbud:</strong> Spilleren er skadet eller væk i en længere periode, men er stadig aktiv medlem. Spilleren kan ikke vælges i nogen holdrunde, før det permanente afbud annulleres.</li>
                </ul>
            </b-message>

            <card-component
                title="Spillere i klubhuset"
                icon="account-multiple"
                dusk="member-management-card"
            >
                <template v-slot:default>
                    <b-field grouped group-multiline>
                        <b-field label="Søg på navn" expanded>
                            <b-input
                                v-model="searchName"
                                placeholder="Indtast navn..."
                                icon="magnify"
                                dusk="search-name-input"
                            ></b-input>
                        </b-field>
                        <b-field label="Køn">
                            <b-select v-model="selectedGender" dusk="gender-select">
                                <option
                                    v-for="option in genderOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </b-select>
                        </b-field>
                        <b-field label="Vis" grouped>
                            <b-switch v-model="showInactive" dusk="show-inactive-switch">Inaktive</b-switch>
                            <b-switch v-model="showPermanentCancellations" dusk="show-permanent-cancellations-switch">Permanent afbud</b-switch>
                        </b-field>
                    </b-field>

                    <b-table
                        :data="membersList"
                        :loading="$apollo.queries.members.loading || isTogglingInactive || isTogglingPlayable || isResettingOverride"
                        :paginated="true"
                        :backend-pagination="true"
                        :total="paginatorInfo.total"
                        :per-page="perPage"
                        v-model:current-page="currentPage"
                        @page-change="onPageChange"
                        :pagination-rounded="true"
                        :hoverable="true"
                        :striped="true"
                        dusk="members-table"
                    >
                        <b-table-column field="name" label="Navn" sortable v-slot="props">
                            <span :class="{'has-text-grey-light': props.row.inactive}">
                                {{ props.row.name }}
                            </span>
                        </b-table-column>

                        <b-table-column field="refId" label="BadmintonPlayer ID" v-slot="props">
                            <span :class="{'has-text-grey-light': props.row.inactive}">
                                {{ props.row.refId }}
                            </span>
                        </b-table-column>

                        <b-table-column field="gender" label="Køn" v-slot="props">
                            <span :class="{'has-text-grey-light': props.row.inactive}">
                                {{ getGenderLabel(props.row.gender) }}
                            </span>
                        </b-table-column>

                        <b-table-column field="vintage" label="Årgang" v-slot="props">
                            <span :class="{'has-text-grey-light': props.row.inactive}">
                                {{ props.row.vintage }}
                            </span>
                        </b-table-column>

                        <b-table-column field="inactive" label="Status" v-slot="props">
                            <b-tag :type="getStatusTagType(props.row)">
                                {{ getStatusLabel(props.row) }}
                            </b-tag>
                            <b-tag
                                v-if="props.row.overrideInactive && props.row.overrideInactive !== 'AUTO'"
                                type="is-info"
                                class="ml-1"
                                :dusk="`override-badge-${props.row.id}`"
                                title="Manuelt tilsidesat af klubben"
                            >
                                Tilsidesat
                            </b-tag>
                        </b-table-column>

                        <b-table-column label="Handlinger" v-slot="props">
                            <b-button
                                v-if="!props.row.inactive"
                                size="is-small"
                                :type="props.row.playable ? 'is-warning' : 'is-success'"
                                :icon-left="props.row.playable ? 'account-clock' : 'account-check'"
                                @click="togglePlayableStatus(props.row)"
                                :dusk="`toggle-playable-${props.row.id}`"
                                class="mr-1"
                            >
                                {{ props.row.playable ? 'Lav afbud permanent' : 'Annuller permanent afbud' }}
                            </b-button>
                            <b-button
                                size="is-small"
                                :type="props.row.inactive ? 'is-success' : 'is-danger'"
                                :icon-left="props.row.inactive ? 'account-check' : 'account-off'"
                                @click="toggleInactiveStatus(props.row)"
                                :dusk="`toggle-inactive-${props.row.id}`"
                            >
                                {{ props.row.inactive ? 'Marker som aktiv' : 'Marker som inaktiv' }}
                            </b-button>
                            <b-button
                                v-if="props.row.overrideInactive && props.row.overrideInactive !== 'AUTO'"
                                size="is-small"
                                type="is-light"
                                icon-left="refresh"
                                :loading="isResettingOverride"
                                @click="resetInactiveOverride(props.row)"
                                :dusk="`reset-override-${props.row.id}`"
                                class="ml-1"
                            >
                                Nulstil til automatisk
                            </b-button>
                        </b-table-column>

                        <template v-slot:empty>
                            <div class="has-text-centered" dusk="members-empty">
                                <p>{{ emptyMessage }}</p>
                                <p v-for="hint in emptyHints" :key="hint" class="is-size-7 has-text-grey">
                                    {{ hint }}
                                </p>
                                <b-button
                                    v-if="hasNarrowingFilters"
                                    type="is-text"
                                    class="mt-2"
                                    @click="showAllMembers"
                                    dusk="show-all-members"
                                >
                                    Vis alle spillere
                                </b-button>
                            </div>
                        </template>
                    </b-table>
                </template>
            </card-component>
        </section>
    </div>
</template>

<style scoped>
.has-text-grey-light {
    opacity: 0.6;
}
</style>

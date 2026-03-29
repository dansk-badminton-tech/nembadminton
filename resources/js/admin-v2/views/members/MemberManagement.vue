<script>
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import CardComponent from "@/components/CardComponent.vue";
import gql from "graphql-tag";

export default {
    name: "MemberManagement",
    components: {CardComponent, HeroBar, TitleBar},
    inject: ['clubhouseId'],
    data() {
        return {
            titleStack: ['Admin', 'Spillere'],
            searchName: '',
            selectedGender: null,
            showInactive: false,
            currentPage: 1,
            perPage: 20,
            isTogglingInactive: false,
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
                query membersSearch($clubhouse: Int!, $name: String, $gender: [Gender!], $inactive: Boolean, $page: Int!, $first: Int!) {
                    membersSearch(
                        clubhouse: $clubhouse
                        name: $name
                        gender: $gender
                        inactive: $inactive
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

                if (this.searchName && this.searchName.trim() !== '') {
                    vars.name = `%${this.searchName}%`;
                }

                if (this.selectedGender) {
                    vars.gender = [this.selectedGender];
                }

                if (!this.showInactive) {
                    vars.inactive = false;
                }

                return vars;
            },
            skip() {
                return !this.clubhouseId
            },
            fetchPolicy: "network-only"
        }
    },
    methods: {
        search() {
            this.currentPage = 1;
            this.$apollo.queries.members.refetch();
        },
        onPageChange(page) {
            this.currentPage = page;
        },
        toggleInactiveStatus(member) {
            this.isTogglingInactive = true;
            const newInactiveStatus = !member.inactive;

            this.$apollo.mutate({
                mutation: gql`
                    mutation updateMember($input: CreateMemberInput!) {
                        updateMember(input: $input) {
                            id
                            inactive
                        }
                    }
                `,
                variables: {
                    input: {
                        id: member.id,
                        inactive: newInactiveStatus
                    }
                }
            }).then(() => {
                this.$buefy.snackbar.open({
                    message: newInactiveStatus ? 'Spiller markeret som inaktiv' : 'Spiller markeret som aktiv',
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
        getGenderLabel(gender) {
            return gender === 'MEN' ? 'Herre' : 'Dame';
        },
        getStatusClass(member) {
            if (member.inactive) return 'has-text-danger';
            if (!member.playable) return 'has-text-warning';
            return 'has-text-success';
        },
        getStatusLabel(member) {
            if (member.inactive) return 'Inaktiv';
            if (!member.playable) return 'Midlertidigt utilgængelig';
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
                <p class="mb-2"><strong>Forskel på "Inaktiv" og "Midlertidigt utilgængelig":</strong></p>
                <ul class="ml-4">
                    <li><strong>Inaktiv:</strong> Spilleren er permanent stoppet med at spille badminton. Når en spiller markeres som inaktiv, vil de blive filtreret fra automatisk i holdrunder og andre spillerlister.</li>
                    <li><strong>Midlertidigt utilgængelig:</strong> Spilleren er skadet eller midlertidigt utilgængelig, men er stadig aktiv medlem. Dette håndteres via afbudssystemet i holdrunder.</li>
                </ul>
                <p class="mt-2"><em>Bemærk: Importering fra badmintonplayer.dk vil ikke ændre en spillers inaktiv-status, så manuelle ændringer bevares.</em></p>
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
                                @input="search"
                                placeholder="Indtast navn..."
                                icon="magnify"
                                dusk="search-name-input"
                            ></b-input>
                        </b-field>
                        <b-field label="Køn">
                            <b-select v-model="selectedGender" @input="search" dusk="gender-select">
                                <option
                                    v-for="option in genderOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </b-select>
                        </b-field>
                        <b-field label="Vis inaktive">
                            <b-switch v-model="showInactive" @input="search" dusk="show-inactive-switch"></b-switch>
                        </b-field>
                    </b-field>

                    <b-table
                        :data="membersList"
                        :loading="$apollo.queries.members.loading || isTogglingInactive"
                        :paginated="true"
                        :backend-pagination="true"
                        :total="paginatorInfo.total"
                        :per-page="perPage"
                        :current-page.sync="currentPage"
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
                            <b-tag :type="props.row.inactive ? 'is-danger' : 'is-success'">
                                {{ getStatusLabel(props.row) }}
                            </b-tag>
                        </b-table-column>

                        <b-table-column label="Handlinger" v-slot="props">
                            <b-button
                                size="is-small"
                                :type="props.row.inactive ? 'is-success' : 'is-danger'"
                                :icon-left="props.row.inactive ? 'account-check' : 'account-off'"
                                @click="toggleInactiveStatus(props.row)"
                                :dusk="`toggle-inactive-${props.row.id}`"
                            >
                                {{ props.row.inactive ? 'Marker som aktiv' : 'Marker som inaktiv' }}
                            </b-button>
                        </b-table-column>

                        <template v-slot:empty>
                            <div class="has-text-centered">
                                Ingen spillere fundet
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

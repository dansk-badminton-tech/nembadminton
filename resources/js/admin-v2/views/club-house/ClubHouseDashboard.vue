<script>
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import CardComponent from "@/components/CardComponent.vue";
import MyClubs from "@/views/club-house/MyClubs.vue";
import MemberList from "@/views/club-house/MemberList.vue";
import clubhouse from "../../../queries/clubhouse.gql";
import InvitationList from "@/views/club-house/InvitationList.vue";
import gql from "graphql-tag";

export default {
    name: "ClubHouseDashboard",
    components: {InvitationList, MemberList, MyClubs, CardComponent, HeroBar, TitleBar},
    inject: ['clubhouseId'],
    data() {
        return {
            titleStack: ['Admin', 'Klubhus'],
            name: '',
            email: '',
            isLoading: false,
            clubhouse: {
                users: []
            }
        }
    },
    methods: {
        updateClubhouse() {
            this.isLoading = true;
            this.$apollo.mutate({
                            mutation: gql`
                    mutation updateClubhouse($input: UpdateClubhouseInput!){
                        updateClubhouse(input: $input){
                            id
                            name
                            email
                        }
                    }`,
                            variables: {
                                input: {
                                    id: this.clubhouse.id,
                                    name: this.name,
                                    email: this.email
                                }
                            },
                            refetchQueries: [
                                {
                                    query: clubhouse,
                                    variables: {
                                        id: this.clubhouseId
                                    }
                                }
                            ]
                        }).then(({data}) => {
                this.$buefy.snackbar.open(
                    {
                        message: 'Klubhus opdateret',
                        type: 'is-success',
                        duration: 5000
                    })
            }).catch((error) => {
                this.$buefy.snackbar.open(
                    {
                        message: 'Klubhus kunne ikke opdateres',
                        type: 'is-danger',
                        duration: 5000
                    })
            }).finally(() => {
                this.isLoading = false;
            })
        }
    },
    apollo: {
        clubhouse: {
            query: clubhouse,
            variables() {
                return {
                    id: this.clubhouseId
                }
            },
            skip() {
                return !this.clubhouseId
            },
            result({data}) {
                this.name = data.clubhouse.name
                this.email = data.clubhouse.email
            }
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Klubhus
        </hero-bar>
        <section class="section is-main-section">
            <card-component
                title="Rediger Klubhus"
                icon="home"
            >
                <form @submit.prevent="updateClubhouse">
                    <b-field
                        horizontal
                        label="Name"
                    >
                        <b-input
                            v-model="name"
                            name="name"
                            placeholder="SAIF"
                            required
                        />
                    </b-field>
                    <b-field
                        horizontal
                        label="E-mail"
                    >
                        <b-input
                            v-model="email"
                            name="email"
                            type="email"
                            placeholder="info@nembadminton.dk"
                            required
                        />
                    </b-field>
                    <hr>
                    <b-field horizontal>
                        <b-field grouped>
                            <div class="control">
                                <b-button
                                    native-type="submit"
                                    type="is-info"
                                    :loading="isLoading"
                                >
                                    Opdater
                                </b-button>
                            </div>
                        </b-field>
                    </b-field>
                </form>
            </card-component>
            <b-tabs>
                <b-tab-item label="Medlemmer">
                    <member-list :loading="$apollo.loading" :users="clubhouse?.users || []"/>
                </b-tab-item>
                <b-tab-item label="Invitationer">
                    <invitation-list :loading="$apollo.loading" :invitations="clubhouse?.invitations || []"/>
                </b-tab-item>
            </b-tabs>
            <my-clubs/>
        </section>
    </div>
</template>

<style scoped>

</style>

<script>
import ME from "../../../queries/me.gql";
import CardComponent from "@/components/CardComponent.vue";
import gql from "graphql-tag";

export default {
    name: "SignUpFinish",
    components: {CardComponent},
    data(){
        return {
            isLoading: false,
            email: null,
            name: null
        }
    },
    apollo: {
        me: {
            query: ME,
        },
    },
    computed: {
        hasClubhouse() {
            return this?.me?.clubhouse !== null
        }
    },
    methods: {
        toDashboard(){
            this.$router.push({name: 'home', params: {clubhouseId: this.me.clubhouse.id}})
        },
        createClubhouse(){
            this.$apollo.mutate({
                mutation: gql`
                    mutation createClubhouse($input: CreateClubhouseInput!){
                        createClubhouse(input: $input){
                            id
                            name
                            email
                        }
                    }
                `,
                variables: {
                    input: {
                        name: this.name,
                        email: this.email,
                        users: {
                            connect: [this.me.id]
                        }
                    }
                },
                refetchQueries: [ME]
                })
                .then(({data}) => {
                    this.$router.push({name: 'home', params: {clubhouseId: data.createClubhouse.id}})
                })
                .catch((err) => {
                    console.log(err)
                })
        },
    }
}
</script>

<template>
    <card-component
        title="Tilslut klub"
        icon="lock"
    >
        <template v-slot:default>
            <div v-show="hasClubhouse">
                <h2 class="subtitle">Du er allerede tilknyttet et klubhus</h2>
                <b-button @click="toDashboard">Videre</b-button>
            </div>
            <form @submit.prevent="createClubhouse">
                <b-field
                    horizontal
                    label="Name"
                    message="Navnet på klubben">
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
                    message="Klubbens email">
                    <b-input
                        v-model="email"
                        name="email"
                        type="email"
                        placeholder="info@nembadminton.dk"
                        required
                    />
                </b-field>
                <b-button
                    native-type="submit"
                    type="is-info"
                    :loading="isLoading">
                    Opret
                </b-button>
            </form>
        </template>
    </card-component>
</template>

<style scoped>

</style>

<template>
    <form @submit.prevent>
        <b-field v-if="!onlyBadmintonId" label="Navn">
            <b-input v-model="me.name"></b-input>
        </b-field>
        <b-field v-if="!onlyBadmintonId" label="Email">
            <b-input v-model="me.email" type="email"></b-input>
        </b-field>
        <b-field label="Badminton ID">
            <b-input v-model="me.player_id" placeholder="ÅÅMMDD-XX" type="text"></b-input>
        </b-field>
        <a class="is-clearfix" href="https://www.badmintonplayer.dk/DBF/Ranglister/" target="_blank">Find Badminton ID på ranglisten</a>
        <b-button :loading="updatingProfile" class="mt-2" @click="update">Gem</b-button>
        <div class="mt-4"></div>
    </form>
</template>
<script>
import gql from 'graphql-tag'

export default {
    name: 'ChangeProfile',
    apollo: {
        me: {
            query: gql`
                query{
                    me{
                        id
                        email
                        name
                        player_id
                    }
                }`,
            fetchPolicy: "network-only"
        }
    },
    props: {
        onlyBadmintonId: Boolean
    },
    methods: {
        update() {
            this.updatingProfile = true
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation updateMe($input: UpdateMe!){
                            updateMe(input: $input){
                                id
                                name
                                email
                                player_id
                            }
                        }
                    `,
                    variables: {
                        input: {
                            name: this.me.name,
                            email: this.me.email,
                            player_id: this.me.player_id,
                        }
                    }
                }
            ).then(() => {
                this.$root.$emit('userUpdated')
                this.$buefy.snackbar.open(
                    {
                        duration: 2000,
                        type: 'is-success',
                        message: `Din profil er nu opdateret`
                    })
            }).finally(() => {
                this.updatingProfile = false
            })
        }
    },
    data() {
        return {
            me: {
                name: '',
                email: '',
            },
            updatingProfile: false
        }
    }
}
</script>

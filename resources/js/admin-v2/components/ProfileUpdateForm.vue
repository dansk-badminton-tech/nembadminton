<template>
    <card-component
        title="Rediger Profil"
        icon="account-circle"
    >
        <form @submit.prevent="submit">
            <b-field
                horizontal
                label="Name"
                message="Påkravet. Dit navn"
            >
                <b-input
                    v-model="userName"
                    name="name"
                    required
                />
            </b-field>
            <b-field
                horizontal
                label="E-mail"
                message="Påkravet. Din e-mail"
            >
                <b-input
                    v-model="userEmail"
                    name="email"
                    type="email"
                    required
                />
            </b-field>
            <b-field
                horizontal
                label="Spiller ID"
                :message="playerIdMessage"
                :type="playerIdType"
            >
                <b-input
                    v-model="playerId"
                    placeholder="123456-78"
                    maxlength="9"
                    pattern="^\d{6}-\d{2}$"
                    @input="onPlayerIdInput"
                    @blur="onPlayerIdBlur"
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
</template>

<script>
import {defineComponent} from 'vue'
import FilePicker from '@/components/FilePicker.vue'
import CardComponent from '@/components/CardComponent.vue'
import gql from "graphql-tag";

export default defineComponent({
    name: 'ProfileUpdateForm',
    inject: ['user'],
    components: {
        CardComponent,
        FilePicker
    },
    data() {
        return {
            isLoading: false,
            playerId: ''
        }
    },
    watch: {
        // Initialize/refresh playerId from injected user
        user: {
            immediate: true,
            handler(newUser) {
                const pid = newUser?.player_id || ''
                // Reuse formatter so value is always in XXXXXX-XX format
                this.onPlayerIdInput(pid)
            }
        }
    },
    computed: {
        userName: {
            get: function () {
                return this.$store.state.userName
            },
            set: function (name) {
                this.$store.commit('user', {name})
            }
        },
        userEmail: {
            get: function () {
                return this.$store.state.userEmail
            },
            set: function (email) {
                this.$store.commit('user', {email})
            }
        },
        playerIdValid() {
            // Must match XXXXXX-XX (six digits, a dash, two digits)
            return /^\d{6}-\d{2}$/.test(this.playerId) || this.playerId === ''
        },
        playerIdType() {
            return this.playerId === '' ? null : (this.playerIdValid ? 'is-success' : 'is-danger')
        },
        playerIdMessage() {
            if (this.playerId === '') return 'Format: 6 cifre, bindestreg, 2 cifre (f.eks. 123456-78)'
            return this.playerIdValid ? 'Gyldigt format' : 'Ugyldigt format. Brug 123456-78'
        }
    },
    methods: {
        onPlayerIdInput(val) {
            // Strip all non-digits, then insert dash after 6 digits
            const digits = String(val).replace(/\D/g, '').slice(0, 8) // up to 8 digits total
            let formatted = digits
            if (digits.length > 6) {
                formatted = digits.slice(0, 6) + '-' + digits.slice(6, 8)
            }
            this.playerId = formatted
        },
        onPlayerIdBlur() {
            // Ensure dash exists when exactly 6 digits entered without dash
            if (/^\d{6}$/.test(this.playerId)) {
                this.playerId = this.playerId + '-'
            }
        },
        submit() {
            this.isLoading = true
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
                            name: this.$store.state.userName,
                            email: this.$store.state.userEmail,
                            player_id: this.playerId === '' ? null : this.playerId,
                        }
                    }
                }
            ).then(() => {
                this.$buefy.snackbar.open(
                    {
                        duration: 2000,
                        type: 'is-success',
                        message: `Din profil er nu opdateret`
                    })
            }).finally(() => {
                this.isLoading = false
            })
        }
    }
})
</script>

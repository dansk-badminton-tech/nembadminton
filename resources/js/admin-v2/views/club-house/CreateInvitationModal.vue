<template>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Send invitation</p>
            <button class="delete" @click="$emit('close')" aria-label="close"></button>
        </header>

        <section class="modal-card-body">
            <form ref="inviteForm" @submit.prevent="submitForm">
                <b-field label="Email" message="Email address til den som du vil invitere">
                    <b-input
                        v-model="formData.email"
                        type="email"
                        placeholder="Enter invitee's email"
                        required>
                    </b-input>
                </b-field>

                <!-- Dropdown to select the role -->
                <b-field label="Role" message="Hvilken en role som skal tildeles denne person?">
                    <b-select v-model="formData.role" placeholder="Vælge en role" required>
                        <option :value="role.value" :key="role.value" v-for="role in roles">{{role.label}}</option>
                    </b-select>
                </b-field>
            </form>
        </section>

        <footer class="modal-card-foot">
            <b-button type="is-success" :loading="isSubmitting" @click="handleSubmit">Send Invitation</b-button>
            <b-button @click="$emit('close')">Cancel</b-button>
        </footer>
    </div>
</template>

<script>

import gql from "graphql-tag";
import {roles} from "@/helpers"
import clubhouse from "@/../queries/clubhouse.gql"

export default {
    name: 'CreateInvitationModal',
    inject: ['clubhouseId', 'user'],
    data() {
        return {
            isSubmitting: false,
            formData: {
                email: '',
                role: 'COACH'
            },
            roles
        };
    },
    methods: {
        handleSubmit() {
            const formElement = this.$refs.inviteForm;
            if (formElement.checkValidity()) {
                this.submitForm();
            } else {
                formElement.reportValidity(); // Ensures browser shows validation errors
            }
        },
        submitForm() {
            this.isSubmitting = true;

            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation invite($input: CreateInvitationInput!) {
                          invite(input: $input) {
                            inviteeEmail
                            role
                            status
                          }
                        }
                    `,
                    variables: {
                        input: {
                            email: this.formData.email,
                            role: this.formData.role,
                            inviter: {
                                connect: this.user.id
                            },
                            clubhouse: {
                                connect: this.clubhouseId
                            }
                        },
                    },
                    refetchQueries: [{
                        query: clubhouse,
                        variables: {
                            id: this.clubhouseId
                        }
                    }]
                })
                .then(({data}) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 6000,
                            type: 'is-success',
                            message: 'Invitationen er blevet sendt'
                        }
                    )
                    this.$emit('close');
                })
                .catch(({graphQLErrors}) => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 6000,
                            type: 'is-danger',
                            message: 'Kunne ikke sende invitationen'
                        })
                })
                .finally(() => {
                    this.isSubmitting = false;
                })

        }
    }
};
</script>

<style scoped>
.modal-card {
    max-width: 100%;
}
</style>

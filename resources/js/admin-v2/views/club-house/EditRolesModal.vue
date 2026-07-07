<template>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Rediger roller</p>
            <button class="delete" @click="$emit('close')" aria-label="close"></button>
        </header>

        <section class="modal-card-body">
            <p class="content">
                <strong>{{ target?.name }}</strong> — {{ target?.email }}
            </p>
            <b-field label="Roller">
                <div class="field" v-for="role in roles" :key="role.value">
                    <b-checkbox
                        v-model="formData.roles"
                        :native-value="role.value"
                    >
                        {{ role.label }}
                    </b-checkbox>
                </div>
            </b-field>
        </section>

        <footer class="modal-card-foot">
            <b-button @click="$emit('close')">Annuller</b-button>
            <b-button type="is-info" :loading="isSubmitting" @click="handleSubmit">Gem</b-button>
        </footer>
    </div>
</template>

<script>
import UPDATE_MEMBERSHIP_ROLES from "@/../queries/updateMembershipRoles.gql"
import clubhouse from "@/../queries/clubhouse.gql"
import {roles, roleNameToEnum} from "@/helpers"

export default {
    name: 'EditRolesModal',
    inject: ['clubhouseId'],
    props: {target: Object},
    data() {
        return {
            isSubmitting: false,
            formData: {roles: []},
            roles
        }
    },
    created() {
        this.formData.roles = (this.target?.roles ?? [])
            .map(r => roleNameToEnum[r.name])
            .filter(Boolean);
    },
    methods: {
        handleSubmit() {
            this.isSubmitting = true;
            this.$apollo.mutate({
                mutation: UPDATE_MEMBERSHIP_ROLES,
                variables: {
                    clubhouseId: String(this.clubhouseId),
                    userId: String(this.target.id),
                    roles: this.formData.roles
                },
                refetchQueries: [{query: clubhouse, variables: {id: this.clubhouseId}}]
            })
                .then(() => {
                    this.$buefy.snackbar.open({
                        message: 'Roller opdateret',
                        type: 'is-success',
                        duration: 5000,
                        queue: false
                    });
                    this.$emit('close');
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                        message: 'Kunne ikke opdatere roller',
                        type: 'is-danger',
                        duration: 5000,
                        queue: false
                    });
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
        }
    }
}
</script>

<style scoped>
.modal-card {
    max-width: 100%;
}
</style>

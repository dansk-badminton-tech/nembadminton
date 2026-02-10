<template>
    <b-modal 
        :active="isActive" 
        @close="$emit('close')"
        :width="600"
        scroll="keep">
        <div class="modal-card" style="width: auto;">
            <header class="modal-card-head">
                <p class="modal-card-title">
                    <b-icon :icon="isEditing ? 'pencil' : 'plus'" size="is-small"></b-icon>
                    <span class="ml-2">{{ isEditing ? 'Rediger Hold' : 'Opret Hold' }}</span>
                </p>
            </header>
            
            <section class="modal-card-body">
                <form @submit.prevent="saveTeam">
                    <b-field 
                        label="Holdnavn" 
                        :type="errors.name ? 'is-danger' : ''"
                        :message="errors.name">
                        <b-input
                            v-model="form.name"
                            placeholder="F.eks. SAIF 1"
                            required
                            icon="soccer"
                            maxlength="255"
                        />
                    </b-field>

                    <b-field 
                        label="Klub"
                        :type="errors.club_id ? 'is-danger' : ''"
                        :message="errors.club_id">
                        <b-select 
                            v-model="form.club_id" 
                            placeholder="Vælg klub"
                            expanded>
                            <option 
                                v-for="club in clubs" 
                                :key="club.id" 
                                :value="club.id">
                                {{ club.name1 }}
                            </option>
                        </b-select>
                    </b-field>

                    <b-field 
                        label="Division"
                        :type="errors.division_id ? 'is-danger' : ''"
                        :message="errors.division_id">
                        <b-select 
                            v-model="form.division_id" 
                            placeholder="Vælg division"
                            expanded>
                            <option 
                                v-for="division in divisions" 
                                :key="division.id" 
                                :value="division.id">
                                {{ division.name }}
                            </option>
                        </b-select>
                    </b-field>

                    <b-notification 
                        v-if="generalError" 
                        type="is-danger" 
                        :closable="false">
                        {{ generalError }}
                    </b-notification>
                </form>
            </section>
            
            <footer class="modal-card-foot">
                <b-button 
                    type="is-primary" 
                    :loading="isSaving"
                    @click="saveTeam"
                    icon-left="content-save">
                    {{ isEditing ? 'Opdater' : 'Opret' }}
                </b-button>
                <b-button @click="$emit('close')">
                    Annuller
                </b-button>
            </footer>
        </div>
    </b-modal>
</template>

<script>
import gql from "graphql-tag";

const CREATE_LEAGUE_TEAM_MUTATION = gql`
    mutation CreateLeagueTeam($input: CreateLeagueTeamInput!) {
        createLeagueTeam(input: $input) {
            id
            name
            created_at
            updated_at
            club {
                id
                name1
            }
            division {
                id
                name
            }

        }
    }
`;

const UPDATE_LEAGUE_TEAM_MUTATION = gql`
    mutation UpdateLeagueTeam($input: UpdateLeagueTeamInput!) {
        updateLeagueTeam(input: $input) {
            id
            name
            created_at
            updated_at
            club {
                id
                name1
            }
            division {
                id
                name
            }

        }
    }
`;

export default {
    name: "LeagueTeamFormModal",
    props: {
        isActive: {
            type: Boolean,
            default: false
        },
        team: {
            type: Object,
            default: null
        },
        clubs: {
            type: Array,
            default: () => []
        },
        divisions: {
            type: Array,
            default: () => []
        }
    },
    emits: ['close', 'saved'],
    data() {
        return {
            form: {
                name: '',
                club_id: '',
                division_id: ''
            },
            errors: {},
            generalError: '',
            isSaving: false
        }
    },
    computed: {
        isEditing() {
            return !!this.team;
        }
    },
    watch: {
        isActive(newVal) {
            if (newVal) {
                this.initializeForm();
            }
        },
        team() {
            this.initializeForm();
        }
    },
    methods: {
        initializeForm() {
            this.errors = {};
            this.generalError = '';
            
            if (this.team) {
                // Editing existing team
                this.form = {
                    name: this.team.name || '',
                    club_id: this.team.club.id || '',
                    division_id: this.team.division.id || ''
                };
            } else {
                // Creating new team
                this.form = {
                    name: '',
                    club_id: '',
                    division_id: ''
                };
            }
        },
        async saveTeam() {
            if (this.isSaving) return;
            
            this.errors = {};
            this.generalError = '';
            this.isSaving = true;

            try {
                // Prepare input data
                const input = {
                    name: this.form.name,
                    club_id: this.form.club_id || null,
                    division_id: this.form.division_id || null
                };

                if (this.isEditing) {
                    input.id = this.team.id;
                    await this.$apollo.mutate({
                        mutation: UPDATE_LEAGUE_TEAM_MUTATION,
                        variables: { input }
                    });
                    
                    this.$buefy.toast.open({
                        message: 'Hold opdateret succesfuldt',
                        type: 'is-success'
                    });
                } else {
                    await this.$apollo.mutate({
                        mutation: CREATE_LEAGUE_TEAM_MUTATION,
                        variables: { input }
                    });
                    
                    this.$buefy.toast.open({
                        message: 'Hold oprettet succesfuldt',
                        type: 'is-success'
                    });
                }

                this.$emit('saved');
            } catch (error) {
                console.error('Error saving league team:', error);
                
                if (error.graphQLErrors && error.graphQLErrors.length > 0) {
                    const validationErrors = error.graphQLErrors[0].extensions?.validation;
                    if (validationErrors) {
                        // Handle validation errors
                        this.errors = {};
                        Object.keys(validationErrors).forEach(field => {
                            this.errors[field] = validationErrors[field][0];
                        });
                    } else {
                        this.generalError = error.graphQLErrors[0].message;
                    }
                } else {
                    this.generalError = 'Der opstod en uventet fejl. Prøv igen.';
                }
            } finally {
                this.isSaving = false;
            }
        }
    }
}
</script>

<style scoped>

</style>

<template>
    <form @submit.prevent="submit">
        <b-field label="Tilføj point">
            <b-select v-model="category" required placeholder="Category">
                <option v-for="category in missingCategories" :value="category">{{ category }}</option>
            </b-select>
            <b-input v-model.number="point" required type="number"></b-input>
            <p class="control">
                <b-button type="is-success" :loading="loading" native-type="submit" label="Opret"/>
            </p>
        </b-field>
    </form>
</template>
<script>

import gql from "graphql-tag";

export default {
    name: 'EditPlayerAddPoint',
    props: {
        player: {},
        missingCategories: [],
        version: {
            type: [Date, undefined],
            default: undefined
        }
    },
    data() {
        return {
            category: null,
            point: 1000,
            loading: false
        }
    },
    methods: {
        submit() {
            this.loading = true
            this.$apollo.mutate({
                                    mutation: gql`mutation UpdateSquadMember($input: UpdateSquadMemberInput!) {
                                      updateSquadMember(input: $input) {
                                        id
                                        points {
                                            id
                                            points
                                            version
                                        }
                                      }
                                    }`,
                                    variables: {
                                        input: {
                                            id: this.player.id,
                                            points: {
                                                create: [{
                                                    category: this.category,
                                                    points: this.point,
                                                    position: 0,
                                                    vintage: this.player.vintage,
                                                    version: this.version.toISOString().substring(0,10),
                                                    corrected_manually: false
                                                }]
                                            }
                                        }
                                    }
                                })
                .then(response => {
                    console.log(response.data);
                })
                .catch(error => {
                    // Handle the error if needed
                    console.error(error);
                })
                .finally(() => {
                    this.loading = false
                });
        }
    }
}
</script>

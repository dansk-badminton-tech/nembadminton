<script>
import {convertShortCategoryAndGenderToFinalCategory, timeToMonth, vintageOptions} from "./helper";
import gql from "graphql-tag";

export default {
    name: "AddSquadMemberModal",
    computed: {
        vintageOptions: vintageOptions,
    },
    props: {
        version: Date,
        category: Object
    },
    data() {
        return {
            gender: 'MEN',
            name: '',
            vintage: 'SEN',
            levelPoint: {category: "LEVEL", label: "Niveau", points: 1000},
            categoryPoint: {category: "LEVEL", label: "Niveau", points: 1000},
            loading: false,
            refBirthday: '',
            refEndId: ''
        }
    },
    methods: {
        timeToMonth,
        createMember() {
            this.loading = true
            this.$apollo
                .mutate({
                            mutation: gql`
                                mutation createSquadMember($input: CreateSquadMemberInput!){
                                    createSquadMember(input: $input){
                                        id
                                        gender
                                        name
                                    }
                                }
                            `,
                            variables: {
                                input: {
                                    category: {
                                        connect: this.category.id
                                    },
                                    gender: this.gender,
                                    name: this.name,
                                    refId: this.refBirthday+'-'+this.refEndId,
                                    points: {
                                        create: [
                                            {
                                                category: null,
                                                points: this.levelPoint.points,
                                                vintage: this.vintage,
                                                corrected_manually: true
                                            },
                                            {
                                                category: convertShortCategoryAndGenderToFinalCategory(this.category.category, this.gender),
                                                points: this.categoryPoint.points,
                                                vintage: this.vintage,
                                                corrected_manually: true
                                            }
                                        ]
                                    }
                                }
                            }
                        })
                .then(() => {
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-success',
                                                  message: `Spiller oprettet`
                                              })
                    this.$emit('close')
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-danger',
                                                  message: `Kunne ikke oprette spiller :(`
                                              })
                })
                .finally(() => {
                    this.loading = false
                })
        }
    }
}
</script>

<template>
    <form @submit.prevent="createMember">
        <div class="modal-card" style="width: auto">
            <header class="modal-card-head">
                <p class="modal-card-title">Opret spiller</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <b-field label="Navn">
                    <b-input
                        type="text"
                        v-model="name"
                        placeholder="Name"
                        required>
                    </b-input>
                </b-field>
                <b-field label="Køn">
                    <b-select v-model="gender" required expanded>
                        <option value="MEN">Mand</option>
                        <option value="WOMEN">Kvinde</option>
                    </b-select>
                </b-field>
                <b-field label="Årgang">
                    <b-select v-model="vintage" required expanded>
                        <option v-for="vintageOption in vintageOptions" :key="vintageOption.value" :value="vintageOption.value">{{ vintageOption.label }}</option>
                    </b-select>
                </b-field>
                <b-field grouped label="Badmintonplayer ID">
                    <b-field expanded>
                        <b-field>
                            <b-input v-model="refBirthday" type="text" minlength="6" maxlength="6" placeholder="XXXXXX" expanded required></b-input>
                            <b-input v-model="refEndId" type="text" minlength="2" maxlength="2" placeholder="XX" required></b-input>
                        </b-field>
                    </b-field>
                </b-field>
                <hr/>
                <label class="label">Points</label>
                <b-field horizontal :label="this.levelPoint.label">
                    <b-input type="number" v-model="this.levelPoint.points" required></b-input>
                </b-field>
                <b-field horizontal :label="this.category.category">
                    <b-input type="number" v-model="this.categoryPoint.points" required></b-input>
                </b-field>
            </section>
            <footer class="modal-card-foot">
                <b-button
                    :loading="this.loading"
                    native-type="submit"
                    label="Gem"/>
                <b-button
                    :loading="this.loading"
                    @click="$emit('close')"
                    label="Luk"/>
            </footer>
        </div>
    </form>
</template>

<style scoped>

</style>

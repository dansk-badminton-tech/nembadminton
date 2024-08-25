<script>
import gql from "graphql-tag";
import {convertCategoryAndGenderToFinalCategory, timeToMonth, vintageOptions} from "./helper";
import ME from "../../../queries/me.gql";

export default {
    name: "AddPlayerModal",
    props: {
        version: Date
    },
    data() {
        return {
            gender: 'MEN',
            name: '',
            refBirthday: '',
            refEndId: '',
            vintage: 'SEN',
            club: null,
            points: [
                {id: null, category: "SINGLE", label: "Single", points: 1000},
                {id: null, category: "DOUBLE", label: "Double", points: 1000},
                {id: null, category: "MIXDOUBLE", label: "Mix double", points: 1000}
            ],
            loading: false
        }
    },
    computed: {
        vintageOptions: vintageOptions,
        memberExists(){
            return this.membersSearch?.data?.length > 0;
        },
        refId(){
            return this.refBirthday + '-' + this.refEndId
        },
        versionMonth(){
            return timeToMonth(this.version.toISOString().substring(0, 10))
        }
    },
    apollo: {
        me: {
            query: ME
        },
        membersSearch: {
            query: gql`query membersSearch($refId: String, $version: Date){
                      membersSearch(refId: $refId) {
                        data {
                          id
                          gender
                          name
                          refId
                          points(version: $version){
                            id
                            points
                            position
                            category
                            version
                            vintage
                          }
                          clubs{
                            id
                            name1
                            initialized
                          }
                        }
                      }
                    }
                `,
            variables() {
                return {
                    refId: this.refId,
                    version: this.version.toISOString().slice(0, 10)
                }
            },
            result({data}){
                if(data.membersSearch.data.length > 0){
                    let member = data.membersSearch.data[0]
                    this.gender = member.gender
                    this.name = member.name
                    this.club = member.clubs[0].id
                    this.points.forEach(point => {
                        let remotePoints = data.membersSearch.data[0].points.find(pointItem => pointItem.category === convertCategoryAndGenderToFinalCategory(point.category, this.gender));
                        if(remotePoints !== undefined){
                            point.id = remotePoints.id
                            point.points = remotePoints.points;
                        }
                    });
                }
                console.log(data)
            },
            fetchPolicy: "network-only"
        }
    },
    methods: {
        createMember(e) {
            this.loading = true
            let memberId = null
            if(this.membersSearch?.data?.length > 0){
                memberId = this.membersSearch.data[0].id
            }
            this.$apollo
                .mutate({
                            mutation: gql`
                                mutation createMember($input: CreateMemberInput!, $version: Date){
                                    createMember(input: $input){
                                        id
                                        gender
                                        name
                                          refId
                                          points(version: $version){
                                            id
                                            points
                                            position
                                            category
                                            version
                                            vintage
                                          }
                                    }
                                }
                            `,
                            variables: {
                                version: this.version.toISOString().substring(0,10),
                                input: {
                                    id: memberId,
                                    gender: this.gender,
                                    name: this.name,
                                    refId: this.refId,
                                    points: {
                                        upsert: this.points.map((point) => ({
                                            id: point.id,
                                            category: convertCategoryAndGenderToFinalCategory(point.category, this.gender),
                                            points: point.points,
                                            vintage: this.vintage,
                                            version: this.version.toISOString().substring(0,10)
                                        }))
                                    },
                                    clubs: {
                                        upsert: [{id: this.club}]
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
                <p class="modal-card-title">Opret spiller på ranglisten</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <p>Spilleren bliver oprettet på ranglisten <strong>{{ versionMonth }}</strong>, spilleren kan ses af <strong>alle</strong> som har klubben tilknyttet.</p>
                <hr/>
                <b-field grouped label="Badmintonplayer ID">
                    <b-field expanded>
                        <b-field>
                            <b-input v-model="refBirthday" type="text" minlength="6" maxlength="6" placeholder="XXXXXX" expanded required></b-input>
                            <b-input v-model="refEndId" type="text" minlength="2" maxlength="2" placeholder="XX" required></b-input>
                        </b-field>
                    </b-field>
                </b-field>
                <b-message v-if="memberExists" type="is-info">Brugeren med {{this.refId}} findes allerede i systemet. Der vil ikke blive oprettet en ny bruger, men i stedet vil brugerens point blive opdateret på {{versionMonth}} til de nye data.</b-message>
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
                        <option value="WOMEN">Dame</option>
                    </b-select>
                </b-field>
                <b-field label="Årgang">
                    <b-select v-model="vintage" required expanded>
                        <option v-for="vintageOption in vintageOptions" :key="vintageOption.value" :value="vintageOption.value">{{ vintageOption.label }}</option>
                    </b-select>
                </b-field>
                <b-field label="Klub">
                    <b-select v-model="club" expanded required>
                        <option v-for="club in me?.clubs" :key="club.id" :value="club.id">{{ club.name1 }}</option>
                    </b-select>
                </b-field>
                <hr/>
                <label class="label">Points</label>
                <b-field horizontal :label="point.label" v-for="point in this.points" :key="point.category">
                    <b-input type="number" v-model.number="point.points" required></b-input>
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

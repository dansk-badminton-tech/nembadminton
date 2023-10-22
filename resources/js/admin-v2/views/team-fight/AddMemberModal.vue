<script>
import {debounce} from "../../helpers";
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
        {category: "LEVEL", label: "Niveau", points: 1000, version: "2023-10-01"},
        {category: "SINGLE", label: "Single", points: 1000, version: "2023-10-01"},
        {category: "DOUBLE", label: "Double", points: 1000, version: "2023-10-01"},
        {category: "MIXDOUBLE", label: "Mix double", points: 1000, version: "2023-10-01"}
      ],
      loading: false
    }
  },
  computed: {
    vintageOptions: vintageOptions,
  },
  apollo: {
    me: {
      query: ME
    }
  },
  methods: {
    timeToMonth,
    createMember(e) {
      this.loading = true
      this.$apollo
          .mutate({
                    mutation: gql`
                                mutation createMember($input: CreateMemberInput!){
                                    createMember(input: $input){
                                        id
                                        gender
                                    }
                                }
                            `,
                    variables: {
                      input: {
                        gender: this.gender,
                        name: this.name,
                        refId: this.refBirthday+'-'+this.refEndId,
                        points: {
                          create: this.points.map((point) => ({category: convertCategoryAndGenderToFinalCategory(point.category, this.gender), points: point.points, vintage: this.vintage, version: point.version}))
                        },
                        clubs: {
                          connect: [this.club]
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
        <p>Spilleren bliver oprettet på ranglisten <strong>{{ timeToMonth(this.version.toISOString().substring(0, 10)) }}</strong>, spilleren kan ses af alle som har klubben tilknyttet.</p>
        <hr/>
        <b-field label="Navn">
          <b-input
              type="text"
              v-model="name"
              placeholder="Name"
              required>
          </b-input>
        </b-field>
        <b-field grouped label="Badmintonplayer ID">
          <b-field expanded>
            <b-field>
              <b-input v-model="refBirthday" type="text" minlength="6" maxlength="6" placeholder="XXXXXX" expanded required></b-input>
              <b-input v-model="refEndId" type="text" minlength="2" maxlength="2" placeholder="XX" required></b-input>
            </b-field>
          </b-field>
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
        <b-field label="Klub">
          <b-select v-model="club" expanded required>
            <option v-for="club in this.me?.clubs" :key="club.id" :value="club.id">{{ club.name1 }}</option>
          </b-select>
        </b-field>
        <hr/>
        <label class="label">Points</label>
        <b-field horizontal :label="point.label" v-for="point in this.points" :key="point.category">
          <b-input type="number" v-model="point.points" required></b-input>
        </b-field>
      </section>
      <footer class="modal-card-foot">
        <b-button
            :loading="this.loading"
            native-type="submit"
            label="Gem og luk"/>
      </footer>
    </div>
  </form>
</template>

<style scoped>

</style>

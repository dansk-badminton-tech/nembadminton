<script>
import clubhouse from "../../../queries/clubhouse.gql";
import gql from "graphql-tag";
import {getCurrentSeason} from "@/helpers.js";
import _ from "lodash/fp.js";

export default {
  name: 'ICalGenerator',
  props: ['teamId'],
  apollo: {
    badmintonPlayerTeams: {
      query: gql`
        query badmintonPlayerTeams($clubId: Int!, $season: Int!){
            badmintonPlayerTeams(input: {clubId: $clubId, season: $season}){
                name
                league
                ageGroupId
                leagueGroupId
            }
        }`,
      variables() {
        return {
          clubId: parseInt(this.teamId),
          season: getCurrentSeason()
        }
      }
    }
  },
  computed: {
    collapsedTeams() {
      return _.uniqBy('name', this.badmintonPlayerTeams)
    },
    icalUrl() {
      let getUrl = window.location;
      let query = new URLSearchParams({only: this.selectedTeams.join(",")})
      console.log(query.toString())
      return getUrl.protocol + "//" + getUrl.host + "/team/" + this.teamId + '/ical-classic' + (query.toString() === 'only=' ? '': '?'+query.toString());
    }
  },
  methods: {
    copyToClipboard() {
      this.$copyText(this.icalUrl).then((e) => {
        this.$buefy.snackbar.open(`Kopiret til udklipsholder`)
      }, (e) => {
        this.$buefy.snackbar.open(`Kunne ikke kopir til udklipsholder. :(`)
      })
    },
  },
  data() {
    return {
      selectedTeams: []
    }
  }
}

</script>

<template>
  <div>
    <div class="block">
      <b-checkbox v-for="team in collapsedTeams" :key="team.name" v-model="selectedTeams"
                  :native-value="team.name">
        {{ team.name }}
      </b-checkbox>
    </div>
    <b-field>
      <b-input
          type="text"
          readonly="true"
          expanded
          :value="icalUrl">
      </b-input>
      <p class="control">
        <b-button class="button is-primary" @click="copyToClipboard">Kopier</b-button>
      </p>
    </b-field>
  </div>
</template>

<style scoped>

</style>

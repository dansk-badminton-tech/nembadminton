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
    },
    webcalUrl() {
      // Convert https:// to webcal:// for calendar apps
      return this.icalUrl.replace('https://', 'webcal://').replace('http://', 'webcal://');
    },
    googleCalendarUrl() {
      // Google Calendar subscription URL
      return `https://calendar.google.com/calendar/u/0/r?cid=${encodeURIComponent(this.webcalUrl)}`;
    },
    outlookUrl() {
      // Outlook calendar subscription URL
      return `https://outlook.live.com/calendar/0/addcalendar?url=${encodeURIComponent(this.webcalUrl)}`;
    },
    appleCalendarUrl() {
      // Apple Calendar uses webcal:// protocol
      return this.webcalUrl;
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

    <!-- Direct Calendar Subscription Buttons -->
    <div class="block" v-if="selectedTeams.length > 0">
      <h6 class="title is-6 mb-3">
        <b-icon icon="calendar-plus" size="is-small"></b-icon>
        Tilføj direkte til kalender:
      </h6>
      
      <div class="buttons">
        <b-button
          tag="a"
          :href="googleCalendarUrl"
          target="_blank"
          type="is-success"
          icon-left="google"
          size="is-small">
          Google Calendar
        </b-button>
        
        <b-button
          tag="a"
          :href="appleCalendarUrl"
          type="is-dark"
          icon-left="apple"
          size="is-small">
          Apple Calendar
        </b-button>
        
        <b-button
          tag="a"
          :href="outlookUrl"
          target="_blank"
          type="is-info"
          icon-left="microsoft"
          size="is-small">
          Outlook
        </b-button>
      </div>
    </div>

    <!-- Manual URL Copy (fallback) -->
    <div class="block" v-if="selectedTeams.length > 0">
      <b-field>
        <b-input
            type="text"
            readonly="true"
            expanded
            :value="icalUrl"
            placeholder="Vælg hold for at generere kalender-link">
        </b-input>
        <p class="control">
          <b-button class="button is-primary" @click="copyToClipboard">Kopier URL</b-button>
        </p>
      </b-field>
      <p class="help">Manuel URL til kopier og indsæt i din kalender-app</p>
    </div>

    <!-- Instructions when no teams selected -->
    <div v-if="selectedTeams.length === 0" class="has-text-centered py-4">
      <b-icon icon="information" size="is-large" type="is-grey-light"></b-icon>
      <p class="has-text-grey mt-2">Vælg mindst ét hold for at generere kalender-links</p>
    </div>
  </div>
</template>

<style scoped>

</style>

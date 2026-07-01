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
        label="Spiller"
        message="Søg efter dit navn og vælg dig selv som spiller i klubben for at forbinde din profil."
      >
        <b-autocomplete
          v-model="searchName"
          :data="searchResults"
          placeholder="Søg efter dit navn..."
          field="name"
          :loading="$apollo.queries.membersSearch.loading"
          :clearable="true"
          @typing="onTyping"
          @select="onSelectMember"
          keep-first
        >
          <template v-slot:default="{ option }">
            <div class="media">
              <div class="media-content">
                {{ option.name }}
                <br>
                <small>BadmintonID: {{ option.refId }}</small>
              </div>
            </div>
          </template>
          <template slot="empty">Ingen spillere fundet</template>
        </b-autocomplete>
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
import { defineComponent } from 'vue'
import {debounce, extractErrors} from '@/helpers.js'
import FilePicker from '@/components/FilePicker.vue'
import CardComponent from '@/components/CardComponent.vue'
import gql from "graphql-tag";

export default defineComponent({
  name: 'ProfileUpdateForm',
  components: {
    CardComponent,
    FilePicker
  },
  inject: ['user', 'clubhouseId'],
  data () {
    return {
      isLoading: false,
      playerId: null,
      initialPlayerId: null,
      searchName: '',
      querySearchName: '',
      selectedMember: null,
      searchResults: []
    }
  },
  computed: {
    userName: {
      get: function () {
        return this.$store.state.userName
      },
      set: function (name) {
        this.$store.commit('user', { name })
      }
    },
    userEmail: {
      get: function () {
        return this.$store.state.userEmail
      },
      set: function (email) {
        this.$store.commit('user', { email })
      }
    }
  },
  watch: {
    user: {
      handler (newVal) {
        if (newVal && newVal.player_id) {
          this.initialPlayerId = newVal.player_id
          this.playerId = newVal.player_id
        }
      },
      immediate: true
    },
    searchName (val) {
      if (!val) {
        this.selectedMember = null
        this.playerId = null
      }
    }
  },
  apollo: {
    membersSearch: {
      query: gql`
        query membersSearch($clubhouse: Int!, $name: String) {
          membersSearch(clubhouse: $clubhouse, name: $name) {
            data {
              id
              name
              refId
            }
          }
        }
      `,
      variables() {
        return {
          clubhouse: parseInt(this.clubhouseId, 10),
          name: '%' + this.querySearchName + '%'
        }
      },
      skip() {
        return !this.clubhouseId || !this.querySearchName
      },
      result({ data }) {
        if (data && data.membersSearch) {
          this.searchResults = data.membersSearch.data
        }
      }
    },
    initialMember: {
      query: gql`
        query initialMemberSearch($clubhouse: Int!, $refId: String) {
          membersSearch(clubhouse: $clubhouse, refId: $refId) {
            data {
              id
              name
              refId
            }
          }
        }
      `,
      variables() {
        return {
          clubhouse: parseInt(this.clubhouseId, 10),
          refId: this.initialPlayerId
        }
      },
      skip() {
        return !this.clubhouseId || !this.initialPlayerId
      },
      update: data => data.membersSearch,
      result({ data }) {
        if (data && data.membersSearch && data.membersSearch.data.length > 0) {
          const member = data.membersSearch.data[0]
          this.selectedMember = member
          this.searchName = member.name
        }
      }
    }
  },
  methods: {
    onTyping: debounce(function (name) {
      this.querySearchName = name
      this.playerId = null
      this.selectedMember = null
    }, 300),
    onSelectMember(option) {
      if (option) {
        this.selectedMember = option
        this.playerId = option.refId
        this.searchName = option.name
      } else {
        this.selectedMember = null
        this.playerId = null
        this.searchName = ''
      }
    },
    submit () {
      if (this.playerId) {
        const regex = /^[0-9]{6}-[0-9]{2}$/
        if (!regex.test(this.playerId)) {
          this.$buefy.snackbar.open({
            duration: 4000,
            type: 'is-danger',
            message: 'BadmintonID skal være i formatet yymmdd-xx (f.eks. 010203-01)'
          })
          return
        }
      }
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
              player_id: this.playerId
            }
          }
        }
      ).then(() => {
        this.$buefy.snackbar.open({
          duration: 2000,
          type: 'is-success',
          message: `Din profil er nu opdateret`
        })
      }).catch(({graphQLErrors}) => {
        let errors = extractErrors(graphQLErrors)
        this.$buefy.snackbar.open({
          duration: 5000,
          type: 'is-danger',
          message: "Kunne ikke opdater din profil. " + errors.join(', ')
        })
      }).finally(() => {
        this.isLoading = false
      })
    }
  }
})
</script>

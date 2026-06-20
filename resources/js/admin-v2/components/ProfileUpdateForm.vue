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
            label="BadmintonID"
            message="Valgfrit. Dit BadmintonID (fra badmintonplayer.dk). Format ååmmdd-xx (f.eks. 010203-01)"
        >
            <b-input
                v-model="playerId"
                name="playerId"
                type="text"
                pattern="[0-9]{6}-[0-9]{2}"
                validationmessage="Formatet skal være yymmdd-xx (f.eks. 010203-01)"
            />
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
import FilePicker from '@/components/FilePicker.vue'
import CardComponent from '@/components/CardComponent.vue'
import gql from "graphql-tag";

export default defineComponent({
  name: 'ProfileUpdateForm',
  components: {
    CardComponent,
    FilePicker
  },
  inject: ['user'],
  data () {
    return {
      isLoading: false,
        playerId: null
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
          this.playerId = newVal.player_id
        }
      },
      immediate: true
    }
  },
  methods: {
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
            this.$buefy.snackbar.open(
                {
                    duration: 2000,
                    type: 'is-success',
                    message: `Din profil er nu opdateret`
                })
        }).catch(({graphQLErrors}) => {
            let errors = graphQLErrors.map(e => e.message)
            this.$buefy.snackbar.open({
                duration: 2000,
                type: 'is-danger',
                message: "Kunne ikke opdater din profil. "+errors.join(', ')
                                      })
        })
            .finally(() => {
            this.isLoading = false
        })
    }
  }
})
</script>

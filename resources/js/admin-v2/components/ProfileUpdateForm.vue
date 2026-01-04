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
  data () {
    return {
      isLoading: false
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
  methods: {
    submit () {
      this.isLoading = true
        this.$apollo.mutate(
            {
                mutation: gql`
                        mutation updateMe($input: UpdateMe!){
                            updateMe(input: $input){
                                id
                                name
                                email
                            }
                        }
                    `,
                variables: {
                    input: {
                        name: this.$store.state.userName,
                        email: this.$store.state.userEmail
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
        }).finally(() => {
            this.isLoading = false
        })
    }
  }
})
</script>

<template>
  <card-component
    title="Login"
    icon="lock"
  >
    <router-link
      slot="button"
      to="/"
      class="button is-small"
    >
      Dashboard
    </router-link>

    <form
      method="POST"
      @submit.prevent="submit"
    >
      <b-field label="E-mail Address">
        <b-input
          v-model="form.email"
          name="email"
          type="email"
          placeholder="daniel@gmail.com"
          required
        />
      </b-field>

      <b-field label="Password">
        <b-input
          v-model="form.password"
          type="password"
          name="password"
          placeholder="********"
          required
        />
      </b-field>

      <hr>

      <b-field grouped>
        <div class="control">
          <b-button
            native-type="submit"
            type="is-black"
            :loading="isLoading"
          >
            Login
          </b-button>
        </div>
      </b-field>
    </form>
  </card-component>
</template>

<script>
import { defineComponent } from 'vue'
import CardComponent from '@/components/CardComponent.vue'
import gql from "graphql-tag";
import {setAuthToken} from "../../../auth";

export default defineComponent({
  name: 'LoginView',
  components: { CardComponent },
  data () {
    return {
      isLoading: false,
      form: {
        email: null,
        password: null
      }
    }
  },
  methods: {
    submit () {
      this.isLoading = true
        this.$apollo.mutate(
            {
                mutation: gql`
                        mutation ($input: LoginInput){
                          login(input: $input){
                            access_token
                          }
                        }
                    `,
                variables: {
                    input: {
                        username: this.form.email,
                        password: this.form.password
                    }
                }
            }
        ).then(({data}) => {
            setAuthToken(data.login.access_token)
            this.$router.push({name: '/'})
        }).catch(({graphQLErrors}) => {
            this.$buefy.snackbar.open(
                {
                    duration: 6000,
                    type: 'is-danger',
                    message: `Forkert brugernavn eller adgangskode.`
                })
        }).finally(() => {
            this.isLoading = false
        })
    }
  }
})
</script>

<template>
  <card-component
    title="Ændre adgangskode"
    icon="lock"
  >
    <form @submit.prevent="submit">
      <b-field
        horizontal
        label="Nuværende adgangskode"
        message="Påkravet. Din nuværende adgangskode"
      >
        <b-input
          v-model="form.password_current"
          name="password_current"
          type="password"
          required
          autcomplete="current-password"
        />
      </b-field>
      <hr>
      <b-field
        horizontal
        label="Ny adgangskode"
        message="Påkravet. Ny adgangskode"
      >
        <b-input
          v-model="form.password"
          name="password"
          type="password"
          required
          autocomplete="new-password"
        />
      </b-field>
      <b-field
        horizontal
        label="Bekræft ny adgangskode"
        message="Påkravet. Bekræft ny adgangskoden"
      >
        <b-input
          v-model="form.password_confirmation"
          name="password_confirmation"
          type="password"
          required
          autocomplete="new-password"
        />
      </b-field>
      <hr>
      <b-field horizontal>
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
    </form>
  </card-component>
</template>

<script>
import { defineComponent } from 'vue'
import CardComponent from '@/components/CardComponent.vue'
import gql from "graphql-tag";
import {extractErrors} from "@/helpers.js";

export default defineComponent({
  name: 'PasswordUpdateForm',
  components: {
    CardComponent
  },
  data () {
    return {
      isLoading: false,
      form: {
        password_current: null,
        password: null,
        password_confirmation: null
      }
    }
  },
  methods: {
    submit () {
      this.isLoading = true

        this.$apollo.mutate(
            {
                mutation: gql`
                        mutation updatePassword($input: UpdatePassword!){
                            updatePassword(input: $input){
                                status
                                message
                            }
                        }
                    `,
                variables: {
                    input: {
                        old_password: this.form.password_current,
                        password: this.form.password,
                        password_confirmation: this.form.password_confirmation
                    }
                }
            }
        ).then(() => {
            this.$buefy.snackbar.open(
                {
                    duration: 2000,
                    type: 'is-success',
                    message: `Din adgangskode er nu opdateret`
                })
        }).catch(({graphQLErrors}) => {
            let errors = extractErrors(graphQLErrors);
            console.log(errors)
            this.$buefy.snackbar.open({
                duration: 2000,
                type: 'is-danger',
                message: errors.join(', ')
                                      })
        })
            .finally(() => {
            this.isLoading = false
        })
    }
  }
})
</script>

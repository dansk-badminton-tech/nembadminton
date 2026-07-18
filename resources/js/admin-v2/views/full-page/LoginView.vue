<template>
    <card-component
        title="Login"
        icon="lock"
    >
        <form
            method="POST"
            @submit.prevent="submit"
            dusk="login-form"
        >
            <b-field label="E-mail Address">
                <b-input
                    v-model="form.email"
                    dusk="email-input"
                    name="email"
                    type="email"
                    icon="email"
                    placeholder="daniel@gmail.com"
                    required
                />
            </b-field>

            <b-field label="Password">
                <b-input
                    v-model="form.password"
                    dusk="password-input"
                    type="password"
                    name="password"
                    icon="lock"
                    placeholder="********"
                    required
                />
            </b-field>
            <router-link to="/forgot-password">Glemt adgangskode?</router-link>
            <hr>

            <b-field grouped>
                <div class="control">
                    <b-button
                        dusk="login-button"
                        native-type="submit"
                        type="is-info"
                        :loading="isLoading"
                        expanded
                        size="is-medium"
                    >
                        <b-icon icon="login" size="is-small" class="mr-2"></b-icon>
                        Login
                    </b-button>
                </div>
            </b-field>

            <p class="has-text-centered has-text-grey is-size-7 my-3">eller</p>

            <social-login-buttons mode="login" />

            <hr class="my-5">

            <div class="has-text-centered">
                <p class="mb-3">Har du ingen bruger?</p>
                <b-button
                    tag="router-link"
                    to="/sign-up"
                    type="is-link"
                    :loading="isLoading"
                    outlined
                    expanded
                >
                    <b-icon icon="account-plus" size="is-small" class="mr-2"></b-icon>
                    Opret bruger
                </b-button>
            </div>
        </form>
    </card-component>
</template>

<style scoped>

.field.is-grouped .control {
    flex: 1;
}

@media screen and (max-width: 768px) {
    .card-component {
        margin: 1rem;
        max-width: none;
    }
}
</style>

<script>
import {defineComponent} from 'vue'
import CardComponent from '@/components/CardComponent.vue'
import SocialLoginButtons from '@/components/auth/SocialLoginButtons.vue'
import gql from "graphql-tag";
import {setAuthToken} from "../../../auth";
import ME from "../../../queries/me.gql";

export default defineComponent({
                                   name: 'LoginView',
                                   components: {CardComponent, SocialLoginButtons},
                                   data() {
                                       return {
                                           isLoading: false,
                                           form: {
                                               email: null,
                                               password: null
                                           }
                                       }
                                   },
                                   methods: {
                                       submit() {
                                           this.isLoading = true
                                           this.$apollo.mutate(
                                               {
                                                   mutation: gql`
                                                        mutation ($input: LoginInput){
                                                          login(input: $input){
                                                            access_token
                                                              user {
                                                                  clubhouse {
                                                                      id
                                                                  }
                                                              }
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
                                               this.$buefy.snackbar.open(
                                                   {
                                                       duration: 6000,
                                                       type: 'is-success',
                                                       message: `Velkommen tilbage!`
                                                   })
                                               setAuthToken(data.login.access_token)
                                               if(data.login.user.clubhouse === null){
                                                   this.$router.push({name: 'sign-up-finish'})
                                               }else{
                                                  this.$router.push({name: 'home', params: {clubhouseId: data.login.user.clubhouse.id}})
                                               }
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

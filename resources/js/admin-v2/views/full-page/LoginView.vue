<template>
    <card-component
        title="Login"
        icon="lock"
    >
        <form
            method="POST"
            @submit.prevent="submit"
        >
            <b-field label="E-mail Address">
                <b-input
                    v-model="form.email"
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
import gql from "graphql-tag";
import {setAuthToken} from "../../../auth";
import ME from "../../../queries/me.gql";

export default defineComponent({
                                   name: 'LoginView',
                                   components: {CardComponent},
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
                                       fetchUser() {
                                           this.$apollo.query({
                                                                  query: ME
                                                              })
                                               .then(({data}) => {
                                                   this.$store.commit('user', {
                                                       id: data.me.id,
                                                       name: data.me.name,
                                                       email: data.me.email,
                                                       avatar: 'https://api.dicebear.com/6.x/fun-emoji/svg',
                                                       clubhouse: data.me.clubhouse
                                                   })
                                               })
                                       },
                                       submit() {
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
                                               this.fetchUser()
                                               this.$router.push({name: 'home'})
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

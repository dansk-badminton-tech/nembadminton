<template>
    <fragment>
        <b-navbar class="container">
            <template slot="start">
                <b-navbar-item :to="{ path: '/team-fight/dashboard' }" tag="router-link">
                    <b-icon icon="home"></b-icon>
                    <span class="nav-item-span">Dashboard</span>
                </b-navbar-item>
                <b-navbar-item :to="{ path: '/team-fight/create' }" tag="router-link">
                    <b-icon icon="hand-rock"></b-icon>
                    <span class="nav-item-span">Holdkamp planner</span>
                </b-navbar-item>
            </template>
            <template slot="end">
                <b-navbar-item v-if="!$apollo.loading && !me" tag="div">
                    <div class="buttons">
                        <router-link :to="{ name: 'new-user-create' }" class="button is-primary">
                            <strong>Kom i gang</strong>
                        </router-link>
                        <router-link :to="{ name: 'login' }" class="button">
                            Login
                        </router-link>
                    </div>
                </b-navbar-item>
                <b-dropdown
                    v-if="!$apollo.loading && me"
                    append-to-body
                    aria-role="menu"
                    expanded>
                    <a
                        slot="trigger"
                        class="navbar-item"
                        role="button">
                        <span style="margin-right: 0.5rem">{{ me.name }}</span>
                        <b-icon icon="user-alt"></b-icon>
                    </a>
                    <b-dropdown-item aria-role="menuitem" custom>
                        Logged ind som <b>{{ me.name }}</b>
                    </b-dropdown-item>
                    <hr class="dropdown-divider">
                    <b-dropdown-item aria-role="menuitem" has-link>
                        <router-link :to="{ name: 'my-profile'}">
                            Min profil
                        </router-link>
                    </b-dropdown-item>
                    <b-dropdown-item aria-role="menuitem" v-on:click="logout">
                        <b-icon icon="sign-out-alt"></b-icon>
                        Log ud
                    </b-dropdown-item>
                </b-dropdown>
            </template>
        </b-navbar>
        <section class="section">
            <div class="container">
                <router-view :key="$route.path"></router-view>
            </div>
        </section>
    </fragment>
</template>

<script>
import gql from 'graphql-tag'

export default {
    data() {
        return {
            me: null
        }
    },
    apollo: {
        me: {
            query: gql`
                query {
                    me{
                        id
                        name
                        email
                    }
                }
            `,
            error(error) {
                this.me = null;
            },
        }
    },
    methods: {
        logout() {
            this.$apollo.mutate(
                {
                    mutation: gql`mutation{
                        logout{
                            status
                        }
                    }`
                })
                .finally(() => {
                    localStorage.removeItem('access_token')
                    this.$router.push({name: 'home'})
                })
        }

    }
}
</script>

<style scoped>
.nav-item-span {
    margin-left: 0.5rem;
}
</style>

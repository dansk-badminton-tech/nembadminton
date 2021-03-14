<template>
    <b-navbar class="container">
        <template slot="brand">
            <b-navbar-item :to="{ path: '/' }" tag="router-link">
                <b-icon icon="home" size="is-large"></b-icon>
            </b-navbar-item>
        </template>
        <template slot="start">
            <b-navbar-item :to="{ path: '/team-fight/dashboard' }" tag="router-link">
                <b-icon icon="hand-rock"></b-icon>
                <span class="nav-item-span">Mine Holdkampe</span>
            </b-navbar-item>
        </template>
        <template slot="end">
            <b-navbar-item v-if="!$apollo.loading && !me" tag="div">
                <div class="buttons">
                    <router-link :to="{ name: 'new-user-create' }" class="button">
                        <strong>Kom i gang</strong>
                    </router-link>
                    <router-link :to="{ name: 'login' }" class="button">
                        Login
                    </router-link>
                </div>
            </b-navbar-item>
            <b-dropdown v-if="!$apollo.loading && me" append-to-body aria-role="menu" expanded>
                <a slot="trigger" class="navbar-item" role="button">
                    <span style="margin-right: 0.5rem">{{ me.name.split(' ')[0] }}</span>
                    <b-icon icon="user-alt"></b-icon>
                </a>
                <b-dropdown-item aria-role="menuitem" custom>
                    Logged ind som <b>{{ me.name }}</b>
                </b-dropdown-item>
                <hr class="dropdown-divider">
                <b-dropdown-item aria-role="menuitem" has-link>
                    <router-link :to="{ name: 'my-profile'}">
                        <b-icon icon="user-alt"></b-icon>
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
</template>
<script>
import gql from 'graphql-tag'
import {isLoggedIn, logoutUser} from "../auth";
import ME from '../queries/me.gql'

export default {
    name: 'TopMenu',
    apollo: {
        me: {
            query: ME,
            skip: () => !isLoggedIn()
        }
    },
    mounted() {
        this.$root.$on('loggedIn', () => {
            this.$apollo.queries.me.skip = false
            this.$apollo.queries.me.refetch()
        })
        this.$root.$on('userUpdated', () => {
            this.$apollo.queries.me.skip = false
            this.$apollo.queries.me.refetch()
        })
    },
    methods: {
        logout() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                    mutation{
                        logout{
                            status
                        }
                    }`
                })
                .finally(() => {
                    logoutUser()
                    this.$router
                        .push({name: 'home'})
                        .catch(() => {
                        })
                        .finally(() => {
                            window.location.reload();
                        })
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

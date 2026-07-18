<template>
    <div id="app">
        <nav-bar :hide-clubhouse-nav="isPlayer"/>
        <aside-menu title="Nembadminton" :menu="computedMenu"/>
        <router-view/>
        <footer-bar/>
    </div>
</template>

<script>
import {defineComponent, computed} from 'vue'
import {adminMenu, playerMenu} from '@/menu.js'
import NavBar from '@/components/NavBar.vue'
import AsideMenu from '@/components/AsideMenu.vue'
import FooterBar from '@/components/FooterBar.vue'
import ME from "../../queries/me.gql";

export default defineComponent(
    {
        name: 'AppComponent',
        components: {
            FooterBar,
            AsideMenu,
            NavBar
        },
        data() {
            return {
                me: null,
                firstLoad: false,
            }
        },
        mounted() {
            this.$root.$on('loggedIn', () => {
                this.fetchMe()
            })
        },
        provide() {
            return {
                clubhouseId: computed(() => this.me?.clubhouse?.id ?? null),
                user: computed(() => this.me)
            }
        },
        created() {
            this.fetchMe()
        },
        computed: {
            computedMenu() {
                if (this.me === null) {
                    return []
                }

                if(this.me?.primaryRole?.name === 'player') {
                    return playerMenu(this.me?.clubhouse?.id ?? null);
                }

                return adminMenu(this.me?.clubhouse?.id ?? null)
            },
            isPlayer() {
                return this.me?.primaryRole?.name === 'player';
            }
        },
        methods: {
            fetchMe() {
                this.$apollo
                    .query({
                               query: ME,
                               fetchPolicy: "network-only"
                    })
                    .then(({data}) => {
                        this.me = data.me
                        this.$store.commit('user', {
                            id: data.me.id,
                            name: data.me.name,
                            email: data.me.email,
                            avatar: 'https://api.dicebear.com/6.x/fun-emoji/svg',
                            clubhouse: data.me.clubhouse
                        })

                        if (this.me.clubhouse === null) {
                            this.$router.push({name: 'sign-up-finish', query: {error: 'missingClubhouse'}})
                        } else if (this.me.player_id === null && this.me?.primaryRole?.name === 'player') {
                            this.$router.push({name: 'complete-profile', params: {clubhouseId: data.me.clubhouse.id}})
                        }
                        this.firstLoad = true;
                    })
                    .catch(({message}) => {
                        if (message.match(/Unauthenticated/i)) {
                            if(!this.firstLoad){
                                this.$router.push({name: 'login'})
                            }
                        } else {
                            this.$buefy.snackbar.open(
                                {
                                    duration: 6000,
                                    type: 'is-danger',
                                    message: `Kunne ikke hente din profil`
                                })
                        }
                    })
            }
        }
    })
</script>

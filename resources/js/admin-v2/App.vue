<template>
    <div id="app">
        <nav-bar/>
        <aside-menu :menu="menu"/>
        <router-view/>
        <footer-bar/>
    </div>
</template>

<script>
import {defineComponent} from 'vue'
import menu from '@/menu.js'
import NavBar from '@/components/NavBar.vue'
import AsideMenu from '@/components/AsideMenu.vue'
import FooterBar from '@/components/FooterBar.vue'
import ME from "../queries/me.gql";
import {isLoggedIn} from "../auth";

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
                menu
            }
        },
        mounted() {
            this.$root.$on('loggedIn', () => {
                this.fetchMe()
            })
        },
        created() {
            this.fetchMe()
        },
        methods: {
            fetchMe(){
                this.$apollo.query({
                                       query: ME
                                   })
                    .then(({data}) => {
                        this.$store.commit('user', {
                            name: data.me.name,
                            email: data.me.email,
                            avatar: 'https://api.dicebear.com/6.x/fun-emoji/svg'
                        })
                    }).catch(({message}) => {
                    if(message.match(/Unauthenticated/i)){
                        //this.$router.push({name: 'login'})
                    }else{
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

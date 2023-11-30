<template>
    <div id="app">
        <nav-bar/>
        <aside-menu :menu="menu"/>
        <router-view/>
        <footer-bar/>
        <Kustomer :params="this.params" :labels="this.labels"/>
    </div>
</template>

<script>
import {defineComponent} from 'vue'
import menu from '@/menu.js'
import NavBar from '@/components/NavBar.vue'
import AsideMenu from '@/components/AsideMenu.vue'
import FooterBar from '@/components/FooterBar.vue'
import Kustomer from './components/Kustomer/Kustomer.vue'
import ME from "../queries/me.gql";

export default defineComponent(
    {
        name: 'AppComponent',
        components: {
            FooterBar,
            AsideMenu,
            NavBar,
            Kustomer
        },
        data() {
            return {
                menu,
                params: {"icon": "\/vendor\/kustomer\/assets\/icon.svg", "close": "\/vendor\/kustomer\/assets\/close.svg", "logo": "\/vendor\/kustomer\/assets\/logo.svg", "colors": {"primary": "rgb(222, 48, 42)"}, "feedbacks": {"like": {"icon": "\/vendor\/kustomer\/assets\/like.svg"}, "dislike": {"icon": "\/vendor\/kustomer\/assets\/dislike.svg"}, "suggestion": {"icon": "\/vendor\/kustomer\/assets\/idea.svg"}}, "screenshot": false},
                labels: {"tooltip": "Giv feedback", "title": "Help os med at blive bedre!", "success": "Tak for din feedback!", "placeholder": "Skriv din feedback her...", "button": "Send feedback", "feedbacks": {"like": {"title": "Noget var godt", "label": "Hvad kunne du lide?"}, "dislike": {"title": "Noget var dårligt", "label": "Hvad kunne du ikke lide ?"}, "suggestion": {"title": "Jeg har et forslag", "label": "Hvad er dit forslag ?"}}}
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
            fetchMe() {
                this.$apollo.query({
                                       query: ME
                                   })
                    .then(({data}) => {
                        this.$store.commit('user', {
                            id: data.me.id,
                            name: data.me.name,
                            email: data.me.email,
                            avatar: 'https://api.dicebear.com/6.x/fun-emoji/svg'
                        })
                    }).catch(({message}) => {
                    if (message.match(/Unauthenticated/i)) {
                        //this.$router.push({name: 'login'})
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

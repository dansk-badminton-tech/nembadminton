<script>
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import CardComponent from "@/components/CardComponent.vue";
import MyClubs from "@/views/club-house/MyClubs.vue";
import MemberList from "@/views/club-house/MemberList.vue";

export default {
    name: "ClubHouseDashboard" ,
    components: {MemberList, MyClubs, CardComponent, HeroBar, TitleBar},
    data(){
        return {
            titleStack: ['Admin', 'Klubhus'],
            name: '',
            email: '',
            isLoading: false,
        }
    },
    methods: {
        submit(){
            this.isLoading = true;
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Klubhus
        </hero-bar>
        <section class="section is-main-section">
            <card-component
                title="Rediger Klubhus"
                icon="home"
            >
                <form @submit.prevent="submit">
                    <b-field
                        horizontal
                        label="Name"
                    >
                        <b-input
                            v-model="name"
                            name="name"
                            placeholder="SAIF"
                            required
                        />
                    </b-field>
                    <b-field
                        horizontal
                        label="E-mail"
                    >
                        <b-input
                            v-model="email"
                            name="email"
                            type="email"
                            placeholder="info@nembadminton.dk"
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
            <member-list />
            <my-clubs/>
        </section>
    </div>
</template>

<style scoped>

</style>

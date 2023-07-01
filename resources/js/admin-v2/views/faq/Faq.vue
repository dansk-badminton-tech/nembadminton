<template>
    <div>
        <title-bar :title-stack="titleStack" />
        <hero-bar :has-right-visible="false">
            FAQ
        </hero-bar>
        <section class="section is-main-section">
            <fragment v-for="faq in faqs" :key="faq.title">
                <strong>{{faq.title}}</strong>
                <p v-html="faq.text"></p>
                <hr>
            </fragment>
        </section>
    </div>
</template>

<script>
import HeroBar from "../../components/HeroBar.vue";
import TitleBar from "../../components/TitleBar.vue";
import {faqs} from "./faqs";

export default {
    name: "Faq",
    components: {TitleBar, HeroBar},
    data() {
        return {
            titleStack: ['Admin', 'FAQ'],
            isOpen: 0,
            faqs: faqs
        }
    }

}
</script>

<style scoped>

</style>

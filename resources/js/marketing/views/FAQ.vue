<script setup>
import {faqs} from "../../admin-v2/views/faq/faqs";

const faqsArr = faqs
</script>

<template>
    <div class="section-light skills" id="faq">
        <div class="container">
            <div class="columns is-multiline">
                <div class="column is-12 about-me">
                    <h1 class="title has-text-centered section-title">FAQ</h1>
                </div>
                <div
                    class="column is-12"
                    data-aos="fade-in"
                    data-aos-easing="linear"
                >
                    <template v-for="faq in faqsArr">
                        <strong>{{faq.title}}</strong>
                        <p v-html="faq.text"></p>
                        <hr>
                    </template>
<!--                    <h1>Hvordan tjekker jeg en opstilling?</h1>-->
<!--                    <p></p>-->
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>

<script>
import Services from "./views/Services.vue";
import Skills from "./views/Skills.vue";
import WorkContent from "./views/WorkContent.vue";
import Contact from "./views/Contact.vue";
import FAQ from "./views/FAQ.vue";

export default {
    name: "App",
    components: {FAQ, Contact, WorkContent, Skills, Services}
}
</script>

<template>
    <div id="app">
        <!-- Begin Scroll Up Button -->

        <form action="#home">
            <button id="toTop" title="Go to top">
                <i class="fas fa-angle-up"></i>
            </button>
        </form>
        <!-- End Scroll Up Button -->

        <!-- Begin Header -->
        <div class="header-wrapper" id="home">
            <!-- Begin Hero -->
            <section class="hero is-large">
                <!-- Begin Hero Content-->
                <div class="hero-body">
                    <div class="container has-text-centered">
                        <h1 class="subtitle"></h1>
                        <h2 class="title">Nembadminton</h2>
                        <h1 class="subtitle profession">Holdturnering - nemt og bekymringsfrit</h1>
                        <a href="/app/home" class="button is-medium">
                            Login
                        </a>
                        <a href="/app/sign-up" class="button is-medium">
                            Opret dig
                        </a>
                    </div>
                </div>
                <!-- End Hero Content-->
            </section>
            <!-- End Hero -->
        </div>
        <!-- End Header -->

        <!-- Begin Main Content -->
        <div class="main-content">
            <!-- Begin About Me Section -->
            <div class="section-light about-me" id="about-me">
                <div class="container">
                    <div class="column is-12 about-me">
                        <h1 class="title has-text-centered section-title">Sammensæt din holdkamp på ingen tid</h1>
                    </div>
                    <div class="columns is-multiline">
                        <div
                            class="column is-6 has-vertically-aligned-content"
                            data-aos="fade-right"
                        >
                            <p class="is-larger">
                                <strong>
                                    Spillerer og ranglister fra badmintonplayer.dk. Tjekker løbende om du overholder reglerne.
                                </strong>
                            </p>
                        </div>
                        <div class="column is-6 right-image " data-aos="fade-left">
                            <img
                                class="is-rounded"
                                src="../../assets/teamfight-planning-landing-2.png"
                                alt=""
                            />
                        </div>
                    </div>
                </div>
            </div>
            <!-- End About Me Content -->
            <div class="section-dark resume">
                <div class="container">
                    <div
                        class="columns is-multiline"
                        data-aos="fade-in"
                        data-aos-easing="linear"
                    >
                        <div class="column is-12 about-me">
                            <h1 class="title has-text-centered section-title">
                                Altid opdateret med nyeste data fra badmintonplayer
                            </h1>
                        </div>
                        <div class="column is-10 has-text-centered is-offset-1">
                            <h2 class="subtitle">Hver nat importer nembadminton de nyeste data fra badmintonplayer.</h2>
                            <a href="/app/login" class="button">
                                Prøv nu
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <Services />
            <FAQ />
<!--            <WorkContent />-->
<!--            <Contact/>-->
        </div>
        <!-- End Main Content -->

        <!-- Begin Footer -->
        <div class="footer">
            <p>
                <strong class="white">Nembadminton</strong> af <a target="_blank" href="https://github.com/dansk-badminton-tech">Den danske IT badminton gruppe</a>.
            </p>
        </div>
        <!-- End Footer -->
    </div>
</template>

<style scoped>

</style>

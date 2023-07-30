<script>
import Services from "./views/Services.vue";
import Skills from "./views/Skills.vue";
import WorkContent from "./views/WorkContent.vue";
import Contact from "./views/Contact.vue";
import FAQ from "./views/FAQ.vue";
import AboutUS from "./views/AboutUS.vue";

export default {
    name: "App",
    components: {AboutUS, FAQ, Contact, WorkContent, Skills, Services}
}
</script>

<template>
    <div id="app">
        <!-- Begin Scroll Up Button -->

<!--        <form action="#home">-->
<!--            <button id="toTop" title="Go to top">-->
<!--                <i class="fas fa-angle-up"></i>-->
<!--            </button>-->
<!--        </form>-->
        <!-- End Scroll Up Button -->

        <!-- Begin Header -->
        <div class="header-wrapper" id="home">
            <!-- Begin Hero -->
            <section class="hero is-large">
                <!-- Begin Hero Content-->
                <div class="hero-body">
                    <div class="container has-text-centered">
                        <h2 class="title">Nembadminton</h2>
                        <h1 class="subtitle profession">Holdturnering - nemt og bekymringsfrit</h1>
                        <a href="/app/home" class="button is-medium">
                            Login
                        </a>
                        <a href="/app/sign-up" class="button is-medium">
                            Kom i gang
                        </a>
                    </div>
                </div>
                <!-- End Hero Content-->
                <!-- Begin Hero Menu -->
                <div class="hero-foot ">
                    <div class="hero-foot--wrapper">
                        <div class="columns">
                            <div class="column is-12 hero-menu-desktop has-text-centered is-fixed-top">
                                <ul>
                                    <li>
                                        <a href="#about-the-tool">Om værktøjet</a>
                                    </li>
                                    <li>
                                        <a href="#services">Features</a>
                                    </li>
                                    <li>
                                        <a href="#about-us">Om os</a>
                                    </li>
                                    <li>
                                        <a href="#faq">FAQ</a>
                                    </li>
                                    <li>
                                        <a href="/app/login">Login</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Hero Menu -->
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
<!--             End About Me Content -->
            <div class="section section-dark resume" id="about-the-tool">
<!--                <div class="container">-->
                    <div class="columns is-multiline">
                        <div class="column is-12 about-me">
                            <h1 class="title has-text-centered section-title">
                                Om værktøjet
                            </h1>
                        </div>
                        <div class="column is-12 has-text-centered">
                            <p>Værktøjet nembadminton.dk er udviklet som et bidrag til badmintonsporten i Danmark for at gøre det nemmere at være træner og frivillig. Badminton Danmark har hverken valideret eller kontrolleret beregningerne. Alle udregninger er lavet på baggrund af reglementet for DH-turneringen. Det er altid jer som klub og bruger, der har ansvaret for, at opstillingerne er korrekte, og I har selv ansvaret for at kontrollere i forhold til ranglister på badmintonplayer og DH-reglementet. Værktøjet hjælper kun med opstillinger internt og mellem hold i forhold til ranglistepoint. Hvis spillere f.eks. har karantæne, ses det ikke her.</p>
                            <a href="/app/login" class="mt-2 button">
                                Forstået - Kom i gang
                            </a>
                        </div>
                    </div>
<!--                </div>-->
            </div>
            <Services />
            <AboutUS />
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

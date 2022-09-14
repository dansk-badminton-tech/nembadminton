<template>
    <section class="hero is-info is-fullheight">
        <div class="hero-head">
            <TopMenu/>
        </div>
        <div class="hero-body">
            <div class="container">
                <div class="columns is-vcentered">
                    <div class="column is-two-fifths">
                        <h1 class="title is-1">Planlæg din holdkamp nemt og bekymringsfrit.</h1>
                        <h2 class="subtitle">Spiller og ranglister fra badmintonplayer.dk. Tjekker løbene om du overholder reglerne</h2>
                    </div>
                    <div class="column">
                        <figure class="image">
                            <img alt="" src="../../assets/teamfight-planning-landing-2.png">
                        </figure>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--        <div class="section">-->
    <!--            <div class="container">-->
    <!--                <div class="tile is-ancestor has-text-centered">-->
    <!--                    <div class="tile is-parent">-->
    <!--                        <article class="tile is-child box">-->
    <!--                            <p class="title mt-4">Data fra badmintonplayer</p>-->
    <!--                            <p class="subtitle">Data opdateres hver nat</p>-->
    <!--                        </article>-->
    <!--                    </div>-->
    <!--                    <div class="tile is-parent">-->
    <!--                        <article class="tile is-child box">-->
    <!--                            <p class="title">Gratis</p>-->
    <!--                            <p class="subtitle">Kvit og frit, ingen betaling</p>-->
    <!--                        </article>-->
    <!--                    </div>-->
    <!--                    <div class="tile is-parent">-->
    <!--                        <article class="tile is-child box">-->
    <!--                            <p class="title">Nemmere hverdag</p>-->
    <!--                            <p class="subtitle">Værktøjer som gør badmintonuniverset nemmere</p>-->
    <!--                        </article>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--        <div class="hero is-info is-small">-->
    <!--            <div class="hero-body">-->
    <!--                <div class="container">-->
    <!--                    <div class="columns">-->
    <!--                        <div class="column is-6">-->
    <!--                            <figure class="image is-16by9">-->
    <!--&lt;!&ndash;                                <iframe allowfullscreen class="has-ratio" frameborder="0" height="360" src="https://www.youtube.com/embed/YE7VzlLtp-4" width="640"></iframe>&ndash;&gt;-->
    <!--                            </figure>-->
    <!--                        </div>-->
    <!--                        <div class="column is-6">-->
    <!--                            <p class="title has-text-centered">Holdkamp</p>-->
    <!--                            <div class="content">-->
    <!--                                <p>Hjælper med sammensætning af hold til holdkampe, nem deling til alle spillere.</p>-->
    <!--                                <ul>-->
    <!--                                    <li>Planlæg holdkamp</li>-->
    <!--                                    <li>Nem deling med spillere via link</li>-->
    <!--                                </ul>-->
    <!--                            </div>-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--        <div class="section">-->
    <!--            <div class="container">-->
    <!--                <div class="columns">-->
    <!--                    <div class="column is-6">-->
    <!--                        <p class="title has-text-centered">Data fra badmintonplayer</p>-->
    <!--                        <div class="content">-->
    <!--                            <p>Hver nat opdateres alle spiller med data fra badmintonplayer. Det sikre du nemt og hurtigt kan sammensæt et hold helt efter reglerne.</p>-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <div class="column is-6">-->
    <!--                        <figure class="image is-16by9">-->
    <!--                            &lt;!&ndash;                            <iframe allowfullscreen class="has-ratio" frameborder="0" height="360" src="https://www.youtube.com/embed/YE7VzlLtp-4" width="640"></iframe>&ndash;&gt;-->
    <!--                        </figure>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--        <div class="section">-->
    <!--            <div class="container">-->
    <!--                <div class="column has-text-centered is-12 mb-6">-->
    <!--                    <p class="title">-->
    <!--                        Udvikleren bag-->
    <!--                    </p>-->
    <!--                </div>-->
    <!--                <div class="columns is-multiline">-->
    <!--                    <div class="column is-6 has-vertically-aligned-content">-->
    <!--                        <p class="is-size-4"><strong>Daniel Fly Nygaard, Programmør/DevOps</strong></p>-->
    <!--                        <br/>-->
    <!--                        <p>-->
    <!--                            Har altid gerne vil lave noget, som var med til at gør badminton verden bedre. Kombineret sammen med mit ønske om at blive iværksætter har jeg lavet denne side. Hvis man har ideer til nye værktøjer eller forbedringer er man velkommen til at kontakte mig-->
    <!--                        </p>-->
    <!--                        <br/>-->
    <!--                        <div class="is-divider"></div>-->
    <!--                        <div class="columns about-links">-->
    <!--                            <div class="column">-->
    <!--                                <p>-->
    <!--                                    <strong>LinkedIn</strong>-->
    <!--                                </p>-->
    <!--                                <p>-->
    <!--                                    <a href="https://www.linkedin.com/in/daniel-fly-nygaard-ab67818b/">LinkedIn</a>-->
    <!--                                </p>-->
    <!--                            </div>-->
    <!--                            <div class="column">-->
    <!--                                <p>-->
    <!--                                    <strong>Email</strong>-->
    <!--                                </p>-->
    <!--                                <p>-->
    <!--                                    <a href="mailto:danielflynygaard@gmail.com">danielflynygaard@gmail.com</a>-->
    <!--                                </p>-->
    <!--                            </div>-->
    <!--                        </div>-->
    <!--                    </div>-->
    <!--                    <div class="column is-6">-->
    <!--                        <figure class="image is-square">-->
    <!--                            <img alt="" class="is-rounded" src="../../assets/daniel_fly_nygaard.jpeg">-->
    <!--                        </figure>-->
    <!--                    </div>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
</template>

<script>
import TopMenu from "./TopMenu";

export default {
    name: "Home",
    components: {TopMenu}
}
</script>

<style scoped>

</style>

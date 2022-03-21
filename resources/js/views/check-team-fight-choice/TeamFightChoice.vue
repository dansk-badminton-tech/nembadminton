<template>
    <div class="columns">
        <div class="column">
            <h1 class="title">Ny version af "Tidligere holdkampe"</h1>
            <div class="columns">
                <div class="column">
                    <div class="box">
                        <div class="content">
                            <p>Begrænsninger (indtil videre):</p>
                            <ul>
                                <li>Viser kun holdkampe mindre end 1 måned</li>
                                <li>Viser kun hold fra danmarksserien og op</li>
                                <li>Kun en rangliste dvs ingen specifik rangliste pr hold</li>
                            </ul>
                        </div>
                        <b-button tag="router-link" to="/team-fight-v2/check" size="is-large">
                            Gå til ny
                        </b-button>
                    </div>
                </div>
                <div class="column">
                    <div class="box">
                        <div class="content">
                            <p>Begrænsninger:</p>
                            <ul>
                                <li>Ingen</li>
                            </ul>
                        </div>
                        <b-button tag="router-link" to="/team-fight/check" size="is-large">
                            Gå til gammel
                        </b-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "TeamFightChoice"
}
</script>

<style scoped>

</style>

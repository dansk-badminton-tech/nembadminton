<template>
    <div class="columns">
        <div class="column has-text-centered">
            <h1 class="title">Ny opdatering tilgængelig</h1>
            <h2 class="subtitle">Den nye har 2 begrænsninger (indtil videre): 1. Viser kun holdkampe mindre end 1 måned gammel. 2. viser kun hold fra danmarksserien og op</h2>
            <div class="field has-addons is-justify-content-center">
                <p class="control">
                    <b-button tag="router-link" to="/team-fight-v2/check" size="is-large">
                        Gå til ny
                    </b-button>
                </p>
                <p class="control">
                    <b-button tag="router-link" to="/team-fight/check" size="is-large">
                        Gå til gammel
                    </b-button>
                </p>
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

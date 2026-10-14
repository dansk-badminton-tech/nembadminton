<template>
    <!-- PROTOTYPE (#227): what each variant means for the surfaces around the lineup. -->
    <div class="prototype-notes mt-4" dusk="reserve-prototype-notes">
        <p class="mb-1"><strong>Prototype {{ variant }}: {{ title }}</strong>
            <span class="is-size-7 has-text-grey">· gemmes ikke, glemmes ved genindlæsning</span></p>
        <ul class="is-size-7">
            <template v-if="variant === 'A'">
                <li><b>Markering:</b> klik <b-icon icon="account-switch" size="is-small"/> på en opstillet spiller, eller brug "Markér reserve" under holdet.</li>
                <li><b>Kategoriplads:</b> uændret. Reserven står på en almindelig plads og har sin kategoriplads.</li>
                <li><b>Sæsonhistorik:</b> pladsen vises som normalt, med en note om at spilleren er reserve.</li>
                <li><b>Ledige spillere:</b> uændret. Reserven står allerede på holdet.</li>
                <li><b>"Optaget på":</b> uændret. Reserven er en opstillet spiller.</li>
                <li><b>Lavere hold:</b> en spiller fra et lavere hold kan ikke markeres. Personen skal flyttes op og mister sin plads dernede.</li>
            </template>
            <template v-else>
                <li><b>Markering:</b> søg i "Reserver" på holdet (kun spillere fra lavere hold), eller klik <b-icon icon="account-switch" size="is-small"/> på en ledig spiller i listen.</li>
                <li><b>Sæsonhistorik:</b> uændret. Reserver vises ikke, og en reserve-række har ingen sæsonhistorik-knap.</li>
                <li><b>Ledige spillere:</b> en ledig spiller, der bliver reserve, er ikke længere ledig og forsvinder fra listen. En note over listen siger det.</li>
                <li><b>"Optaget på":</b> reserven tæller som optaget i parallelle holdrunder med samme runde. Reserve-rækken viser spillerens eget "Optaget på"-mærke.</li>
                <li><b>Lavere hold:</b> en spiller fra et lavere hold beholder sin plads og får mærket "Også reserve på Hold N" dér.</li>
                <li><b>CSV:</b> reserverne kommer efter holdets kategorier. Se forhåndsvisningen i Del → CSV.</li>
                <li><b>Offentligt link:</b> en "Reserve"-række på hvert holdkort.
                    <router-link :to="{name: 'team-fight-public-view', params: {teamUUID: teamRoundId}, query: {variant: 'B'}}" dusk="prototype-public-link">
                        Åbn det offentlige link i denne fane</router-link>
                    (et nyt vindue kender ikke reserverne, for de gemmes ikke).</li>
            </template>
        </ul>
    </div>
</template>
<script>
import {VARIANTS} from "./reserve-prototype.js";

export default {
    name: 'ReservePrototypeNotes',
    props: {
        variant: {type: String, required: true},
        teamRoundId: {type: String, default: null}
    },
    computed: {
        title() {
            return VARIANTS[this.variant]
        }
    }
}
</script>
<style scoped>
.prototype-notes {
    border: 2px dashed #8b5cf6;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    background: #faf5ff;
}
.prototype-notes ul {
    list-style: disc;
    margin-left: 1.25rem;
}
</style>

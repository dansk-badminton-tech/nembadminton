<script>
import {TeamFightHelper} from "./teams";

export default {
    name: "AddCustomSquadModal",
    props: ['addSquad'],
    data() {
        return {
            isModalActive: false,
            mix: 0,
            womenSingles: 0,
            womenDoubles: 0,
            mensSingles: 0,
            mensDoubles: 0,
            loading: false
        };
    },
    methods: {
        submitSquad() {
            // Call generateSquad with the form data
            const squad = TeamFightHelper.generateSquad(this.mix, this.womenSingles, this.womenDoubles, this.mensSingles, this.mensDoubles);
            this.loading = true
            this.addSquad(squad).then(() => {
                this.$emit('close')
            })
        },
    },
}
</script>

<template>
    <form @submit.prevent="submitSquad">
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Opret brugerdefineret hold</p>
                <button
                    type="button"
                    class="delete"
                    @click="$emit('close')"/>
            </header>
            <section class="modal-card-body">
                <div class="content">
                    <b-field label="Mix Doubles (MD)">
                        <b-input v-model.number="mix" type="number" min="0"></b-input>
                    </b-field>
                    <b-field label="Dame Singles (DS)">
                        <b-input v-model.number="womenSingles" type="number" min="0"></b-input>
                    </b-field>
                    <b-field label="Dame Doubles (DD)">
                        <b-input v-model.number="womenDoubles" type="number" min="0"></b-input>
                    </b-field>
                    <b-field label="Herre Singles (HS)">
                        <b-input v-model.number="mensSingles" type="number" min="0"></b-input>
                    </b-field>
                    <b-field label="Herre Doubles (HD)">
                        <b-input v-model.number="mensDoubles" type="number" min="0"></b-input>
                    </b-field>
                </div>
            </section>
            <footer class="modal-card-foot">
                <b-button
                    :loading="this.loading"
                    native-type="submit"
                    label="Gem"/>
                <b-button
                    :loading="this.loading"
                    @click="$emit('close')"
                    label="Luk"/>
            </footer>
        </div>
    </form>
</template>

<style scoped>

</style>

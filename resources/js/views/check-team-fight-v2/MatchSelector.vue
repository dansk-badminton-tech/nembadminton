<template>
    <div>
        <h1 class="title">Hold kampe (14 dage tilbage)</h1>
        <h2 class="subtitle">Vælge den specifikke hold kamp. Husk ranglisten skal passe med holdkamps
            runden</h2>
        <b-field>
            <Teams @input="onInput" :teams="teams"></Teams>
        </b-field>
    </div>
</template>
<script>
import Teams from "./Teams"

export default {
    name: 'MatchSelector',
    components: {Teams},
    props: {
        value: Array,
        teams: Array
    },
    methods: {
        onInput(e){
            this.$emit('input', e.target.value)
        }
    }
}
</script>

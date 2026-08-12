<template>
    <div>
        <b-button v-show="!showDropDown" @click.prevent="toggleRankingDropDown">Vælg anden rangliste</b-button>
        <RankingListDropdown v-show="showDropDown" :season="season" @input="handleInput" :use-system-rankings="true"></RankingListDropdown>
    </div>
</template>

<script>
import RankingListDropdown from "../../components/ranking-list-dropdown/RankingListDropDown.vue";
export default {
    name: "OptionalRanking",
    emits: ['input'],
    components: {RankingListDropdown},
    props: ['value', 'season'],
    data(){
        return {
            showDropDown: false
        }
    },
    methods: {
        handleInput(value) {
            this.$emit('input', value)
        },
        toggleRankingDropDown(){
            this.showDropDown = true
        }
    },
}
</script>

<style scoped>

</style>

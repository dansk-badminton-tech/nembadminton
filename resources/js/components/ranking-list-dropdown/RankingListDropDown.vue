<template>
    <b-select expanded placeholder="Vælge rangliste" @input="handleInput">
        <option v-for="date in calculateDates" :value="date.value">{{ date.value }}</option>
    </b-select>
</template>

<script>
export default {
    name: 'RankingListDropdown',
    props: ['value', 'season'],
    methods: {
        handleInput(value) {
            this.$emit('input', value)
        }
    },
    computed: {
        calculateDates() {
            let dates = []
            const dateObject = new Date()
            dateObject.setFullYear(this.season, 6, 1)
            for (let month = 0; month < 10; month++) {
                if(dateObject <= Date.now()){
                    dates.push({value: dateObject.toLocaleDateString('en-CA')});
                    dateObject.setMonth(dateObject.getMonth()+1)
                }
            }
            return dates;
        }
    }
};
</script>

<template>
    <b-datepicker
        v-model="version"
        :first-day-of-week="1"
        :max-date="new Date()"
        :show-week-number="true"
        :unselectable-days-of-week="[0,2,4,6]"
        icon="calendar-alt"
        placeholder="Vælge rangliste"
        trap-focus
    />
</template>
<script>
export default {
    name: 'RankingListDatePicker',
    props: ['value'],
    computed: {
        version: {
            get() {
                return this.value
            },
            set(newValue) {
                this.$emit('input', newValue);
            }
        }
    }
}
</script>

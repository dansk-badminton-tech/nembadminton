<template>
    <b-select expanded placeholder="Vælge rangliste" @input="handleInput">
        <option value="2021-10-01">2021-10-01</option>
        <option value="2021-09-01">2021-09-01</option>
        <option value="2021-08-01">2021-08-01</option>
        <option value="2021-07-01">2021-07-01</option>
        <option value="2020-12-01">2020-12-01</option>
        <option value="2020-11-01">2020-11-01</option>
        <option value="2020-10-01">2020-10-01</option>
        <option value="2020-09-01">2020-09-01</option>
        <option value="2020-08-01">2020-08-01</option>
        <option value="2020-07-01">2020-07-01</option>
    </b-select>
</template>

<script>
    export default {
        name: 'RankingListDropdown',
        props: ['value'],
        methods: {
            handleInput(value) {
                this.$emit('input', value)
            }
        },
    };
</script>

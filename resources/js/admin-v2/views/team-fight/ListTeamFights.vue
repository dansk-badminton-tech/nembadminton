<template>
    <b-table :data="teams" :loading="loading">
        <b-table-column v-slot="props" field="id" label="Navn">
            <router-link v-bind:to="'/team-fight/'+props.row.id+'/edit'">{{ props.row.name }}</router-link>
        </b-table-column>
        <b-table-column v-slot="props" field="gameDate" label="Spilledato">
            {{ props.row.gameDate }}
        </b-table-column>
        <b-table-column v-slot="props" field="version" label="Rangliste">
            {{ props.row.version }}
        </b-table-column>
        <b-table-column v-slot="props" field="updatedAt" label="Opdateret">
            {{ props.row.updatedAt }}
        </b-table-column>
        <b-table-column v-slot="props" label="Funktioner">
            <b-button
                size="is-small"
                tag="router-link"
                type="is-link"
                v-bind:to="'/team-fight/'+props.row.id+'/edit'">Rediger
            </b-button>
        </b-table-column>
    </b-table>
</template>
<script>
export default {
    name: 'ListTeamFights',
    props: {
        teams: {},
        loading: Boolean
    }
}
</script>

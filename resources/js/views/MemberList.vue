<template>
    <fragment>
        <h1 class="title">Hændelseslog</h1>
        <h2 class="subtitle">Følge med i hvordan systemet synkroniser med data fra badmintonplayer.dk</h2>
        <b-table
            :data="logs.data"
            :loading="$apollo.queries.logs.loading"
            :per-page="10"
            :total="logs.paginatorInfo.total"
            backend-pagination
            class="mb-3"
            paginated
            @page-change="onPageChange">
            <b-table-column v-slot="props" field="log" label="Log">
                {{ props.row.log }}
            </b-table-column>
            <b-table-column v-slot="props" field="component" label="Komponent">
                {{ props.row.component }}
            </b-table-column>
            <b-table-column v-slot="props" field="createdAt" label="Tidspunkt">
                {{ props.row.createdAt }}
            </b-table-column>
        </b-table>
    </fragment>
</template>
<script>
import gql from "graphql-tag"

export default {
    name: 'MemberList',
    data() {
        return {
            page: 1,
            logs: {
                data: [],
                paginatorInfo: {}
            },
            polling: 2000
        }
    },
    apollo: {
        logs: {
            query: gql`
                query logs($page: Int!){
                    logs(page: $page){
                        paginatorInfo{
                            total
                            lastPage
                        }
                        data{
                            id
                            log
                            component
                            createdAt
                        }
                    }
                }
            `,
            pollInterval: 2000,
            variables() {
                return {
                    page: this.page
                }
            }
        },
    },
    methods: {
        onPageChange(page) {
            this.page = page
        }
    }
}
</script>

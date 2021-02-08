<template>
    <div>
        <b-field :label="$t('roundsGenerator.findClub')">
            <b-autocomplete
                icon="search"
                v-if="!selectedClub"
                v-model="querySearchName"
                :clear-on-select="true"
                :clearable="true"
                :data="this.clubs.data"
                :keep-first="true"
                :loading="$apollo.queries.clubs.loading"
                :placeholder="$t('roundsGenerator.findClubPlaceholder')"
                field="name1"
                @select="setClub"
                @typing="searchClubs"
            >
                <template slot="empty">No results for {{ querySearchName }}</template>
            </b-autocomplete>
        </b-field>
        <div class="field">
            <b-tag v-if="selectedClub"
                   aria-close-label="Fjern"
                   attached
                   closable
                   close-type='is-danger'
                   size="is-large"
                   @close="unsetClub">
                {{ selectedClub.name1 }}
            </b-tag>
        </div>
    </div>
</template>

<script>
import {debounce} from "../../helpers";
import gql from 'graphql-tag'

export default {
    name: "ClubSearch",
    props: {
        selectClub: Function
    },
    methods: {
        setClub(option) {
            if (!option) {
                return
            }
            if (typeof this.selectClub === 'function') {
                this.selectClub(parseInt(option.id))
            }
            this.selectedClub = option
        },
        unsetClub() {
            this.selectedClub = null
            this.selectClub(null)
        },
        searchClubs: debounce(function (name) {
            if (!name.length) {
                this.clubs.data = []
                return
            }
            this.searchName = name;
            this.$apollo.queries.clubs.refresh();
        }, 200)
    },
    data() {
        return {
            querySearchName: '',
            searchName: '',
            selectedClub: null,
            clubs: {
                data: []
            }
        }
    },
    apollo: {
        clubs: {
            query: gql`query ClubsSearch($name: String){
                      clubsSearch(name: $name, orderBy: { column: NAME1, order: ASC }) {
                        data {
                          id
                          name1
                        }
                        paginatorInfo {
                          count
                          total
                        }
                      }
                    }
                `,
            update: data => data.clubsSearch,
            variables() {
                return {
                    name: '%' + this.searchName + '%'
                }
            }
        }
    }
}
</script>

<style scoped>

</style>

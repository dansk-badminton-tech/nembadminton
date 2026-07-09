<template>
    <b-field
        label="Spiller"
        :message="message"
    >
        <b-autocomplete
            v-model="searchName"
            :data="searchResults"
            placeholder="Søg efter dit navn..."
            field="name"
            :loading="$apollo.queries.membersSearch.loading"
            :clearable="true"
            @typing="onTyping"
            @select="onSelectMember"
            keep-first
        >
            <template v-slot:default="{ option }">
                <div class="media">
                    <div class="media-content">
                        {{ option.name }}
                        <br>
                        <small>BadmintonID: {{ option.refId }}</small>
                    </div>
                </div>
            </template>
            <template slot="empty">Ingen spillere fundet</template>
        </b-autocomplete>
    </b-field>
</template>

<script>
import { defineComponent } from 'vue'
import { debounce } from '@/helpers.js'
import gql from "graphql-tag";

export default defineComponent({
    name: 'MemberSearchAutocomplete',
    props: {
        clubhouseId: [String, Number],
        initialPlayerId: { type: String, default: null },
        message: { type: String, default: 'Søg efter dit navn og vælg dig selv som spiller i klubben for at forbinde din profil.' }
    },
    data() {
        return {
            searchName: '',
            querySearchName: '',
            selectedMember: null,
            searchResults: []
        }
    },
    watch: {
        searchName(val) {
            if (!val) {
                this.selectedMember = null
                this.$emit('clear')
            }
        }
    },
    apollo: {
        membersSearch: {
            query: gql`
                query membersSearch($clubhouse: Int!, $name: String) {
                    membersSearch(clubhouse: $clubhouse, name: $name) {
                        data {
                            id
                            name
                            refId
                        }
                    }
                }
            `,
            variables() {
                return {
                    clubhouse: parseInt(this.clubhouseId, 10),
                    name: '%' + this.querySearchName + '%'
                }
            },
            skip() {
                return !this.clubhouseId || !this.querySearchName
            },
            result({ data }) {
                if (data && data.membersSearch) {
                    this.searchResults = data.membersSearch.data
                }
            }
        },
        initialMember: {
            query: gql`
                query initialMemberSearch($clubhouse: Int!, $refId: String) {
                    membersSearch(clubhouse: $clubhouse, refId: $refId) {
                        data {
                            id
                            name
                            refId
                        }
                    }
                }
            `,
            variables() {
                return {
                    clubhouse: parseInt(this.clubhouseId, 10),
                    refId: this.initialPlayerId
                }
            },
            skip() {
                return !this.clubhouseId || !this.initialPlayerId
            },
            update: data => data.membersSearch,
            result({ data }) {
                if (data && data.membersSearch && data.membersSearch.data.length > 0) {
                    const member = data.membersSearch.data[0]
                    this.selectedMember = member
                    this.searchName = member.name
                }
            }
        }
    },
    methods: {
        onTyping: debounce(function (name) {
            this.querySearchName = name
            this.selectedMember = null
            this.$emit('clear')
        }, 300),
        onSelectMember(option) {
            if (option) {
                this.selectedMember = option
                this.searchName = option.name
                this.$emit('select', option.refId)
            } else {
                this.selectedMember = null
                this.$emit('clear')
            }
        }
    }
})
</script>

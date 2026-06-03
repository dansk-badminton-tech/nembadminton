<template>
    <b-select :required="required" :loading="$apollo.queries.rankingVersions.loading" @focus="onFocus" v-model="version" :expanded="expanded" :placeholder="placeholder">
        <option
            v-for="version in rankingVersions"
            :key="version"
            :value="version"
        >
            {{ timeToMonth(version) }}
            {{ hintText(version) }}
        </option>
    </b-select>
</template>

<script>

import gql from 'graphql-tag'
import {timeToMonth} from "../team-fight/helper";
import {isRecommendedRankingVersionByPlayingDate, resolveRecommendedRankingVersion} from "./ranking-version";

export default {
    name: "RankingVersionSelect",
    props: {
        'value': String,
        'expanded': Boolean|null,
        'afterChange': Function|null,
        'playingDate': Date|null,
        'placeholder': {
            type: String,
            default: "Vælge rangliste"
        },
        required: {
            type: Boolean,
            default: false
        },
        autoSelectRecommended: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            // Tracks the last version we auto-selected. We only overwrite the
            // user's choice if it still matches what we auto-set (i.e. they
            // haven't manually picked something else).
            lastAutoSelectedVersion: null
        }
    },
    methods: {
        onFocus(){
            this.$emit('focus')
        },
        timeToMonth: timeToMonth,
        hintText(currentVersion){
            if(this.playingDate === null || this.playingDate === undefined){
                return ''
            }
            if (isRecommendedRankingVersionByPlayingDate(currentVersion, this.playingDate)) {
                return '(Indstillet automatisk)'
            }
        },
        maybeAutoSelect() {
            if (!this.autoSelectRecommended) {
                return;
            }
            if (!Array.isArray(this.rankingVersions) || this.rankingVersions.length === 0) {
                return;
            }
            if (this.playingDate === null || this.playingDate === undefined) {
                return;
            }
            // Only auto-fill when the field is empty or still matches our own
            // previous auto-pick. If the user manually picked something else,
            // leave it alone.
            const isUntouchedOrAutoSet =
                !this.value || this.value === this.lastAutoSelectedVersion;
            if (!isUntouchedOrAutoSet) {
                return;
            }
            const recommended = resolveRecommendedRankingVersion(this.rankingVersions, this.playingDate);
            if (recommended === null) {
                return; // No matching version — leave field as-is.
            }
            if (recommended === this.value) {
                return;
            }
            this.lastAutoSelectedVersion = recommended;
            this.$emit('input', recommended);
            this.$emit('change', recommended, this.value);
        }
    },
    computed: {
        version: {
            get() {
                return this.value
            },
            set(newValue) {
                this.$emit('input', newValue);
                this.$emit('change', newValue, this.value)
            }
        }
    },
    watch: {
        playingDate() {
            this.maybeAutoSelect();
        },
        rankingVersions() {
            this.maybeAutoSelect();
        }
    },
    apollo: {
        rankingVersions: {
            query: gql`
                    query{
                        rankingVersions
                    }
                `,
            result() {
                this.maybeAutoSelect();
            }
        }
    }
}
</script>

<style scoped>

</style>

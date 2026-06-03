<template>
    <fragment v-if="!$apollo.loading">
        <div v-if="teamRound" dusk="team-fight-public-page">
            <div class="public-hero box mb-5">
                <p class="public-hero-eyebrow has-text-grey">{{ teamRound.clubhouse.name }}</p>
                <h1 class="title is-size-2 mb-3" dusk="team-fight-public-title">
                    {{ teamRound.name || `Holdrunde nr. ${teamRound.round}` }}
                </h1>
                <div class="tags public-hero-chips">
                    <b-tag rounded size="is-medium" dusk="team-fight-public-game-date">
                        <b-icon icon="calendar" size="is-small" class="mr-1"></b-icon>
                        {{ formatGameDate(teamRound.gameDate) || teamRound.gameDate }}
                    </b-tag>
                    <b-tag rounded size="is-medium" v-if="teamRound.squads && teamRound.squads.length">
                        <b-icon icon="badminton" size="is-small" class="mr-1"></b-icon>
                        {{ teamRound.squads.length }} hold
                    </b-tag>
                </div>
            </div>

            <div v-if="teamRound.squads && teamRound.squads.length" class="columns is-multiline">
                <div
                    v-for="(squad, index) in teamRound.squads"
                    :key="squad.id"
                    class="column is-half-desktop is-full-tablet"
                    dusk="team-fight-public-squad">
                    <div class="card squad-card" :dusk="'squad-card-' + index">
                        <div class="card-content">
                            <p class="squad-eyebrow has-text-grey">HOLD {{ index + 1 }} - {{squad.name}}</p>
                            <h2 v-if="squad.tier" class="is-size-4 has-text-weight-bold squad-title">
                                {{ squad.tier }}
                            </h2>
                            <div
                                v-if="hasSquadChips(squad)"
                                class="tags squad-info-chip-row mt-3"
                                :dusk="'squad-info-chip-row-' + index">
                                <b-tag rounded v-if="squad.playingDatetime">
                                    <b-icon icon="calendar" size="is-small" class="mr-1"></b-icon>
                                    {{ formatPlayingDatetime(squad.playingDatetime) }}
                                </b-tag>
                                <b-tag rounded v-if="squad.playingPlace">
                                    <b-icon icon="map-marker" size="is-small" class="mr-1"></b-icon>
                                    {{ squad.playingPlace }}
                                </b-tag>
                                <a
                                    v-if="composedAddress(squad)"
                                    :href="mapsUrl(squad)"
                                    target="_blank"
                                    rel="noopener"
                                    class="tag is-rounded is-link is-light">
                                    <b-icon icon="map" size="is-small" class="mr-1"></b-icon>
                                    {{ composedAddress(squad) }}
                                </a>
                                <a
                                    v-if="squad.externalTeamFightID"
                                    :href="bpUrl(squad.externalTeamFightID)"
                                    target="_blank"
                                    rel="noopener"
                                    class="tag is-rounded is-link is-light">
                                    <b-icon icon="open-in-new" size="is-small" class="mr-1"></b-icon>
                                    BP #{{ squad.externalTeamFightID }}
                                </a>
                            </div>
                        </div>

                        <div class="card-content squad-body">
                            <div v-if="hasAnyPlayer(squad)" class="category-grid">
                                <template v-for="category in nonEmptyCategories(squad)">
                                    <div :key="category.id + '-label'" class="category-label">
                                        {{ category.name }}
                                    </div>
                                    <div :key="category.id + '-players'" class="category-players">
                                        <div
                                            v-for="player in category.players"
                                            :key="player.id"
                                            class="player-row"
                                            :dusk="'player-' + player.refId">
                                            <span class="player-name">
                                                <b-icon
                                                    v-if="player.gender === 'MEN'"
                                                    icon="gender-male"
                                                    size="is-small">
                                                </b-icon>
                                                <b-icon
                                                    v-if="player.gender === 'WOMEN'"
                                                    icon="gender-female"
                                                    size="is-small">
                                                </b-icon>
                                                {{ player.name }}
                                            </span>
                                            <span class="player-tags">
                                                <b-tag size="is-small">
                                                    {{ findPositions(player, category.category) }}
                                                </b-tag>
                                                <b-tag v-if="isYoungPlayer(player)" size="is-small" type="is-info" class="ml-1">
                                                    {{ ageGroupLabel(player) }}
                                                </b-tag>
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <p v-else class="has-text-grey">
                                <em>Ingen spillere endnu</em>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <section v-else class="section has-text-centered" dusk="team-fight-public-empty">
                <b-icon icon="account-group-outline" size="is-large" class="mb-2"></b-icon>
                <h2 class="title is-size-5">Ingen hold endnu</h2>
                <p class="has-text-grey">Der er endnu ikke oprettet hold i denne holdrunde.</p>
            </section>
        </div>

        <section v-else class="section has-text-centered" dusk="team-fight-public-not-found">
            <b-icon icon="magnify" size="is-large" class="mb-2"></b-icon>
            <h1 class="title">Kamp ikke fundet</h1>
            <p class="subtitle">Vi kunne ikke finde den ønskede kamp. Den kan være slettet, eller linket er forkert.</p>
        </section>
    </fragment>
</template>

<script>
import gql from 'graphql-tag'
import {ageGroupLabel, findPositions, getCurrentSeason, isYoungPlayer} from "../../helpers";
import {
    formatGameDate,
    formatPlayingDatetime,
    composedAddress,
    badmintonPlayerUrl,
    googleMapsUrl
} from "./helper";

export default {
    name: "TeamFightPublic",
    methods: {
        ageGroupLabel,
        isYoungPlayer,
        findPositions,
        formatGameDate,
        formatPlayingDatetime,
        composedAddress,
        bpUrl(externalTeamFightID) {
            return badmintonPlayerUrl(externalTeamFightID, getCurrentSeason())
        },
        mapsUrl(squad) {
            return googleMapsUrl(squad)
        },
        hasSquadChips(squad) {
            return !!(squad.playingDatetime || squad.playingPlace || composedAddress(squad) || squad.externalTeamFightID)
        },
        nonEmptyCategories(squad) {
            return (squad.categories || []).filter(category => category.players && category.players.length > 0)
        },
        hasAnyPlayer(squad) {
            return (squad.categories || []).some(category => category.players && category.players.length > 0)
        }
    },
    data() {
        return {
            searchPlayer: '',
            showNotificationPopUp: false,
            teamRound: null
        }
    },
    props: {
        teamRoundId: String,
    },
    computed: {
        getCurrentSeason
    },
    watch: {
        teamRound(newValue) {
            if (newValue) {
                document.title = newValue.name || `Holdrunde nr. ${newValue.round}`
            }
        }
    },
    apollo: {
        teamRound: {
            query: gql` query($id: ID!){
                  teamRound(id: $id){
                    id
                    name
                      round
                    gameDate
                      clubhouse {
                          name
                      }
                    squads{
                        id
                        playerLimit
                        name
                        tier
                        playingDatetime
                        playingPlace
                        playingAddress
                        playingZipCode
                        playingCity
                        externalTeamFightID
                        categories{
                            id
                            category
                            name
                            players{
                                id
                                gender
                                name
                                refId
                                vintage
                                points{
                                    category
                                    points
                                    position
                                    vintage
                                }
                            }
                        }
                    }
                  }
                }`,
            fetchPolicy: "network-only",
            variables: function () {
                return {
                    id: this.teamRoundId
                }
            }
        }
    }
}
</script>

<style scoped>
.public-hero {
    background-color: #fafafa;
    border-left: 4px solid #3273dc;
}
.public-hero-eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.public-hero-chips {
    flex-wrap: wrap;
    gap: 0.25rem 0.5rem;
    margin-bottom: 0;
}
.public-hero-chips .tag {
    margin-bottom: 0;
}

.squad-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}
.squad-eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 0.1rem;
}
.squad-title {
    line-height: 1.2;
    word-break: break-word;
}
.squad-info-chip-row {
    flex-wrap: wrap;
    gap: 0.25rem 0.5rem;
    margin-bottom: 0;
}
.squad-info-chip-row .tag {
    margin-bottom: 0;
}
.squad-info-chip-row a.tag {
    text-decoration: none;
}

.squad-body {
    border-top: 1px solid #f0f0f0;
    padding-top: 1rem;
}

.category-grid {
    display: grid;
    grid-template-columns: 3.5rem 1fr;
    row-gap: 0.85rem;
    column-gap: 0.75rem;
    align-items: start;
}
.category-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #4a4a4a;
    letter-spacing: 0.04em;
    padding-top: 0.2rem;
}
.category-players {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.player-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.player-name {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}
.player-tags {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
}

@media (max-width: 480px) {
    .category-grid {
        grid-template-columns: 3rem 1fr;
        column-gap: 0.5rem;
    }
    .public-hero {
        padding: 1rem;
    }
}
</style>

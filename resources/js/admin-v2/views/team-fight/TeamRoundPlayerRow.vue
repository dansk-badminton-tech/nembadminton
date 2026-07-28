<template>
    <div
        class="team-fight-item mb-4 p-4"
        :class="isUpcoming ? 'is-upcoming' : 'is-past'"
    >
        <div class="is-flex is-justify-content-space-between is-align-items-center">
            <div>
                <div class="is-flex is-align-items-center mb-1">
                    <b-tag
                        :type="isUpcoming ? 'is-info' : 'is-light'"
                        size="is-small"
                        class="mr-2"
                    >
                        {{ isUpcoming ? 'Kommende' : 'Spillet' }}
                    </b-tag>
                    <span class="title is-6 mb-0">
                        {{ round.name === null ? 'Runde ' + round.round : round.name }}
                    </span>
                </div>
                <p class="subtitle is-7 has-text-grey mb-0">
                    <b-icon icon="calendar" size="is-small" class="mr-1"></b-icon>
                    {{ formatGameDate(round.gameDate) }}
                </p>
            </div>
            <b-button
                tag="router-link"
                :to="{ name: 'team-fight-public-view', params: { teamUUID: round.id } }"
                type="is-link"
                outlined
                size="is-small"
                icon-right="eye"
            >
                Vis holdopstilling
            </b-button>
        </div>
    </div>
</template>

<script>
import {formatGameDate} from "./helper";

export default {
    name: "TeamRoundPlayerRow",
    props: {
        round: {
            type: Object,
            required: true
        }
    },
    computed: {
        isUpcoming() {
            if (!this.round.gameDate) return false;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const gameDate = new Date(this.round.gameDate);
            return gameDate >= today;
        }
    },
    methods: {
        formatGameDate
    }
}
</script>

<style scoped>
.team-fight-item {
    border-radius: 6px;
    border: 1px solid #dbdbdb;
    background-color: #ffffff;
    transition: all 0.2s ease;
}

.team-fight-item.is-upcoming {
    border-left: 4px solid #3e8ed0;
}

.team-fight-item.is-past {
    border-left: 4px solid #b5b5b5;
    background-color: #fafafa;
    opacity: 0.85;
}

.team-fight-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}
</style>

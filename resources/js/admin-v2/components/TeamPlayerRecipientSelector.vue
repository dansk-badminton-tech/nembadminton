<script>
export default {
    name: 'TeamPlayerRecipientSelector',
    props: {
        squads: { type: Array, required: true },
        reachablePlayerRefIds: { type: Array, default: () => [] },
        modelValue: { type: Array, required: true },
    },
    computed: {
        reachableSet() {
            return new Set(this.reachablePlayerRefIds);
        },
        selectedSet() {
            return new Set(this.modelValue);
        },
        playersBySquad() {
            return this.squads.map(squad => {
                const seen = new Set();
                const players = (squad.categories || []).flatMap(c => (c.players || []))
                    .filter(p => {
                        if (!p.refId || seen.has(p.refId)) return false;
                        seen.add(p.refId);
                        return true;
                    })
                    .map(p => ({
                        refId: p.refId,
                        name: p.name,
                        reachable: this.reachableSet.has(p.refId),
                        selected: this.selectedSet.has(p.refId),
                    }));
                return {
                    squadId: squad.id,
                    squadName: squad.name,
                    players,
                };
            });
        },
        allPlayerRefIds() {
            return (this.squads || []).flatMap(s => (s.categories || []).flatMap(c => (c.players || [])))
                .filter(p => p.refId)
                .map(p => p.refId);
        },
        selectedCount() {
            return this.modelValue.length;
        },
    },
    methods: {
        emitChange(nextSet) {
            this.$emit('update:modelValue', [...nextSet]);
        },
        togglePlayer(refId) {
            const next = new Set(this.selectedSet);
            next.has(refId) ? next.delete(refId) : next.add(refId);
            this.emitChange(next);
        },
        toggleSquad(squad, selectAll) {
            const next = new Set(this.selectedSet);
            squad.players.forEach(p => {
                selectAll ? next.add(p.refId) : next.delete(p.refId);
            });
            this.emitChange(next);
        },
        selectAll() {
            this.emitChange(new Set(this.allPlayerRefIds));
        },
        deselectAll() {
            this.emitChange(new Set());
        },
        isSquadFullySelected(squad) {
            return squad.players.length > 0 && squad.players.every(p => this.selectedSet.has(p.refId));
        },
    },
};
</script>

<template>
    <div dusk="notify-player-list" class="team-player-recipient-selector">
        <div class="is-flex is-justify-content-space-between is-align-items-center mb-2">
            <p class="is-size-7 has-text-grey">
                Vælg hvilke spillere der skal modtage beskeden
                ({{ selectedCount }} valgt)
            </p>
            <div class="buttons has-addons">
                <b-button size="is-small" type="is-text" dusk="select-all-players" @click="selectAll">Vælg alle</b-button>
                <b-button size="is-small" type="is-text" dusk="deselect-all-players" @click="deselectAll">Fravælg alle</b-button>
            </div>
        </div>

        <div v-for="squad in playersBySquad" :key="squad.squadId" class="squad-group">
            <div class="squad-header" @click="toggleSquad(squad, !isSquadFullySelected(squad))">
                <b-checkbox
                    :dusk="`squad-toggle-${squad.squadId}`"
                    :value="isSquadFullySelected(squad)">
                    <strong>{{ squad.squadName }}</strong>
                    <span class="is-size-7 has-text-grey">
                        ({{ squad.players.filter(p => p.selected).length }}/{{ squad.players.length }})
                    </span>
                </b-checkbox>
            </div>
            <div class="player-rows">
                <div
                    v-for="p in squad.players"
                    :key="p.refId"
                    :dusk="`player-row-${p.refId}`"
                    class="player-row"
                    @click="togglePlayer(p.refId)">
                    <b-checkbox :value="p.selected">
                        {{ p.name }}
                    </b-checkbox>
                    <b-tag v-if="!p.reachable" type="is-warning is-light" class="ml-2">Ingen konto</b-tag>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.team-player-recipient-selector {
    border: 1px solid #dbdbdb;
    border-radius: 6px;
    padding: 1rem;
    max-height: 400px;
    overflow-y: auto;
}
.squad-group {
    margin-bottom: 1rem;
}
.squad-group:last-child {
    margin-bottom: 0;
}
.squad-header {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f5f5f5;
    cursor: pointer;
}
.player-rows {
    padding-left: 1.5rem;
}
.player-row {
    padding: 0.25rem 0;
    cursor: pointer;
}
.player-row:hover {
    background-color: #f5f5f5;
}
</style>

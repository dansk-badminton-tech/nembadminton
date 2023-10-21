import omitDeep from "omit-deep";

export function hasInvalidCategory(playingToHighSquadList) {
    const playersWithoutYouth = playingToHighSquadList.filter((playerInfo) => {
        return !playerInfo.isYouthPlayer
    })
    const playersWithoutYouthPartner = playersWithoutYouth.filter((playerInfo) => {
        return !playerInfo.hasYouthPlayerPartner
    })
    return playersWithoutYouthPartner.length > 0
}

export function hasInvalidLevel(playingToHighList) {
    const playersWithoutYouth = playingToHighList.filter((playerInfo) => {
        return !playerInfo.isYouthPlayer
    })
    return playersWithoutYouth.length > 0
}

export function wrapSquadsInTeamWithoutLeague(squads){
    return squads.map((squad) => ({
        name: 'Team X',
        squad: {
            id: squad.id,
            playerLimit: squad.playerLimit,
            categories: squad.categories.map((category) => ({
                id: category.id,
                category: category.category,
                name: category.name,
                players: category.players.map((player) => ({
                    id: player.id,
                    gender: player.gender,
                    name: player.name,
                    refId: player.refId,
                    points: player.points.map((point) => ({
                        category: point.category,
                        points: point.points,
                        position: point.position,
                        vintage: point.vintage
                    }))
                }))
            }))
        }
    }))
}
export function wrapInTeamAndSquads(squads) {
    return squads.map((squad) => ({
        name: 'Team X',
        squad: {
            id: squad.id,
            playerLimit: squad.playerLimit,
            league: squad.league,
            categories: squad.categories.map((category) => ({
                id: category.id,
                category: category.category,
                name: category.name,
                players: category.players.map((player) => ({
                    id: player.id,
                    gender: player.gender,
                    name: player.name,
                    refId: player.refId,
                    points: player.points.map((point) => ({
                        category: point.category,
                        points: point.points,
                        position: point.position,
                        vintage: point.vintage
                    }))
                }))
            }))
        }
    }))
}

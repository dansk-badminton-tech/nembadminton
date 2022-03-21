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

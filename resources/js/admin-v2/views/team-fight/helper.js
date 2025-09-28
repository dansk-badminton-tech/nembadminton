export function hasInvalidCategory(playingToHighSquadList) {
    const playersWithoutYouthPartner = filterYouthFromCategory(playingToHighSquadList)
    return playersWithoutYouthPartner.length > 0
}

export function filterYouthFromCategory(players){
    const playersWithoutYouth = players.filter((playerInfo) => {
        return !playerInfo.isYouthPlayer
    })
    return playersWithoutYouth.filter((playerInfo) => {
        return !playerInfo.hasYouthPlayerPartner
    })
}

export function hasInvalidLevel(playingToHighList) {
    const playersWithoutYouth = filterYouthFromLevel(playingToHighList)
    return playersWithoutYouth.length > 0
}

export function filterYouthFromLevel(players){
    return players.filter((playerInfo) => {
        return !playerInfo.isYouthPlayer
    });
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

export function vintageOptions(){
    return [
        {value: "U15", label: "U15"},
        {value: "U17", label: "U17"},
        {value: "U19", label: "U19"},
        {value: "SEN", label: "SEN"}
    ]
}

export function convertCategoryAndGenderToFinalCategory(category, gender){
    if(category === "LEVEL"){
        return null
    }
    if(gender === "MEN"){
        if(category === "SINGLE"){
            return "HS"
        }
        if(category === "DOUBLE"){
            return "HD"
        }
        if(category === "MIXDOUBLE"){
            return "MxH"
        }
    }
    if(gender === "WOMEN"){
        if(category === "SINGLE"){
            return "DS"
        }
        if(category === "DOUBLE"){
            return "DD"
        }
        if(category === "MIXDOUBLE"){
            return "MxD"
        }
    }
}

export function convertShortCategoryAndGenderToFinalCategory(shortCategory, gender){
    if(shortCategory === "MD"){
        if(gender === "MEN"){
            return "MxH"
        }else{
            return "MxD"
        }
    }
    return shortCategory
}

export function timeToMonth(currentVersion){
    let date = new Date(Date.parse(currentVersion))
    let dateString = date.toLocaleString("da-DK", {month: "long"});
    return dateString.charAt(0).toUpperCase() + dateString.slice(1) + " "+date.toLocaleString("da-DK", {year: "numeric"})
}

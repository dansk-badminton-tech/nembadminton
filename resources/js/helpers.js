export function chunk(array, size) {
    const chunked_arr = [];
    for (let i = 0; i < array.length; i++) {
        const last = chunked_arr[chunked_arr.length - 1];
        if (!last || last.length === size) {
            chunked_arr.push([array[i]]);
        } else {
            last.push(array[i]);
        }
    }
    return chunked_arr;
}

export function defaultIfUndefined(test, defaultValue) {
    if (test === undefined) {
        return defaultValue
    }
    return test
}

export function debounce(fn, delay) {
    let timeoutID = null
    return function () {
        clearTimeout(timeoutID)
        const args = arguments;
        const that = this;
        timeoutID = setTimeout(function () {
            fn.apply(that, args)
        }, delay)
    }
}

export function findPlayersInCategory(categories, searchCategory, gender) {
    const players = [];
    for (let categoryItem of categories) {
        if (categoryItem.category === searchCategory) {
            for (let player of categoryItem.players) {
                if (player.gender === gender) {
                    players.push(player)
                }
            }
        }
    }
    return players;
}

export function isYoungPlayer(player, category) {
    if (!player.points) {
        return false
    }
    for (let point of player.points) {
        if (point.vintage === '' || point.vintage === null) {
            return false;
        } else {
            if (category === 'MD') {
                if (player.gender === 'M' && point.category === 'MxH') {
                    return point.vintage.toUpperCase() === 'U17' || point.vintage.toUpperCase() === 'U19'
                }
                if (player.gender === 'K' && point.category === 'MxD') {
                    return point.vintage.toUpperCase() === 'U17' || point.vintage.toUpperCase() === 'U19'
                }
            }
            if (point.category === category) {
                return point.vintage.toUpperCase() === 'U17' || point.vintage.toUpperCase() === 'U19'
            }
        }
    }
    return false;
}

export function convertRankingToCategory(categoryEnum){
    if(categoryEnum === 'MEN_LEVEL' || categoryEnum === 'WOMEN_LEVEL' || categoryEnum === 'ALL_LEVEL'){
        return null
    }
    if(categoryEnum === 'WOMEN_SINGLE'){
        return 'DS'
    }
    if(categoryEnum === 'WOMENS_DOUBLE'){
        return 'DD'
    }
    if(categoryEnum === 'WOMEN_MIX' || categoryEnum === 'MEN_MIX'){
        return 'MD'
    }
    if(categoryEnum === 'MEN_SINGLE'){
        return 'HS'
    }
    if(categoryEnum === 'MENS_DOUBLE'){
        return 'HD'
    }
}

export function findLevel(member, category) {
    if (!member.points) {
        return 0
    }
    for (let point of member.points) {
        if (category === 'MD') {
            if (member.gender === 'M' && point.category === 'MxH') {
                return point.points
            }
            if (member.gender === 'K' && point.category === 'MxD') {
                return point.points
            }
        }
        if (point.category === category) {
            return point.points
        }
    }
    return 0
}

export function findPositions(member, show = 'all') {
    if (!member.points) {
        return ''
    }
    let summary = []
    for (let point of member.points) {
        if (point.category === null && point.points !== null && (show === 'all' || show === 'N')) {
            summary.push('N:' + point.points)
        }
        if (point.category === 'HS' && (show === 'all' || show === 'HS')) {
            summary.push('HS:' + point.points)
        }
        if (point.category === 'HD' && (show === 'all' || show === 'HD')) {
            summary.push('HD:' + point.points)
        }
        if (point.category === 'DS' && (show === 'all' || show === 'DS')) {
            summary.push('DS:' + point.points)
        }
        if (point.category === 'DD' && (show === 'all' || show === 'DD')) {
            summary.push('DD:' + point.points)
        }
        if (point.category === 'MxH' && member.gender === 'M' && (show === 'all' || show === 'MD')) {
            summary.push('MxH:' + point.points)
        }
        if (point.category === 'MxD' && member.gender === 'K' && (show === 'all' || show === 'MD')) {
            summary.push('MxD:' + point.points)
        }
    }
    return summary.join(', ')
}

export function extractErrors(graphqlErrors) {
    let errors = [];
    for (let graphqlError of graphqlErrors) {
        if (graphqlError.extensions.category === 'validation') {
            for (let validationKey in graphqlError.extensions.validation) {
                for (let error of graphqlError.extensions.validation[validationKey]) {
                    errors.push(error)
                }
            }
        }
    }
    return errors;
}

export function isPlayingToHigh(playingToHighPlayers, player, category) {
    return getPlayingToHigh(playingToHighPlayers, player, category) !== undefined;
}

export function isPlayingToHighByName(playingToHighPlayers, player, category) {
    return playingToHighPlayers.find(toHighPlayer => toHighPlayer.name === player.name && toHighPlayer.category === category) !== undefined;
}

export function isPlayingToHighByBadmintonPlayerId(playingToHighPlayers, player, category) {
    return getPlayingToHighByBadmintonPlayerId(playingToHighPlayers, player, category) !== undefined;
}

export function getPlayingToHighByBadmintonPlayerId(playingToHighPlayers, player, category) {
    return playingToHighPlayers.find(toHighPlayer => toHighPlayer.refId === player.refId && toHighPlayer.category === category)
}

export function getPlayingToHigh(playingToHighPlayers, player, category) {
    return playingToHighPlayers.find(toHighPlayer => toHighPlayer.id === player.id && toHighPlayer.category === category)
}

export function highlight(playingToHighCrossSquads, playingToHighInSquad, player, category) {
    const playerInfoCrossSquads = getPlayingToHighByBadmintonPlayerId(playingToHighCrossSquads, player, category);
    const playerInfoInSquad = getPlayingToHighByBadmintonPlayerId(playingToHighInSquad, player, category);

    if (playerInfoCrossSquads !== undefined) {
        if (playerInfoCrossSquads.isYouthPlayer) {
            return {
                'has-background-success': true
            }
        }
        return {
            'has-background-warning': true
        }
    }
    if (playerInfoInSquad !== undefined) {
        if (playerInfoInSquad.isYouthPlayer) {
            return {
                'has-background-success': true
            }
        } else {
            if (playerInfoInSquad.hasYouthPlayerPartner) {
                return {
                    'has-background-success': true
                }
            }
            return {
                'has-background-danger': true
            }
        }
    }
    return {};
}

export function resolveToolTip(player, category, league, playingToHighCrossSquads, playingToHighInSquad) {
    let msg = []
    let resolveNames = (playerWithBelowPlayers) => {
        let names = playerWithBelowPlayers.belowPlayer.map(x => x.name)
        return names.join(', ')
    }
    let playerWithBelowPlayersCrossSquads = getPlayingToHighByBadmintonPlayerId(playingToHighCrossSquads, player, category)
    let playerWithBelowPlayersSquad = getPlayingToHighByBadmintonPlayerId(playingToHighInSquad, player, category)
    if (playerWithBelowPlayersSquad !== undefined || playerWithBelowPlayersCrossSquads !== undefined) {
        if (isYoungPlayer(player, null)) {
            msg.push("OBS: U17/U19 spiller");
        }
    }
    if (playerWithBelowPlayersCrossSquads !== undefined) {
        if (isLeagueOrFirstDivision(league)) {
            msg.push("Bedre spiller på KATEGORI-ranglisten: " + resolveNames(playerWithBelowPlayersCrossSquads));
        } else {
            msg.push("Bedre spiller på NIVEAU-ranglisten: " + resolveNames(playerWithBelowPlayersCrossSquads));
        }
    }
    if (playerWithBelowPlayersSquad !== undefined) {
        if(!playerWithBelowPlayersSquad.isYouthPlayer && playerWithBelowPlayersSquad.hasYouthPlayerPartner){
            msg.push("OBS: Har U17/U19 makker")
        }
        msg.push("Bedre spiller i kategorien: " + resolveNames(playerWithBelowPlayersSquad))
    }
    return msg.join("<br />--------<br />");
}

export function isDoubleCategory(category) {
    return ["MD", "DD", "HD"].includes(category.category.toUpperCase());
}

export function isWomenDouble(category) {
    return ["DD"].includes(category.category.toUpperCase());
}

export function isMensDouble(category) {
    return ["HD"].includes(category.category.toUpperCase());
}

export function isMixDouble(category) {
    return ["MD"].includes(category.category.toUpperCase());
}

export function isMensSingle(category) {
    return ["HS"].includes(category.category.toUpperCase());
}

export function isWomensSingle(category) {
    return ["DS"].includes(category.category.toUpperCase());
}

export function containsWomen(category) {
    return category.players.some((player) => {
        return player.gender === 'K';
    });
}

export function containsMen(category) {
    return category.players.some((player) => {
        return player.gender === 'M';
    });
}

export function existsInSquadsByRefId(squads, player) {
    for (let squad of squads) {
        for (let category of squad.categories) {
            for (let currentPlayer of category.players) {
                if (currentPlayer.refId === player.refId) {
                    return true
                }
            }
        }
    }
    return false
}

function isLeagueOrFirstDivision(league) {
    return league.toUpperCase() === 'FIRSTDIVISION' || league.toUpperCase() === 'LIGA';
}

export function swap(arr, from, to) {
    arr.splice(from, 1, arr.splice(to, 1, arr[from])[0]);
}

export function swapObject(obj, from, to) {
    const fromItem = obj[from]
    const toItem = obj[to]
    obj[to] = fromItem
    obj[from] = toItem
}

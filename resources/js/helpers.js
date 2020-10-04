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

export function findPlayersInCategory(categories, searchCategory) {
    const players = [];
    for (let categoryItem of categories) {
        if (categoryItem.category === searchCategory) {
            players.push.apply(players, categoryItem.players)
        }
    }
    return players;
}

export function findLevel(member) {
    if (!member.points) {
        return 0
    }
    for (let point of member.points) {
        if (point.category === null && point.points !== null) {
            return point.points
        }
    }
    return 0
}

export function findPositions(member) {
    if (!member.points) {
        return ''
    }
    let summary = []
    for (let point of member.points) {
        if (point.category === null && point.points !== null) {
            summary.push('N:' + point.points)
        }
//                if (point.category === 'HS') {
//                    summary.push('HS:' + point.points)
//                }
//                if (point.category === 'HD') {
//                    summary.push('HD:' + point.points)
//                }
//                if (point.category === 'DS') {
//                    summary.push('DS:' + point.points)
//                }
//                if (point.category === 'MxH') {
//                    summary.push('MxH:' + point.points)
//                }
//                if (point.category === 'MxD') {
//                    summary.push('MxD:' + point.points)
//                }
    }
    return '(' + summary.join(', ') + ')'
}

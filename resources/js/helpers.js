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

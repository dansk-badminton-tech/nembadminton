const PREDEFINED_SQUAD_FORMATS = Object.freeze({
    9: {
        playerLimit: 6,
        categories: [
            {name: "1. MD", category: "MD"},
            {name: "2. MD", category: "MD"},
            {name: "1. DS", category: "DS"},
            {name: "2. DS", category: "DS"},
            {name: "1. HS", category: "HS"},
            {name: "2. HS", category: "HS"},
            {name: "1. DD", category: "DD"},
            {name: "1. HD", category: "HD"},
            {name: "2. HD", category: "HD"},
        ],
    },
    10: {
        playerLimit: 10,
        categories: [
            {name: "1. HD", category: "HD"},
            {name: "2. HD", category: "HD"},
            {name: "1. DD", category: "DD"},
            {name: "1. DS", category: "DS"},
            {name: "1. MD", category: "MD"},
            {name: "1. HS", category: "HS"},
            {name: "2. HS", category: "HS"},
            {name: "3. HS", category: "HS"},
            {name: "3. HD", category: "HD"},
            {name: "2. DD", category: "DD"},
        ],
    },
    11: {
        playerLimit: 8,
        categories: [
            {name: "1. MD", category: "MD"},
            {name: "2. MD", category: "MD"},
            {name: "1. DS", category: "DS"},
            {name: "2. DS", category: "DS"},
            {name: "1. HS", category: "HS"},
            {name: "2. HS", category: "HS"},
            {name: "3. HS", category: "HS"},
            {name: "4. HS", category: "HS"},
            {name: "1. DD", category: "DD"},
            {name: "1. HD", category: "HD"},
            {name: "2. HD", category: "HD"},
        ],
    },
    13: {
        playerLimit: 10,
        categories: [
            {name: "1. MD", category: "MD"},
            {name: "2. MD", category: "MD"},
            {name: "1. DS", category: "DS"},
            {name: "2. DS", category: "DS"},
            {name: "1. HS", category: "HS"},
            {name: "2. HS", category: "HS"},
            {name: "3. HS", category: "HS"},
            {name: "4. HS", category: "HS"},
            {name: "1. DD", category: "DD"},
            {name: "2. DD", category: "DD"},
            {name: "1. HD", category: "HD"},
            {name: "2. HD", category: "HD"},
            {name: "3. HD", category: "HD"},
        ],
    },
});

function createSquadPayload(playerLimit, categories) {
    return {
        playerLimit,
        categories: {
            create: categories.map((category) => ({...category})),
        },
    };
}

export class TeamFightHelper {
    static getSupportedMatchCounts() {
        return Object.keys(PREDEFINED_SQUAD_FORMATS).map((value) => Number.parseInt(value, 10));
    }

    static generateSquadByMatchCount(matchCount) {
        const normalizedMatchCount = Number.parseInt(matchCount, 10);
        const format = PREDEFINED_SQUAD_FORMATS[normalizedMatchCount];
        if (format === undefined) {
            throw new Error(`Unsupported match count format: ${matchCount}`);
        }

        return createSquadPayload(format.playerLimit, format.categories);
    }

    static generateSquad(mix, womenSingles, womenDoubles, mensSingles, mensDoubles) {
        let categories = [];
        let playerLimit = mix + womenSingles + womenDoubles + mensSingles + mensDoubles;

        // Generate Mixed Doubles Categories
        for (let i = 1; i <= mix; i++) {
            categories.push({name: `${i}. MD`, category: "MD"});
        }

        // Generate Women Singles Categories
        for (let i = 1; i <= womenSingles; i++) {
            categories.push({name: `${i}. DS`, category: "DS"});
        }

        // Generate Women Doubles Categories
        for (let i = 1; i <= womenDoubles; i++) {
            categories.push({name: `${i}. DD`, category: "DD"});
        }

        // Generate Men Singles Categories
        for (let i = 1; i <= mensSingles; i++) {
            categories.push({name: `${i}. HS`, category: "HS"});
        }

        // Generate Men Doubles Categories
        for (let i = 1; i <= mensDoubles; i++) {
            categories.push({name: `${i}. HD`, category: "HD"});
        }

        return {
            playerLimit: playerLimit,
            categories: {
                create: categories
            }
        };
    }

    // Backwards-compatible wrappers used by the current UI.
    static generateSquadWith10Players() {
        return this.generateSquadByMatchCount(13);
    }

    static generateSquadWith8Players() {
        return this.generateSquadByMatchCount(11);
    }

    static generateSquadWith6Players() {
        return this.generateSquadByMatchCount(9);
    }

    static generateSquadSeries1() {
        return this.generateSquadByMatchCount(10);
    }
}

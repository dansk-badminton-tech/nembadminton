export class TeamFightHelper {
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
            league: 'OTHER',
            categories: {
                create: categories
            }
        };
    }

    static generateSquadWith10Players() {
        return {
            playerLimit: 10,
            league: 'OTHER',
            categories: {
                create: [{
                    name: "1. MD",
                    category: "MD",
                }, {
                    name: "2. MD",
                    category: "MD",
                }, {
                    name: "1. DS",
                    category: "DS",
                }, {
                    name: "2. DS",
                    category: "DS",
                }, {
                    name: "1. HS",
                    category: "HS",
                }, {
                    name: "2. HS",
                    category: "HS",
                }, {
                    name: "3. HS",
                    category: "HS",
                }, {
                    name: "4. HS",
                    category: "HS",
                }, {
                    name: "1. DD",
                    category: "DD",
                }, {
                    name: "2. DD",
                    category: "DD",
                }, {
                    name: "1. HD",
                    category: "HD",
                }, {
                    name: "2. HD",
                    category: "HD",
                }, {
                    name: "3. HD",
                    category: "HD",
                }
                ]
            }
        }
    }

    static generateSquadWith8Players() {
        return {
            playerLimit: 8,
            league: 'OTHER',
            categories: {
                create: [{
                    name: "1. MD",
                    category: "MD",
                }, {
                    name: "2. MD",
                    category: "MD",
                }, {
                    name: "1. DS",
                    category: "DS",
                }, {
                    name: "2. DS",
                    category: "DS",
                }, {
                    name: "1. HS",
                    category: "HS",
                }, {
                    name: "2. HS",
                    category: "HS",
                }, {
                    name: "3. HS",
                    category: "HS",
                }, {
                    name: "4. HS",
                    category: "HS",
                }, {
                    name: "1. DD",
                    category: "DD",
                }, {
                    name: "1. HD",
                    category: "HD",
                }, {
                    name: "2. HD",
                    category: "HD",
                }
                ]
            }
        }
    }

    static generateSquadWith6Players() {
        return {
            playerLimit: 6,
            league: 'OTHER',
            categories: {
                create: [{
                    name: "1. MD",
                    category: "MD",
                }, {
                    name: "2. MD",
                    category: "MD",
                }, {
                    name: "1. DS",
                    category: "DS",
                }, {
                    name: "2. DS",
                    category: "DS",
                }, {
                    name: "1. HS",
                    category: "HS",
                }, {
                    name: "2. HS",
                    category: "HS",
                }, {
                    name: "1. DD",
                    category: "DD",
                }, {
                    name: "1. HD",
                    category: "HD",
                }, {
                    name: "2. HD",
                    category: "HD",
                }
                ]
            }
        }
    }

}

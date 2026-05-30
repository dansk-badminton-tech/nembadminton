export function isRecommendedRankingVersionByPlayingDate(currentVersion, playingDate) {
    let date = new Date(Date.parse(currentVersion));
    let lowerBound = new Date(Date.parse(currentVersion));
    lowerBound.setUTCDate(10);
    lowerBound.setHours(0, 0, 0, 0);
    let upperBound = new Date(date.setMonth(date.getMonth() + 1, 9));
    upperBound.setHours(0, 0, 0, 0);

    return lowerBound.getTime() <= playingDate.getTime() && playingDate.getTime() <= upperBound.getTime();
}

export function resolveRecommendedRankingVersion(rankingVersions, playingDate) {
    if (playingDate === null || playingDate === undefined) {
        return null;
    }

    const versions = Array.isArray(rankingVersions)
                     ? rankingVersions
                     : [];

    for (const currentVersion of versions) {
        if (isRecommendedRankingVersionByPlayingDate(currentVersion, playingDate)) {
            return currentVersion;
        }
    }

    return null;
}

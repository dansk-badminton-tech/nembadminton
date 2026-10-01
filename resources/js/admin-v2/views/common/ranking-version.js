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

// Badminton Danmark sometimes re-publishes a month's ranking list under a new version date,
// so a month can have several versions. Only the newest of them is recommended.
export function resolveRecommendedNewestRankingVersion(rankingVersions, newestRankingVersions, playingDate) {
    const newest = Array.isArray(newestRankingVersions)
                   ? newestRankingVersions
                   : [];
    const versions = (Array.isArray(rankingVersions) ? rankingVersions : [])
        .filter((version) => newest.length === 0 || newest.includes(version));

    return resolveRecommendedRankingVersion(versions, playingDate);
}

// Distinguishes versions of a month that has been re-published, e.g. "(01.09 – nyeste)".
export function rankingVersionSuffix(version, rankingVersions, newestRankingVersions) {
    const month = version.substring(0, 7);
    const versionsInMonth = (Array.isArray(rankingVersions) ? rankingVersions : [])
        .filter((other) => other.substring(0, 7) === month);
    if (versionsInMonth.length < 2) {
        return '';
    }
    const [, mm, dd] = version.split('-');
    const isNewest = Array.isArray(newestRankingVersions) && newestRankingVersions.includes(version);

    return isNewest
           ? `(${dd}.${mm} – nyeste)`
           : `(${dd}.${mm})`;
}

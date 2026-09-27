export function determineBadmintonMatchWinner(games) {
    let homeWins = 0;
    let guestWins = 0;

    for (const {homePoints, guestPoints} of games) {
        if (homePoints === null || guestPoints === null) {
            continue;
        }

        if (homePoints < 0 || guestPoints < 0 || homePoints > 21 || guestPoints > 21) {
            throw new Error('Invalid score found');
        }

        if ((homePoints >= 15 && homePoints - guestPoints >= 2) || homePoints === 21 && homePoints > guestPoints) {
            homeWins++;
        } else if ((guestPoints >= 15 && guestPoints - homePoints >= 2) || guestPoints === 21 && guestPoints > homePoints) {
            guestWins++;
        }
    }

    if (homeWins >= 2) {
        return 'HOME';
    }
    if (guestWins >= 2) {
        return 'GUEST';
    }
    return 'UNKNOWN';
}

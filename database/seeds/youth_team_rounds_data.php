<?php

use Database\Seeders\YouthTeamRoundSeeder;

/**
 * Three holdrunder in the same Season (2025) and Clubhouse that follow one
 * Youth Player, Aske Groth Jensen (U17), through the season:
 *
 *   1. "Ungdom - 1. runde"               — Aske on Hold 2 (1. HS, 2. HD)
 *   2. "Ungdom - 2. runde"               — Aske on Hold 1 (4. HS, 3. HD)
 *   3. "Ungdom - 3. runde (konflikter)"  — Aske on Hold 1 (3. HS, 2. HD) with Youth Player conflicts:
 *        - Within squad, youth above: Jakob Christensen (HS 2420) is placed below Aske (HS 2225).
 *        - Within squad, youth below: Lauge Almlund Højgaard (U19, HS 2677) is placed below
 *          Jesper Lauge Andersen (HS 2422).
 *        - Doubles: Victor R. Andersen + Jakob Christensen (5192) at 3. HD are stronger than
 *          Jesper Lauge Andersen + Aske (4417) at 2. HD and Lars Juncker + Lauge (4907) at 1. HD,
 *          so Jesper and Lars "Har U15/U17/U19 makker".
 *        - Across squads: Mathilde Hay-Schmidt (U19, DS 2113) on Hold 2 has more points than
 *          Nanna Reese (DS 1876) at 2. DS on Hold 1.
 *
 * Rounds 1 and 2 report no conflicts today, but only because Youth Players are not
 * validated on points: their youth placements would be conflicts for seniors.
 *
 * Players are Members by name; their Juli 2025 points are copied from the points table.
 * Every Squad is a 13-kamps hold and lists categories in the order they are created.
 */
$hold1 = [
    '1. MD' => ['Josefine Eggert Jackson', 'Victor R. Andersen'],
    '2. MD' => ['Hanne Juul Christensen', 'Lars Juncker'],
    '1. DS' => ['Tanja Damsgaard'],
    '2. DS' => ['Nanna Reese'],
    '1. HS' => ['Jesper Lauge Andersen'],
    '2. HS' => ['Lauge Almlund Højgaard'],
    '3. HS' => ['Jakob Christensen'],
    '4. HS' => ['Jesper Røikjær'],
    '1. DD' => ['Josefine Eggert Jackson', 'Hanne Juul Christensen'],
    '2. DD' => ['Tanja Damsgaard', 'Nanna Reese'],
    '1. HD' => ['Victor R. Andersen', 'Lars Juncker'],
    '2. HD' => ['Jesper Lauge Andersen', 'Jesper Røikjær'],
    '3. HD' => ['Lauge Almlund Højgaard', 'Jakob Christensen'],
];

$hold2 = [
    '1. MD' => ['Tine Gade', 'Nikolaj Hvidtfeldt Lassen'],
    '2. MD' => ['Anne-Birgitte Holm', 'Jannik Adler'],
    '1. DS' => ['Mathilde Hay-Schmidt'],
    '2. DS' => ['Cecillie Henriksen'],
    '1. HS' => ['Aske Groth Jensen'],
    '2. HS' => ['Magnus Venge-Jep'],
    '3. HS' => ['Thomas Martin Leth'],
    '4. HS' => ['Kaspar Holm'],
    '1. DD' => ['Mathilde Hay-Schmidt', 'Cecillie Henriksen'],
    '2. DD' => ['Tine Gade', 'Anne-Birgitte Holm'],
    '1. HD' => ['Nikolaj Hvidtfeldt Lassen', 'Jannik Adler'],
    '2. HD' => ['Aske Groth Jensen', 'Magnus Venge-Jep'],
    '3. HD' => ['Thomas Martin Leth', 'Kaspar Holm'],
];

// Round 2: Aske takes 4. HS and 3. HD on Hold 1; Jesper Røikjær drops to Hold 2.
$round2Hold1 = array_merge($hold1, [
    '2. HS' => ['Lauge Almlund Højgaard'],
    '3. HS' => ['Jakob Christensen'],
    '4. HS' => ['Aske Groth Jensen'],
    '2. HD' => ['Jesper Lauge Andersen', 'Jakob Christensen'],
    '3. HD' => ['Lauge Almlund Højgaard', 'Aske Groth Jensen'],
]);
$round2Hold2 = array_merge($hold2, [
    '1. HS' => ['Jesper Røikjær'],
    '2. HD' => ['Jesper Røikjær', 'Magnus Venge-Jep'],
]);

// Round 3: Aske moves up to 3. HS and 2. HD on Hold 1, above stronger seniors.
$round3Hold1 = array_merge($hold1, [
    '3. HS' => ['Aske Groth Jensen'],
    '4. HS' => ['Jakob Christensen'],
    '1. HD' => ['Lars Juncker', 'Lauge Almlund Højgaard'],
    '2. HD' => ['Jesper Lauge Andersen', 'Aske Groth Jensen'],
    '3. HD' => ['Victor R. Andersen', 'Jakob Christensen'],
]);
$round3Hold2 = $round2Hold2;

return [
    [
        'id' => 'U1kQm7RzT3vXa9LcY2pWn5Hd',
        'name' => 'Ungdom - 1. runde',
        'game_date' => '2025-09-13',
        'round' => 1,
        'squads' => [$hold1, $hold2],
    ],
    [
        'id' => 'U2bNf4JsE8gKq6TyV1mRc3Zx',
        'name' => 'Ungdom - 2. runde',
        'game_date' => '2025-10-04',
        'round' => 2,
        'squads' => [$round2Hold1, $round2Hold2],
    ],
    [
        'id' => 'U3hWd9PqL2sMx7BzG5kTj4Fy',
        'name' => YouthTeamRoundSeeder::CONFLICT_ROUND,
        'game_date' => '2025-11-01',
        'round' => 3,
        'squads' => [$round3Hold1, $round3Hold2],
    ],
];

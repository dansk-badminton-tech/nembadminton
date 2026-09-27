import test from 'node:test';
import assert from 'node:assert/strict';
import {determineBadmintonMatchWinner} from '../../../resources/js/admin-v2/views/check-team-fight/score.js';

const game = (homePoints, guestPoints) => ({homePoints, guestPoints});

test('two 15-point games decide the winning side', () => {
    assert.equal(determineBadmintonMatchWinner([game(15, 13), game(16, 14)]), 'HOME');
    assert.equal(determineBadmintonMatchWinner([game(13, 15), game(14, 16)]), 'GUEST');
});

test('a tied match is decided by the third game, including the 21-point cap', () => {
    assert.equal(determineBadmintonMatchWinner([game(15, 10), game(10, 15), game(21, 20)]), 'HOME');
    assert.equal(determineBadmintonMatchWinner([game(10, 15), game(15, 10), game(20, 21)]), 'GUEST');
});

test('an unfinished match has no winner', () => {
    assert.equal(determineBadmintonMatchWinner([game(15, 10), game(null, null), game(14, 13)]), 'UNKNOWN');
});

test('scores above the 21-point cap are rejected', () => {
    assert.throws(() => determineBadmintonMatchWinner([game(22, 20)]), /Invalid score/);
});

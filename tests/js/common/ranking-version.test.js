import test from 'node:test';
import assert from 'node:assert/strict';
import {
    rankingVersionSuffix,
    resolveRecommendedNewestRankingVersion,
} from '../../../resources/js/admin-v2/views/common/ranking-version.js';

// September was first published as 2026-09-02 and later re-published as 2026-09-01.
const versions = ['2026-09-02', '2026-09-01', '2026-08-01'];
const newest = ['2026-09-01', '2026-08-01'];

test('the newest version of a re-published month is recommended', () => {
    assert.equal(resolveRecommendedNewestRankingVersion(versions, newest, new Date(2026, 8, 20)), '2026-09-01');
});

test('without newest versions every version can be recommended', () => {
    assert.equal(resolveRecommendedNewestRankingVersion(versions, undefined, new Date(2026, 8, 20)), '2026-09-02');
});

test('versions of a re-published month show their date and which one is newest', () => {
    assert.equal(rankingVersionSuffix('2026-09-01', versions, newest), '(01.09 – nyeste)');
    assert.equal(rankingVersionSuffix('2026-09-02', versions, newest), '(02.09)');
});

test('a month with a single version has no suffix', () => {
    assert.equal(rankingVersionSuffix('2026-08-01', versions, newest), '');
});

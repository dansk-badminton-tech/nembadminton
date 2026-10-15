import test from 'node:test';
import assert from 'node:assert/strict';
import {internalRedirectPath} from '../../../resources/js/admin-v2/router/login-redirect.js';

test('an internal path is followed', () => {
    assert.equal(internalRedirectPath('/c-1/team-fight/abc/edit'), '/c-1/team-fight/abc/edit');
    assert.equal(internalRedirectPath('/c-1/members?tab=2#top'), '/c-1/members?tab=2#top');
});

test('no redirect is ignored', () => {
    assert.equal(internalRedirectPath(undefined), null);
    assert.equal(internalRedirectPath(null), null);
    assert.equal(internalRedirectPath(''), null);
});

test('a redirect to another site is ignored', () => {
    assert.equal(internalRedirectPath('//example.com/phish'), null);
    assert.equal(internalRedirectPath('/\\example.com'), null);
    assert.equal(internalRedirectPath('https://example.com'), null);
    assert.equal(internalRedirectPath('javascript:alert(1)'), null);
    assert.equal(internalRedirectPath('c-1/home'), null);
});

test('a redirect back to login is ignored', () => {
    assert.equal(internalRedirectPath('/login'), null);
    assert.equal(internalRedirectPath('/login?redirect=/c-1/home'), null);
});

test('a repeated query parameter is ignored', () => {
    assert.equal(internalRedirectPath(['/c-1/home', '/c-2/home']), null);
});

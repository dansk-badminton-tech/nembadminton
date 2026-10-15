/**
 * The path to return to after login, taken from the login page's `?redirect=` query.
 * Returns null unless it is a path inside the app, so a crafted link can't send the
 * user to another site after they log in.
 */
export function internalRedirectPath(redirect) {
    if (typeof redirect !== 'string' || !redirect.startsWith('/')) {
        return null
    }
    // "//host" and "/\host" are read by browsers as links to another site.
    if (redirect.startsWith('//') || redirect.startsWith('/\\')) {
        return null
    }
    if (redirect === '/login' || redirect.startsWith('/login?') || redirect.startsWith('/login/')) {
        return null
    }
    return redirect
}

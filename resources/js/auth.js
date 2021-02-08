import decode from 'jwt-decode'
import globals from './globals'

const AUTH_TOKEN_KEY = 'access_token'

export function setUser(newUser) {
    globals.user = newUser;
}

export function getUser() {
    return globals.user;
}

export function setAuthToken(token) {
    localStorage.setItem(AUTH_TOKEN_KEY, token)
}

export function getAuthToken() {
    return localStorage.getItem(AUTH_TOKEN_KEY)
}

export function isLoggedIn() {
    let authToken = getAuthToken()
    return !!authToken && !isTokenExpired(authToken)
}

export function logoutUser() {
    clearAuthToken()
}

export function clearAuthToken() {
    localStorage.removeItem(AUTH_TOKEN_KEY)
}

export function getUserInfo() {
    if (isLoggedIn()) {
        return decode(getAuthToken())
    }
}

function isTokenExpired(token) {
    let expirationDate = getTokenExpirationDate(token)
    return expirationDate < new Date()
}

function getTokenExpirationDate(encodedToken) {
    let token = decode(encodedToken)
    if (!token.exp) {
        return null
    }

    let date = new Date(0)
    date.setUTCSeconds(token.exp)

    return date
}

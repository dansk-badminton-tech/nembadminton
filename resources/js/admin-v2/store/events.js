/* App-wide event bus.
 *
 * Vue 3 removes $on/$off/$once from component instances, so the previous
 * `this.$root.$on(...)` / `this.$root.$emit(...)` pattern no longer works.
 * This is a direct replacement with no dependency.
 *
 * `on()` returns an unsubscribe function — call it from beforeUnmount.
 * The old $root.$on calls never unsubscribed, so components that mounted more
 * than once (e.g. PlayersListSearch) accumulated duplicate handlers.
 */
const listeners = new Map()

export function on(event, handler) {
    if (!listeners.has(event)) {
        listeners.set(event, new Set())
    }
    listeners.get(event).add(handler)

    return () => off(event, handler)
}

export function off(event, handler) {
    listeners.get(event)?.delete(handler)
}

export function emit(event, payload) {
    // Copy before iterating so a handler that unsubscribes mid-emit is safe.
    const handlers = listeners.get(event)
    if (handlers) {
        [...handlers].forEach(handler => handler(payload))
    }
}

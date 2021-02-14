import {ApolloClientInstance} from "./graphql";
import gql from 'graphql-tag'

export class ServiceWorkerHelper {
    /**
     * Register the service worker.
     */
    static registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            console.log('Service workers aren\'t supported in this browser.')
            return
        }

        return navigator.serviceWorker.register('/sw.js')
                        .then(() => this.initialiseServiceWorker())
    }

    static initialiseServiceWorker() {
        if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
            console.log('Notifications aren\'t supported.')
            return
        }

        if (Notification.permission === 'denied') {
            console.log('The user has blocked notifications.')
            return
        }

        if (!('PushManager' in window)) {
            console.log('Push messaging isn\'t supported.')
            return
        }

        return navigator.serviceWorker.ready
    }

    /**
     * Subscribe for push notifications.
     */
    static subscribe() {
        return navigator.serviceWorker.ready.then(registration => {
            const options = {userVisibleOnly: true}
            const vapidPublicKey = window.Laravel.vapidPublicKey

            if (vapidPublicKey) {
                options.applicationServerKey = ServiceWorkerHelper.urlBase64ToUint8Array(vapidPublicKey)
            }

            return registration.pushManager.subscribe(options)
                               .catch(e => {
                                   if (Notification.permission === 'denied') {
                                       console.log('Permission for Notifications was denied')
                                       this.pushButtonDisabled = true
                                   } else {
                                       console.log('Unable to subscribe to push.', e)
                                       this.pushButtonDisabled = false
                                   }
                               })
        })
    }

    /**
     * Unsubscribe from push notifications.
     */
    static unsubscribe() {
        return navigator.serviceWorker.ready.then(registration => {
            return registration.pushManager.getSubscription().catch(e => {
                console.log('Error thrown while unsubscribing.', e)
            })
        })
    }

    /**
     * Send a request to the server to update user's subscription.
     *
     * @param {PushSubscription} subscription
     */
    static updateSubscription(subscription) {
        const key = subscription.getKey('p256dh')
        const token = subscription.getKey('auth')
        const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0]

        const data = {
            endpoint: subscription.endpoint,
            publicKey: key
                       ? btoa(String.fromCharCode.apply(null, new Uint8Array(key)))
                       : null,
            authToken: token
                       ? btoa(String.fromCharCode.apply(null, new Uint8Array(token)))
                       : null,
            contentEncoding
        }

        ApolloClientInstance.mutate({
                                        mutation: gql`
                                            mutation($input: SubscribeWebPushInput!){
                                                subscribeWebPush(input: $input){
                                                    id
                                                }
                                            }
                                        `,
                                        variables: {
                                            input: data
                                        }
                                    })
    }

    /**
     * Send a requst to the server to delete user's subscription.
     *
     * @param {PushSubscription} subscription
     */
    static deleteSubscription(subscription) {
        ApolloClientInstance.mutate({
                                        mutation: gql`
                                            mutation($input: UnsubscribeWebPushInput!){
                                                unsubscribeWebPush(input: $input)
                                            }
                                        `,
                                        variables: {
                                            input: {
                                                endpoint: subscription.endpoint
                                            }
                                        }
                                    })
    }

    /**
     * https://github.com/Minishlink/physbook/blob/02a0d5d7ca0d5d2cc6d308a3a9b81244c63b3f14/app/Resources/public/js/app.js#L177
     *
     * @param  {String} base64String
     * @return {Uint8Array}
     */
    static urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4)
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/')

        const rawData = window.atob(base64)
        const outputArray = new Uint8Array(rawData.length)

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i)
        }

        return outputArray
    }

    /**
     * Toggle push notifications subscription.
     */
    togglePush() {
        if (this.isPushEnabled) {
            this.unsubscribe()
        } else {
            this.subscribe()
        }
    }
}

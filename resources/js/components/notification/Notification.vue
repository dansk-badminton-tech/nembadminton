<template>
    <fragment>
        <div v-if="!$apollo.loading && !hasBadmintonId">
            <p>For at notificationer virker skal du angive dit Badminton ID</p>
            <ChangeProfile :only-badminton-id="true"></ChangeProfile>
        </div>
        <div v-if="!$apollo.loading && hasBadmintonId">
            <h1 class="title is-5">Web Push</h1>
            <b-button
                v-if="!isPushEnabled"
                :loading="this.subscribingWebPushLoading"
                :disabled="!isPushPossible"
                type="is-primary"
                @click="subscribe">
                Aktiver
            </b-button>
            <b-button
                v-if="isPushEnabled"
                :disabled="!isPushPossible"
                :loading="this.unsubscribingWebPushLoading"
                type="is-primary"
                @click="unsubscribe">
                Deaktiver
            </b-button>
            <p class="mt-2">Personlig push beskeder direkte til dig fra din browser.</p>
            <hr/>
            <h1 class="title is-5">Email</h1>
            <b-button
                v-if="!$apollo.loading && !isEmailSubscribed"
                :loading="$apollo.queries.me.loading || updateSubscriptionEmailLoading"
                type="is-primary"
                @click="emailSubscribe(true)">
                Aktiver
            </b-button>
            <b-button
                v-if="!$apollo.loading && isEmailSubscribed"
                :loading="$apollo.queries.me.loading || updateSubscriptionEmailLoading"
                type="is-primary"
                @click="emailSubscribe(false)">
                Deaktiver
            </b-button>
            <p v-if="!$apollo.loading" class="mt-2">Sender til: <strong>{{ me.email }}</strong></p>
        </div>
    </fragment>
</template>

<script>
import {ServiceWorkerHelper} from "../../service-worker";
import gql from "graphql-tag"
import ChangeProfile from "../../views/ChangeProfile";

export default {
    name: "Notification",
    components: {ChangeProfile},
    data() {
        return {
            isPushEnabled: false,
            pushButtonDisabled: false,
            subscribingWebPushLoading: false,
            unsubscribingWebPushLoading: false,
            updateSubscriptionEmailLoading: false,
            isPushPossible: true
        }
    },
    computed: {
        isEmailSubscribed: function () {
            return this.me.subscriptionSettings?.email || false
        },
        hasBadmintonId: function () {
            return this.me?.player_id || false
        }
    },
    mounted() {
        let serviceWorker = ServiceWorkerHelper.registerServiceWorker();
        if (serviceWorker) {
            serviceWorker.then(registration => {
                registration
                    .pushManager
                    .getSubscription()
                    .then(subscription => {
                        this.pushButtonDisabled = false

                        if (!subscription) {
                            return
                        }

                        ServiceWorkerHelper.updateSubscription(subscription)

                        this.isPushEnabled = true
                    })
                    .catch(e => {
                        console.log('Error during getSubscription()', e)
                    })
            })
        } else {
            this.isPushPossible = false
        }
    },
    apollo: {
        me: {
            query: gql`
                query {
                    me{
                        id
                        email
                        player_id
                        subscriptionSettings{
                            id
                            email
                        }
                    }
                }
            `
        }
    },
    methods: {
        subscribe() {
            this.subscribingWebPushLoading = true
            ServiceWorkerHelper.subscribe().then(subscription => {
                this.isPushEnabled = true

                ServiceWorkerHelper.updateSubscription(subscription)
            }).finally(() => {
                this.subscribingWebPushLoading = false
            })
        },
        unsubscribe() {
            this.unsubscribingWebPushLoading = true
            ServiceWorkerHelper.unsubscribe().then(subscription => {
                if (!subscription) {
                    this.isPushEnabled = false
                    return
                }

                subscription.unsubscribe().then(() => {
                    ServiceWorkerHelper.deleteSubscription(subscription)
                    this.isPushEnabled = false
                }).catch(e => {
                    console.log('Unsubscription error: ', e)
                    this.pushButtonDisabled = false
                }).finally(() => {
                    this.unsubscribingWebPushLoading = false
                })
            });
        },
        emailSubscribe(subscribe) {
            this.unsubscribingEmailLoading = true
            let variables = {
                input: {
                    subscriptionSettings: {
                        upsert: {
                            email: subscribe
                        }
                    }
                }
            };
            if (this.me.subscriptionSettings?.id) {
                variables.input.subscriptionSettings.upsert.id = this.me.subscriptionSettings.id
            }
            this
                .$apollo
                .mutate(
                    {
                        mutation:
                            gql`
                                mutation updateMe($input: UpdateMe!){
                                    updateMe(input: $input){
                                        id
                                        name
                                        email
                                        subscriptionSettings{
                                            id
                                            email
                                        }
                                    }
                                }
                            `,
                        variables
                    })
                .then(() => {
                    this.$apollo.queries.me.refresh()
                })
                .finally(() => {
                    this.updateSubscriptionEmailLoading = false
                })
        }
    }
}
</script>

<style scoped>

</style>

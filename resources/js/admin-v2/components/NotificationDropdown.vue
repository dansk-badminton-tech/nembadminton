<script>
import gql from "graphql-tag";
import Notification from "../../queries/notifications.graphql"
import {mapState} from "vuex";

export default {
    name: "NotificationDropdown",
    computed: {
        parsedNotification() {
            return this.notifications?.map((notification) => {
                return {
                    ...notification,
                    dataParsed: JSON.parse(notification.data)
                }
            })
        },
        ...mapState([
                        'userId'
                    ])
    },
    data() {
        return {}
    },
    methods: {
        readAll() {
            this.$apollo.mutate(
                {
                    mutation: gql`
                        mutation notificationRead{
                            notificationRead
                        }
                    `,
                    refetchQueries: [{query: Notification}]
                })
                .then(() => {
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-success',
                                                  message: `Alle markeret som læst`
                                              })
                })
                .catch(() => {
                    this.$buefy.snackbar.open({
                                                  duration: 4000,
                                                  type: 'is-danger',
                                                  message: `Kunne ikke marker dem som læst :(`
                                              })
                })
        },
        timeAgo(date) {
            const currentDate = new Date(date);
            const now = new Date();
            const seconds = Math.floor((now - currentDate) / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);
            const months = Math.floor(days / 30);
            const years = Math.floor(days / 365);

            if (seconds < 60) {
                return `${seconds} seconds ago`;
            }
            if (minutes < 60) {
                return `${minutes} minutes ago`;
            }
            if (hours < 24) {
                return `${hours} hours ago`;
            }
            if (days < 30) {
                return `${days} days ago`;
            }
            if (months < 12) {
                return `${months} months ago`;
            }
            return `${years} years ago`;
        }
    },
    apollo: {
        notifications: {
            query: Notification,
            skip(){
                return this.userId === null
            },
            subscribeToMore: {
                document: gql`subscription notifications($userId: Int!){
                    notifications(userId: $userId){
                        id
                        type
                        data
                        createdAt
                        readAt
                    }
                  }`,
                skip () {
                    return this.userId === null
                },
                variables () {
                    return {
                        userId: this.userId
                    }
                },
                // Mutate the previous result
                updateQuery: (previousResult, { subscriptionData }) => {
                    return {
                        notifications: [
                            ...previousResult.notifications,
                            subscriptionData.data.notifications
                        ]
                    }
                },
            }
        }
    }
}
</script>

<template>
    <b-dropdown
        position="is-bottom-left"
        append-to-body
        aria-role="list"
        trap-focus
        class="navbar-item"
    >
        <template #trigger>
            <a
                class="navbar-item has-divider"
                title="Notificationer">
                <b-icon
                    icon="bell-outline"
                    class="is-marginless"
                />
                <b-tag type="is-danger" v-show="parsedNotification?.length !== 0">{{ parsedNotification?.length }}</b-tag>
                <span class="is-hidden-desktop">Notifikationer</span>
            </a>
        </template>
        <div v-show="parsedNotification?.length === 0" class="dropdown-item">
            Ingen notifikationer
        </div>
        <b-dropdown-item
            aria-role="listitem"
            :focusable="false"
            :scrollable="true"
            :custom="true"
            max-height="300px"
            v-for="notification in parsedNotification"
            :key="notification.id">
            <div class="media">
                <!--                    <figure class="media-left">-->
                <!--                        <p class="image is-64x64">-->
                <!--                            <img src="https://bulma.io/images/placeholders/128x128.png">-->
                <!--                        </p>-->
                <!--                    </figure>-->
                <div class="media-content">
                    <h3><strong>{{ notification.dataParsed.title }}</strong> <small>{{ timeAgo(notification.createdAt) }}</small></h3>
                    <small style="white-space: pre-wrap;">{{ notification.dataParsed.message }}</small>
                </div>
                <!--                    <div class="media-right">-->
                <!--                        <button class="delete"></button>-->
                <!--                    </div>-->
            </div>
        </b-dropdown-item>
        <hr v-show="parsedNotification?.length !== 0" class="dropdown-divider">
        <a v-show="parsedNotification?.length !== 0" @click.prevent="readAll" href="#" class="dropdown-item">
            <b-icon size="is-small" icon="check"></b-icon>
            Marker alle som læst
        </a>
    </b-dropdown>
</template>

<style scoped>

</style>

<script>
import gql from "graphql-tag";
import Notification from "../../queries/notifications.graphql"
import {mapState} from "vuex";

export default {
    name: "NotificationDropdown",
    computed: {
        parsedNotification() {
            return this.notifications?.map((notification) => {
                const dataParsed = JSON.parse(notification.data);
                return {
                    ...notification,
                    dataParsed,
                    title: dataParsed.title,
                    // Different notifications use different keys for the body.
                    body: dataParsed.body ?? dataParsed.message ?? '',
                    actionUrl: dataParsed.action_url ?? null,
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
        openAction(notification) {
            if (!notification.actionUrl) return;
            const url = notification.actionUrl;
            // Keep internal links inside the SPA, open external links in a new tab.
            if (url.startsWith('http')) {
                const origin = window.location.origin;
                if (url.startsWith(origin)) {
                    this.$router.push(url.substring(origin.length));
                } else {
                    window.open(url, '_blank', 'noopener');
                }
            } else {
                this.$router.push(url);
            }
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
                return 'lige nu';
            }
            if (minutes < 60) {
                return `for ${minutes} min. siden`;
            }
            if (hours < 24) {
                return `for ${hours} ${hours === 1 ? 'time' : 'timer'} siden`;
            }
            if (days < 30) {
                return `for ${days} ${days === 1 ? 'dag' : 'dage'} siden`;
            }
            if (months < 12) {
                return `for ${months} ${months === 1 ? 'måned' : 'måneder'} siden`;
            }
            return `for ${years} ${years === 1 ? 'år' : 'år'} siden`;
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
                            subscriptionData.data.notifications,
                            ...previousResult.notifications
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
        aria-role="list"
        trap-focus
        class="navbar-item notification-dropdown"
    >
        <template #trigger>
            <a
                class="navbar-item has-divider"
                title="Notificationer">
                <b-icon
                    icon="bell-outline"
                    class="is-marginless"
                />
                <b-tag rounded type="is-danger" v-show="parsedNotification?.length !== 0">{{ parsedNotification?.length }}</b-tag>
                <span class="is-hidden-desktop">Notifikationer</span>
            </a>
        </template>

        <div class="notification-panel">
            <div class="notification-header">
                <span class="notification-header-title">Notifikationer</span>
                <b-tag
                    v-show="parsedNotification?.length !== 0"
                    rounded
                    type="is-danger"
                    size="is-small">
                    {{ parsedNotification?.length }} nye
                </b-tag>
            </div>

            <div v-show="parsedNotification?.length === 0" class="notification-empty">
                <b-icon icon="bell-sleep-outline" size="is-large" class="notification-empty-icon"></b-icon>
                <p>Ingen nye notifikationer</p>
            </div>

            <div class="notification-list">
                <b-dropdown-item
                    aria-role="listitem"
                    :focusable="false"
                    :custom="true"
                    v-for="notification in parsedNotification"
                    :key="notification.id"
                    class="notification-item">
                    <div class="notification-item-inner">
                        <div class="notification-item-icon">
                            <b-icon icon="bullhorn-outline"></b-icon>
                        </div>
                        <div class="notification-item-content">
                            <div class="notification-item-top">
                                <strong class="notification-item-title">{{ notification.title }}</strong>
                                <small class="notification-item-time">{{ timeAgo(notification.createdAt) }}</small>
                            </div>
                            <p v-if="notification.body" class="notification-item-body">{{ notification.body }}</p>
                            <b-button
                                v-if="notification.actionUrl"
                                type="is-info"
                                size="is-small"
                                icon-left="arrow-right-circle"
                                class="notification-item-action"
                                @click="openAction(notification)">
                                Se detaljer
                            </b-button>
                        </div>
                    </div>
                </b-dropdown-item>
            </div>

            <div v-show="parsedNotification?.length !== 0" class="notification-footer">
                <a @click.prevent="readAll" href="#" class="notification-footer-action">
                    <b-icon size="is-small" icon="check-all"></b-icon>
                    <span>Marker alle som læst</span>
                </a>
            </div>
        </div>
    </b-dropdown>
</template>

<style scoped>
.notification-panel {
    width: 360px;
    max-width: 90vw;
}

.notification-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.notification-header-title {
    font-weight: 700;
    font-size: 0.95rem;
}

.notification-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    color: #9a9a9a;
    text-align: center;
}

.notification-empty-icon {
    margin-bottom: 0.5rem;
    color: #dbdbdb;
}

.notification-list {
    max-height: 360px;
    overflow-y: auto;
}

.notification-item {
    padding: 0 !important;
}

.notification-item-inner {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f5f5f5;
    transition: background-color 0.15s ease;
    white-space: normal;
}

.notification-item-inner:hover {
    background-color: #f7fafc;
}

.notification-item-icon {
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #eef6fc;
    color: #3e8ed0;
}

.notification-item-content {
    flex: 1;
    min-width: 0;
}

.notification-item-top {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.5rem;
}

.notification-item-title {
    font-size: 0.9rem;
    color: #363636;
    word-break: break-word;
}

.notification-item-time {
    flex-shrink: 0;
    color: #b5b5b5;
    font-size: 0.7rem;
    white-space: nowrap;
}

.notification-item-body {
    margin-top: 0.15rem;
    font-size: 0.8rem;
    color: #6b6b6b;
    white-space: pre-wrap;
    word-break: break-word;
}

.notification-item-action {
    margin-top: 0.5rem;
}

.notification-footer {
    border-top: 1px solid #f0f0f0;
}

.notification-footer-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.65rem 1rem;
    color: #3e8ed0;
    font-size: 0.85rem;
    font-weight: 600;
}

.notification-footer-action:hover {
    background-color: #f7fafc;
}
</style>

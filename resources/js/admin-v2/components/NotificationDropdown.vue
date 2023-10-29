<script>
import gql from "graphql-tag";
import Notification from "../../queries/notifications.graphql"

export default {
    name: "NotificationDropdown",
    computed: {
        parsedNotification(){
            return this.notifications?.map((notification) => {
                notification.dataParsed = JSON.parse(notification.data)
                return notification
            })
        }
    },
    methods: {
        readAll(){
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
        timeAgo(date){
            const currentDate = new Date(date);
            const now = new Date();
            const seconds = Math.floor((now - currentDate) / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);
            const months = Math.floor(days / 30);
            const years = Math.floor(days / 365);

            if (seconds < 60) return `${seconds} seconds ago`;
            if (minutes < 60) return `${minutes} minutes ago`;
            if (hours < 24) return `${hours} hours ago`;
            if (days < 30) return `${days} days ago`;
            if (months < 12) return `${months} months ago`;
            return `${years} years ago`;
        }
    },
    apollo: {
        notifications: {
            query: Notification
        }
    }
}
</script>

<template>
    <b-dropdown
        position="is-bottom-left"
        append-to-body
        aria-role="menu"
        trap-focus
        class="navbar-item"
    >
        <template #trigger>
            <a
                href="#"
                class="navbar-item has-divider"
                title="Notificationer">
                <b-icon
                    icon="bell-outline"
                    class="is-marginless"
                />
                <b-tag type="is-danger" v-show="parsedNotification?.length !== 0">{{parsedNotification?.length}}</b-tag>
                <span class="is-hidden-desktop">Notifikationer</span>
            </a>
        </template>
        <b-dropdown-item
            aria-role="menu-item"
            :focusable="false"
            custom
            paddingless
            expanded
            scrollable
            max-height="300px"
        >
            <div v-if="parsedNotification?.length === 0" class="dropdown-item">
                Ingen notifikationer
            </div>
            <div class="dropdown-item" v-for="notification in parsedNotification">
                <article class="media notification-width">
<!--                    <figure class="media-left">-->
<!--                        <p class="image is-64x64">-->
<!--                            <img src="https://bulma.io/images/placeholders/128x128.png">-->
<!--                        </p>-->
<!--                    </figure>-->
                    <div class="media-content">
                        <div class="content is-marginless">
                            <p>
                                <strong>{{notification.dataParsed.title}}</strong> <small>{{timeAgo(notification.createdAt)}}</small>
                                <br>
                                {{notification.dataParsed.message}}
                            </p>
                        </div>
                    </div>
<!--                    <div class="media-right">-->
<!--                        <button class="delete"></button>-->
<!--                    </div>-->
                </article>
            </div>
            <hr v-show="parsedNotification?.length !== 0" class="dropdown-divider">
            <a v-show="parsedNotification?.length !== 0" @click.prevent="readAll" href="#" class="dropdown-item">
                <b-icon size="is-small" icon="check"></b-icon>
                Marker alle some læst
            </a>
        </b-dropdown-item>
    </b-dropdown>
</template>

<style scoped>
    .notification-item:hover {
        background-color: hsl(0, 0%, 96%);
        color: hsl(0, 0%, 4%);
        cursor: pointer;
    }

    @media only screen and (max-width: 600px) {
        .notification-width {
            min-width: 200px;
        }
    }

    @media only screen and (min-width: 768px) {
        .notification-width {
            min-width: 400px;
        }
    }
</style>
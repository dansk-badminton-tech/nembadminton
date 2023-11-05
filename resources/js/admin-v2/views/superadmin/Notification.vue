<script>
import HeroBar from "../../components/HeroBar.vue";
import TitleBar from "../../components/TitleBar.vue";
import RankingVersionSelect from "../common/RankingVersionSelect.vue";
import gql from "graphql-tag";

export default {
    name: "Notification",
    components: {RankingVersionSelect, TitleBar, HeroBar},
    data() {
        return {
            titleStack: ['Superadmin', 'Notification'],
            title: null,
            types: [
                {value: "Release", label: "Release"}
            ],
            type: null,
            message: null,
            sendToAll: false,
            selectedReceivers: [],
            loading: false
        }
    },
    apollo: {
        notificationReceivers: {
            query: gql`
                query notificationReceivers{
                    notificationReceivers{
                        id
                        name
                    }
                }
            `
        }
    },
    methods: {
        createNotification() {
            this.loading = true
            this.$apollo.mutate({
                                    mutation: gql`
                                        mutation notificationSend($input: NotificationSendInput!){
                                            notificationSend(input: $input)
                                        }
                                    `,
                                    variables: {
                                        input: {
                                            receivers: {
                                                all: this.sendToAll,
                                                users: this.selectedReceivers
                                            },
                                            message: {
                                                title: this.title,
                                                body: this.message,
                                                type: this.type.value
                                            }
                                        }
                                    }
                                })
                .then(() => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 2000,
                            type: 'is-success',
                            message: `Notification oprettet`
                        })
                })
                .catch(() => {
                    this.$buefy.snackbar.open(
                        {
                            duration: 2000,
                            type: 'is-danger',
                            message: `Kunne ikke oprette notification :(`
                        })
                })
                .finally(() => {
                    this.loading = false
                })
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Notification
        </hero-bar>
        <section class="section is-main-section">
            <div class="column">
                <b-field label="Navn">
                    <b-input v-model="title" placeholder="Nyt release"></b-input>
                </b-field>
                <b-field label="Type">
                    <b-select v-model="type">
                        <option v-for="type in types" :value="type" :key="type.value">{{ type.label }}</option>
                    </b-select>
                </b-field>
                <b-field label="Receivers">
                    <b-checkbox v-model="sendToAll">All</b-checkbox>
                    <b-select
                        v-model="selectedReceivers"
                        multiple
                        native-size="8"
                    >
                        <option v-for="notificationReceiver in notificationReceivers" :value="notificationReceiver.id" :key="notificationReceiver.id">{{ notificationReceiver.name }}</option>
                    </b-select>
                </b-field>
                <b-field label="Message">
                    <b-input v-model="message" type="textarea"></b-input>
                </b-field>
                <b-button :loading="loading" class="mt-2" icon-left="content-save" @click="createNotification">Opret notification</b-button>
            </div>
        </section>
    </div>
</template>

<style scoped>

</style>

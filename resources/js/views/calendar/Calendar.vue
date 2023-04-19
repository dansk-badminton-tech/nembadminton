<template>
    <div>
        <b-loading v-model="$apollo.queries.calendarEvents.loading" :can-cancel="true"></b-loading>
        <vue-cal style="min-height: 500px" :events="calendarEvents" :on-event-click="onEventClick" />
        <b-modal v-model="showModal">
            <div class="card">
                <div class="card-header">
                    <p class="card-header-title">
                        {{ selectedEvent.title }}
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <div v-html="selectedEvent.contentFull"></div>
                        <br>
                        <strong>Event details:</strong>
                        <ul>
                            <li>Start: {{ selectedEvent.start && selectedEvent.start.formatTime() }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

<script>

import VueCal from 'vue-cal'
import 'vue-cal/dist/vuecal.css'
import gql from "graphql-tag";

export default {
    name: "Calendar",
    components: {VueCal},
    data: () => ({
        events: [],
        calendarEvents: [],
        showModal: false,
        selectedEvent: {}
    }),
    apollo: {
        calendarEvents: {
            query: gql`
                query {
                  calendarEvents{
                    start
                    end
                    title
                    content
                    contentFull
                  }
                }
            `
        }
    },
    methods: {
        onEventClick(event, e){
            this.selectedEvent = event
            this.showModal = true
//            this.$buefy.dialog.alert({
//                                         title: event.title,
//                                         message: event.contentFull,
//                                         confirmText: 'Ok'
//                                     })

            e.stopPropagation()
        }
    }
}
</script>

<style scoped>

</style>

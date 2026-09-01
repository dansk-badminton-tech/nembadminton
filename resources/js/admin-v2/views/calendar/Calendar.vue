<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Kalender
        </hero-bar>
        <section class="section is-main-section">
            <b-loading v-model="$apollo.queries.calendarEvents.loading" :is-full-page="false" :can-cancel="true"></b-loading>
            <div class="calendar-parent">
                <calendar-month
                    :items="eventItems"
                    :display-week-numbers="true"
                    @click-item="onEventClick"/>
            </div>

            <b-modal v-model="showModal">
                <div class="card">
                    <div class="card-header">
                        <p class="card-header-title">
                            {{ selectedEvent.title }}
                        </p>
                    </div>
                    <div class="card-content">
                        <div class="content">
                            <div v-html="selectedEvent?.url"></div>
                            <br>
                            <strong>Event details:</strong>
                            <ul>
                                <li>Start: {{ selectedEvent.startDate && formatTime(selectedEvent.startDate) }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </b-modal>
        </section>
    </div>
</template>

<script>

import gql from "graphql-tag";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";
import CalendarMonth from "../../components/CalendarMonth.vue";

export default {
    name: "Calendar",
    components: {HeroBar, TitleBar, CalendarMonth},
    data: () => ({
        titleStack: ['Admin', 'Kalender'],
        events: [],
        calendarEvents: [],
        showModal: false,
        selectedEvent: {},
        eventItems: []
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
                    matchId
                  }
                }
            `,
            result({data}, key){
                this.eventItems = data[key].map(e => ({
                    id: e.matchId,
                    startDate: e.start,
                    endDate: e.end,
                    title: e.title,
                    url: e.contentFull
                }))
            }
        }
    },
    methods: {
        formatTime(str){
            return new Date(str).toLocaleTimeString('da-DK', {
                hour: '2-digit',
                minute: '2-digit'
            })
        },
        onEventClick(event) {
            this.selectedEvent = event
            this.showModal = true
        }
    }
}
</script>

<style scoped>
    .calendar-parent {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        overflow-x: hidden;
        overflow-y: hidden;
        max-height: 80vh;
        min-height: 800px;
        background-color: white;
    }
</style>

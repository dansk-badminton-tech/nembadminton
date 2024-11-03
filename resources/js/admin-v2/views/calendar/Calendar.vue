<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Kalender
        </hero-bar>
        <section class="section is-main-section">
            <b-loading v-model="$apollo.queries.calendarEvents.loading" :is-full-page="false" :can-cancel="true"></b-loading>
            <div class="calendar-parent">
                <calendar-view
                    :items="eventItems"
                    :show-date="showDate"
                    :startingDayOfWeek="1"
                    @click-item="onEventClick"
                    class="theme-default">
                    <calendar-view-header
                        slot="header"
                        slot-scope="t"
                        :header-props="t.headerProps"
                        @input="setShowDate" />
                </calendar-view>
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
                            <div v-html="selectedEvent?.originalItem?.url"></div>
                            <br>
                            <strong>Event details:</strong>
                            <ul>
                                <li>Start: {{ selectedEvent.startDate && selectedEvent.startDate.formatTime() }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </b-modal>
        </section>
    </div>
</template>

<script>

import { CalendarView, CalendarViewHeader } from "vue-simple-calendar"
import "vue-simple-calendar/static/css/default.css"

import gql from "graphql-tag";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";

export default {
    name: "Calendar",
    components: {HeroBar, TitleBar, CalendarView, CalendarViewHeader},
    data: () => ({
        titleStack: ['Admin', 'Kalender'],
        events: [],
        calendarEvents: [],
        showModal: false,
        selectedEvent: {},
        showDate: new Date(),
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
        setShowDate(d) {
            this.showDate = d;
        },
        toTimeStr(str){
            return str.substring(10, 16);
        },
        toDateObject(str){
            return new Date(str.substring(0,10))
        },
        onEventClick(event, e) {
            this.selectedEvent = event
            this.showModal = true
            console.log(event, e)
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

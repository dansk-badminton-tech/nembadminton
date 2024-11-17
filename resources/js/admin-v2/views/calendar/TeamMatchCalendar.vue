<script>
import gql from "graphql-tag";
import { CalendarView, CalendarViewHeader } from "vue-simple-calendar"
import "./default-theme.css"

export default {
    name: "TeamMatchCalendar",
    components: {CalendarView, CalendarViewHeader},
    props: {
        clubs: {
            type: Array,
            required: true
        },
        selectedDates: {
            type: Array,
            required: false,
            default: () => ([])
        }
    },
    data(){
        return {
            showModal: false,
            selectedEvent: {},
            showDate: new Date(),
            eventItems: []
        }
    },
    computed: {
        eventsAndCancellations(){
            if(!this.calendarEvents) return []
            const events = this.calendarEvents.map(e => ({
                id: e.matchId+e.title,
                startDate: e.start,
                endDate: e.end,
                title: e.title,
                url: e.contentFull
            }))
            const selectedDates = this.selectedDates.map(e => ({
                id: Math.random(),
                startDate: e.startDate,
                endDate: e.endDate,
                title: e.title,
                classes: e.classes || ''
            }))
            events.push(...selectedDates)
            return events
        }
    },
    apollo: {
        calendarEvents: {
            query: gql`
                query calendarEvents($clubIds: [Int!]!){
                  calendarEvents(clubIds: $clubIds){
                    start
                    end
                    title
                    content
                    contentFull
                    matchId
                  }
                }
            `,
            variables(){
                return {
                    clubIds: this.clubs.map(c => parseInt(c.id))
                }
            },
            result({data}, key){
                this.eventItems = data[key].map(e => ({
                    id: e.matchId+e.title,
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
        onClickItem(item, e) {
            this.selectedEvent = item
            this.showModal = true
        },
        onSelectionFinish([startDate, endDate, windowEvent]){
            console.log(startDate, endDate, windowEvent)
        },
        onClickDate(selectedDate){
            console.log(selectedDate)
        }
    }
}
</script>

<template>
    <div>
        <strong class="title is-4" v-show="$apollo.queries.calendarEvents.loading">Henter kalender fra badmintonplayer.dk... <b-icon icon="loading" customClass="mdi-spin" /></strong>
        <div class="calendar-parent">
            <calendar-view
                :items="eventsAndCancellations"
                :show-date="showDate"
                :startingDayOfWeek="1"
                :displayWeekNumbers="true"
                :enable-date-selection="true"
                @date-selection-finish="onSelectionFinish"
                @click-item="onClickItem"
                @click-date="onClickDate"
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
                        {{ selectedEvent?.title }}
                    </p>
                </div>
                <div class="card-content">
                    <div class="content">
                        <div v-html="selectedEvent?.originalItem?.url"></div>
                        <br>
                        <strong>Event details:</strong>
                        <ul>
                            <li>Start: {{ selectedEvent?.originalItem?.startDate}}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </b-modal>
    </div>
</template>

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

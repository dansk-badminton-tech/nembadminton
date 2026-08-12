<script>
import gql from "graphql-tag";
// TODO(vue3): vue-simple-calendar@5 is a Vue 2 build (peerDependencies.vue
// ^2.6.12) and cannot run without @vue/compat. The calendar markup below is
// commented out until it is replaced with a Vue 3 compatible calendar.
// import { CalendarView, CalendarViewHeader } from "vue-simple-calendar"
import "./default-theme.css"

export default {
    name: "TeamMatchCalendar",
    props: {
        clubs: {
            type: Array,
            required: true
        },
        selectedDates: {
            type: Array,
            required: false,
            default: () => ([])
        },
        cancellationCollector: {
            type: Object,
            required: false,
            default: () => ({})
        }
    },
    data(){
        return {
            showModal: false,
            selectedEvent: {},
            showDate: new Date(),
            eventItems: [],
            cancellationEvents: [],
            currentDate: new Date(),
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
            const cancellationsAggregate = this.cancellationEvents.map(e => ({
                id: e.start,
                startDate: e.start,
                endDate: e.end,
                title: e.title,
                url: e.contentFull,
                classes: 'has-background-danger-light'
            }))
            events.push(...selectedDates)
            events.push(...cancellationsAggregate)
            return events
        }
    },
    apollo: {
        cancellationEvents: {
            query: gql`
                query cancellationEvents($id: ID!){
                    cancellationEvents(id: $id){
                        content
                        contentFull
                        end
                        start
                        title
                    }
                },
            `,
            variables(){
                return {
                    id: this.cancellationCollector.id
                }
            },
            skip(){
                return !this.cancellationCollector.hasOwnProperty('id')
            }
        },
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
        }
    }
}
</script>

<template>
    <div>
        <strong class="title is-4" v-show="$apollo.queries.calendarEvents.loading">Henter kalender fra badmintonplayer.dk... <b-icon icon="loading" customClass="mdi-spin" /></strong>
        <b-message type="is-warning" :closable="false">
            Kalenderen er midlertidigt utilgængelig.
        </b-message>
        <!-- TODO(vue3): restore once a Vue 3 compatible calendar replaces
             vue-simple-calendar@5 (Vue 2 only). See the import above.
        <div class="calendar-parent">
            <calendar-view
                :value="currentDate"
                @input="currentDate = $event"
                :items="eventsAndCancellations"
                :show-date="showDate"
                :startingDayOfWeek="1"
                :displayWeekNumbers="true"
                :enable-date-selection="true"
                @click-item="onClickItem"
                class="theme-default">
                <calendar-view-header
                    slot="header"
                    slot-scope="t"
                    :header-props="t.headerProps"
                    @input="setShowDate">
                </calendar-view-header>
            </calendar-view>
        </div>
        -->

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
                        <strong>Detaljer:</strong>
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

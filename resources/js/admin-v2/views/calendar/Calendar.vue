<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Kalender
        </hero-bar>
        <section class="section is-main-section">
            <div class="buttons">
                <b-button>2022/2023</b-button>
                <b-button>2023/2024</b-button>
            </div>
            <div class="columns is-multiline" v-for="event in calendarEvents">
                <div class="column is-full">
                    <div class="columns">
                        <div class="column is-one-fifth is-flex is-justify-content-center is-align-items-center is-flex-direction-column is-gapless">
                            <span class="is-size-5">{{toDateObject(event.start).toLocaleString('da-dk', {  weekday: 'short' })}}</span>
                            <span class="is-size-2">{{toDateObject(event.start).toLocaleString('da-dk', {  day: '2-digit' })}}</span>
                        </div>
                        <div class="column is-one-quarter is-flex is-justify-content-space-evenly is-flex-direction-column">
                            <div><p><b-icon icon="clock-time-four" size="is-small"></b-icon>{{toTimeStr(event.start)}} - {{toTimeStr(event.end)}}</p></div>
                            <div><p><b-icon icon="map-marker" size="is-small"></b-icon> At valby hallen</p></div>
                        </div>
                        <div class="column is-flex is-justify-content-space-evenly is-flex-direction-column">
                            <p>{{event.title}}</p>
                            <p v-html="event.contentFull"></p>
                        </div>
                    </div>
                </div>
            </div>
            <b-loading v-model="$apollo.queries.calendarEvents.loading" :can-cancel="true"></b-loading>
            <vue-cal style="min-height: 500px" :events="calendarEvents" :on-event-click="onEventClick"/>
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
        </section>
    </div>
</template>

<script>

import VueCal from 'vue-cal'
import 'vue-cal/dist/vuecal.css'
import gql from "graphql-tag";
import TitleBar from "../../components/TitleBar.vue";
import HeroBar from "../../components/HeroBar.vue";

export default {
    name: "Calendar",
    components: {HeroBar, TitleBar, VueCal},
    data: () => ({
        titleStack: ['Admin', 'Kalender'],
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
                    matchId
                  }
                }
            `
        }
    },
    methods: {
        toTimeStr(str){
            return str.substring(10, 16);
        },
        toDateObject(str){
            return new Date(str.substring(0,10))
        },
        onEventClick(event, e) {
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

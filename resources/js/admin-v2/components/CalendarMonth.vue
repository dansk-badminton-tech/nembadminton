<template>
    <div class="calendar-month">
        <div class="calendar-month-toolbar">
            <button type="button" aria-label="Forrige måned" @click="prevMonth">‹</button>
            <span class="calendar-month-label">{{ monthLabel }}</span>
            <button type="button" aria-label="Næste måned" @click="nextMonth">›</button>
            <button type="button" class="calendar-month-today" @click="goToday">I dag</button>
        </div>
        <div class="calendar-month-grid">
            <div class="calendar-month-head">
                <div v-if="displayWeekNumbers" class="calendar-month-week-number"></div>
                <div v-for="label in weekdayLabels" :key="label" class="calendar-month-weekday">{{ label }}</div>
            </div>
            <div v-for="week in weeks" :key="week.days[0].getTime()" class="calendar-month-week" :style="{ height: week.height + 'px' }">
                <div v-if="displayWeekNumbers" class="calendar-month-week-number">{{ week.number }}</div>
                <div class="calendar-month-week-body">
                    <div class="calendar-month-day-row">
                        <div
                            v-for="day in week.days"
                            :key="day.getTime()"
                            class="calendar-month-day-cell"
                            :class="{ 'is-other-month': isOtherMonth(day), 'is-today': isToday(day) }"
                        >{{ day.getDate() }}</div>
                    </div>
                    <div class="calendar-month-events" :style="{ height: week.eventsHeight + 'px' }">
                        <div
                            v-for="ev in week.events"
                            :key="ev.item.id + '-' + ev.top"
                            class="calendar-month-event"
                            :class="[ev.item.classes || 'calendar-month-event--default']"
                            :style="{ left: ev.left + '%', width: ev.width + '%', top: ev.top + 'px' }"
                            :title="ev.item.title"
                            @click="onEventClick(ev.item, $event)"
                        >
                            <span v-if="ev.startsBefore" class="calendar-month-event-arrow">‹</span>
                            <span class="calendar-month-event-title">{{ ev.item.title }}</span>
                            <span v-if="ev.endsAfter" class="calendar-month-event-arrow">›</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
const TRACK_HEIGHT = 22
const EVENT_TOP = 26

export default {
    name: "CalendarMonth",
    props: {
        items: {
            type: Array,
            default: () => []
        },
        startingDayOfWeek: {
            type: Number,
            default: 1
        },
        displayWeekNumbers: {
            type: Boolean,
            default: false
        }
    },
    emits: ["click-item"],
    data() {
        return {
            showDate: new Date()
        }
    },
    computed: {
        normalizedItems() {
            return (this.items || []).map(item => {
                const start = item.startDate ? new Date(item.startDate) : null
                const end = item.endDate ? new Date(item.endDate) : start
                const startIdx = start ? this.dayIndexOf(start) : this.dayIndexOf(new Date())
                const endIdx = end ? this.dayIndexOf(end) : startIdx
                return {item, startIdx, endIdx}
            })
        },
        weekdayLabels() {
            const labels = ['man', 'tir', 'ons', 'tor', 'fre', 'lør', 'søn']
            const shift = this.startingDayOfWeek % 7
            return [...labels.slice(shift), ...labels.slice(0, shift)]
        },
        monthLabel() {
            return this.showDate.toLocaleDateString('da-DK', {month: 'long', year: 'numeric'})
        },
        gridStart() {
            const first = new Date(this.showDate.getFullYear(), this.showDate.getMonth(), 1)
            const offset = (first.getDay() - this.startingDayOfWeek + 7) % 7
            first.setDate(first.getDate() - offset)
            return first
        },
        weeks() {
            const start = this.gridStart
            const weeks = []
            for (let w = 0; w < 6; w++) {
                const days = []
                for (let d = 0; d < 7; d++) {
                    days.push(new Date(start.getFullYear(), start.getMonth(), start.getDate() + w * 7 + d))
                }
                const weekStartIdx = this.dayIndexOf(days[0])
                const weekEndIdx = this.dayIndexOf(days[6])
                const events = this.normalizedItems
                    .filter(n => n.endIdx >= weekStartIdx && n.startIdx <= weekEndIdx)
                    .sort((a, b) => a.startIdx - b.startIdx || b.endIdx - a.endIdx)
                    .map(n => {
                        const leftStart = Math.max(n.startIdx, weekStartIdx)
                        const rightEnd = Math.min(n.endIdx, weekEndIdx)
                        return {
                            item: n.item,
                            left: ((leftStart - weekStartIdx) / 7) * 100,
                            width: (((rightEnd - leftStart) + 1) / 7) * 100,
                            startsBefore: n.startIdx < weekStartIdx,
                            endsAfter: n.endIdx > weekEndIdx,
                            startIdx: n.startIdx,
                            endIdx: n.endIdx
                        }
                    })
                const tracks = []
                for (const ev of events) {
                    let placed = false
                    for (let t = 0; t < tracks.length; t++) {
                        const last = tracks[t][tracks[t].length - 1]
                        if (last.endIdx < ev.startIdx) {
                            tracks[t].push({...ev, top: t * TRACK_HEIGHT})
                            placed = true
                            break
                        }
                    }
                    if (!placed) {
                        tracks.push([{...ev, top: tracks.length * TRACK_HEIGHT}])
                    }
                }
                const eventsHeight = Math.max(tracks.length * TRACK_HEIGHT, 1)
                weeks.push({
                    number: this.isoWeekNumber(days[0]),
                    days,
                    events: tracks.length ? tracks.reduce((acc, t) => acc.concat(t), []) : [],
                    eventsHeight,
                    height: Math.max(EVENT_TOP + eventsHeight, 92)
                })
            }
            return weeks
        }
    },
    methods: {
        dayIndexOf(date) {
            return Math.round(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()) / 86400000)
        },
        isoWeekNumber(date) {
            const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()))
            const dayNum = d.getUTCDay() || 7
            d.setUTCDate(d.getUTCDate() + 4 - dayNum)
            const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
            return Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
        },
        isOtherMonth(date) {
            return date.getMonth() !== this.showDate.getMonth()
        },
        isToday(date) {
            return this.dayIndexOf(date) === this.dayIndexOf(new Date())
        },
        prevMonth() {
            this.showDate = new Date(this.showDate.getFullYear(), this.showDate.getMonth() - 1, 1)
        },
        nextMonth() {
            this.showDate = new Date(this.showDate.getFullYear(), this.showDate.getMonth() + 1, 1)
        },
        goToday() {
            this.showDate = new Date()
        },
        onEventClick(item, event) {
            event.stopPropagation()
            this.$emit('click-item', item)
        }
    }
}
</script>

<style scoped>
    .calendar-month {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        background: #fff;
        border: 1px solid #e6e6e6;
        border-radius: 6px;
        overflow: hidden;
    }
    .calendar-month-toolbar {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        background: #f5f5f5;
        border-bottom: 1px solid #e6e6e6;
    }
    .calendar-month-toolbar button {
        border: 1px solid #dbdbdb;
        background: #fff;
        padding: 4px 12px;
        border-radius: 4px;
        cursor: pointer;
        color: #363636;
        line-height: 1.4;
    }
    .calendar-month-toolbar button:hover {
        background: #f0f0f0;
    }
    .calendar-month-label {
        flex: 1;
        font-weight: 600;
        font-size: 1.1rem;
        color: #363636;
        text-transform: capitalize;
    }
    .calendar-month-grid {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 8px;
    }
    .calendar-month-head,
    .calendar-month-week {
        display: flex;
        min-height: 92px;
    }
    .calendar-month-week-number {
        width: 34px;
        min-width: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #7a7a7a;
    }
    .calendar-month-weekday {
        flex: 1;
        text-align: center;
        font-size: 12px;
        color: #7a7a7a;
        padding: 4px 0;
        text-transform: capitalize;
    }
    .calendar-month-week-body {
        flex: 1;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .calendar-month-day-row {
        display: flex;
        flex: 1 1 auto;
        gap: 1px;
        background: #ededed;
    }
    .calendar-month-day-cell {
        flex: 1;
        min-width: 0;
        padding: 4px 6px;
        font-size: 12px;
        color: #363636;
        background: #fff;
    }
    .calendar-month-day-cell.is-other-month {
        color: #b5b5b5;
        background: #fafafa;
    }
    .calendar-month-day-cell.is-today {
        box-shadow: inset 0 0 0 2px #3273dc;
    }
    .calendar-month-events {
        position: absolute;
        top: 26px;
        left: 0;
        right: 0;
    }
    .calendar-month-event {
        position: absolute;
        height: 18px;
        line-height: 18px;
        border-radius: 3px;
        padding: 0 4px;
        font-size: 11px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        color: #363636;
        cursor: pointer;
        box-sizing: border-box;
        transition: filter 0.1s ease, box-shadow 0.1s ease;
    }
    .calendar-month-event:hover {
        filter: brightness(0.9);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        z-index: 5;
    }
    .calendar-month-event--default {
        background: #dbedfb;
    }
    .calendar-month-event-arrow {
        padding: 0 2px;
    }
    .calendar-month-event-title {
        vertical-align: middle;
    }
</style>
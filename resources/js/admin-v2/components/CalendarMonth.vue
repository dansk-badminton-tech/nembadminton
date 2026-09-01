<template>
    <div class="calendar-month">
        <div class="calendar-month-toolbar">
            <div class="calendar-month-nav">
                <button type="button" class="button is-small calendar-month-nav-arrow" title="Forrige måned" aria-label="Forrige måned" @click="prevMonth">‹</button>
                <span class="calendar-month-label" aria-live="polite">{{ monthLabel }}</span>
                <button type="button" class="button is-small calendar-month-nav-arrow" title="Næste måned" aria-label="Næste måned" @click="nextMonth">›</button>
            </div>
            <button type="button" class="button is-small calendar-month-today-button" :disabled="isCurrentMonth" title="Gå til i dag" @click="goToday">I dag</button>
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
                            :class="{ 'is-other-month': isOtherMonth(day), 'is-today': isToday(day), 'is-weekend': isWeekend(day) }"
                        >
                            <span class="calendar-month-day-number">{{ day.getDate() }}</span>
                        </div>
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
const EVENT_TOP = 32

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
        isCurrentMonth() {
            const now = new Date()
            return now.getFullYear() === this.showDate.getFullYear() && now.getMonth() === this.showDate.getMonth()
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
        isWeekend(date) {
            return date.getDay() === 0 || date.getDay() === 6
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
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(16, 24, 40, 0.06);
    }
    .calendar-month-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 8px 12px;
        background: linear-gradient(135deg, #3273dc 0%, #2156b8 100%);
        border-bottom: 1px solid #1d4ea8;
    }
    .calendar-month-nav {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .calendar-month-nav-arrow {
        font-size: 1.2rem;
        line-height: 1;
        padding: 0 9px;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.15);
        border-color: transparent;
        color: #fff;
    }
    .calendar-month-nav-arrow:hover {
        background: rgba(255, 255, 255, 0.3);
        color: #fff;
    }
    .calendar-month-label {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 140px;
        font-weight: 700;
        font-size: 0.95rem;
        letter-spacing: 0.02em;
        color: #fff;
        text-transform: capitalize;
        white-space: nowrap;
        padding: 0 6px;
    }
    .calendar-month-today-button {
        border-radius: 6px;
        background: #fff;
        border-color: transparent;
        color: #3273dc;
        font-weight: 600;
    }
    .calendar-month-today-button:hover:not([disabled]) {
        background: #e8f0fe;
        color: #2156b8;
    }
    .calendar-month-today-button[disabled] {
        background: rgba(255, 255, 255, 0.18);
        border-color: transparent;
        color: rgba(255, 255, 255, 0.75);
        opacity: 1;
        cursor: default;
    }
    .calendar-month-grid {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 8px;
    }
    .calendar-month-grid::-webkit-scrollbar {
        width: 8px;
    }
    .calendar-month-grid::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .calendar-month-grid::-webkit-scrollbar-track {
        background: transparent;
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
        align-items: flex-start;
        justify-content: flex-end;
        padding-top: 6px;
        padding-right: 10px;
        font-size: 11px;
        font-weight: 700;
        color: #3273dc;
    }
    .calendar-month-weekday {
        flex: 1;
        text-align: center;
        padding: 4px 0 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #3273dc;
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
    }
    .calendar-month-day-cell {
        flex: 1;
        min-width: 0;
        padding: 6px;
        text-align: right;
        background: #fff;
        border-right: 1px solid #eef0f3;
        border-bottom: 1px solid #eef0f3;
    }
    .calendar-month-day-cell:last-child {
        border-right: none;
    }
    .calendar-month-day-cell.is-weekend {
        background: #f4f8ff;
    }
    .calendar-month-day-cell.is-other-month {
        background: #f7f8fa;
    }
    .calendar-month-day-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 24px;
        padding: 0 6px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
    }
    .calendar-month-day-cell.is-other-month .calendar-month-day-number {
        color: #bfc4cc;
    }
    .calendar-month-day-cell.is-today .calendar-month-day-number {
        background: #3273dc;
        color: #fff;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(50, 115, 220, 0.4);
    }
    .calendar-month-events {
        position: absolute;
        top: 32px;
        left: 0;
        right: 0;
    }
    .calendar-month-event {
        position: absolute;
        height: 20px;
        line-height: 20px;
        border-radius: 5px;
        padding: 0 6px;
        font-size: 11px;
        font-weight: 600;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        color: #363636;
        cursor: pointer;
        box-sizing: border-box;
        transition: filter 0.12s ease, box-shadow 0.12s ease, transform 0.12s ease;
    }
    .calendar-month-event:hover {
        filter: brightness(0.95);
        box-shadow: 0 3px 8px rgba(16, 24, 40, 0.18);
        transform: translateY(-1px);
        z-index: 5;
    }
    .calendar-month-event--default {
        background: #dbeafe;
        color: #1e40af;
    }
    .calendar-month-event-arrow {
        padding: 0 2px;
        opacity: 0.7;
    }
    .calendar-month-event-title {
        vertical-align: middle;
    }
</style>
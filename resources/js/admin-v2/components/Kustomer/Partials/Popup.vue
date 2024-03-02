<template>
    <div class="kustomer-popup">
        <kustomer-header>
            <img slot="logo" :src="params.logo" />
            <h1 slot="title" v-text="labels.title" :style="{'color': params.colors.primary}"></h1>
        </kustomer-header>
        <div class="kustomer-container">
            <kustomer-feedbacks-list
                :feedbacks="params.feedbacks"
                :labels="labels"
                @selected="setSelectedFeedback"
            ></kustomer-feedbacks-list>
            <kustomer-feedback-form
                :feedback="selectedFeedback"
                :params="params"
                :labels="labels"
                @unselected="selectedFeedback = undefined"
            ></kustomer-feedback-form>
        </div>
    </div>
</template>

<script>
import Header from './Header.vue'
import FeedbacksList from './FeedbacksList.vue'
import Form from './Form.vue'

export default {
    props: ['params', 'labels'],

    data() {
        return {
            selectedFeedback: undefined
        }
    },

    methods: {
        setSelectedFeedback(feedback) {
            this.selectedFeedback = feedback
        }
    },

    components: {
        'kustomer-header': Header,
        'kustomer-feedbacks-list': FeedbacksList,
        'kustomer-feedback-form': Form
    }
}
</script>

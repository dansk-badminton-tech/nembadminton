<template>
    <section class="kustomer-feedbacks">
        <div
            class="kustomer-feedback"
            v-for="(feedback, type) in feedbacks"
            :key="type"
            @click="setFeedbackType(feedback, type)"
        >
            <img :src="feedback.icon" :alt="feedback.title" />
            <p v-text="label(type)"></p>
        </div>
    </section>
</template>

<script>
export default {
    props: ['feedbacks', 'labels'],

    methods: {
        setFeedbackType(feedback, type) {
            feedback.type = type
            this.$emit('selected', feedback)
        },
        label(type) {
            if(this.labels?.feedbacks?.hasOwnProperty(type)){
                return this.labels.feedbacks[type].title
            }
            return ''
        }
    }
}
</script>

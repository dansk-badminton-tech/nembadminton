<template>
    <help-collection title="Nyheder" eyebrow="Det seneste" introduction="Nye funktioner og ændringer i Nembadminton." empty-text="Der er endnu ikke udgivet nogen nyheder." base-path="/help/news" :items="news" />
</template>

<script>
import HelpCollection from './HelpCollection.vue'
import {news} from '@/help/documents'

export default {name: 'HelpNews', components: {HelpCollection}, data: () => ({news})}
</script>

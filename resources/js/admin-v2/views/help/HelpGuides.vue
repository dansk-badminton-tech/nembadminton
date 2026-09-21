<template>
    <help-collection title="Vejledninger" eyebrow="Sådan gør du" introduction="Trin for trin gennem funktionerne i NemBadminton." empty-text="Der er endnu ikke udgivet nogen vejledninger." base-path="/help/guides" :items="guides" />
</template>

<script>
import HelpCollection from './HelpCollection.vue'
import {guides} from '@/help/documents'

export default {name: 'HelpGuides', components: {HelpCollection}, data: () => ({guides})}
</script>

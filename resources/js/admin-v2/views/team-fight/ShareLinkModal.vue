<template>
    <div class="card">
        <div class="card-content">
            <div class="content">
                <p>Alle med linket kan udelukkende se holdet - ikke redigere. Du behøver ikke at være logget ind for
                    at se holdet. Hvis holdet opdateres efterfølgende bliver det afspejlet med det samme.</p>
                <pre>{{ shareUrl }}</pre>
            </div>
        </div>
        <footer class="card-footer">
            <a :href="shareUrl" class="card-footer-item" target="_blank">Vis (nyt vindue)</a>
            <a class="card-footer-item" @click.prevent="copyToClipboard">Kopier</a>
            <a class="card-footer-item" @click.prevent="$emit('close')">Luk</a>
        </footer>
    </div>
</template>

<script>

export default {
    name: "ShareLinkModal",
    props: ['teamId'],
    data(){
        return {
            showShareLink: false
        }
    },
    computed: {
        shareUrl(){
            let getUrl = window.location;
            return getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1] + "/team-fight/" + this.teamId + '/public-view';
        }
    },
    methods: {
        copyToClipboard() {
            this.$copyText(this.shareUrl).then((e) => {
                this.$buefy.snackbar.open(`Kopiret til udklipsholder`)
            }, (e) => {
                this.$buefy.snackbar.open(`Kunne ikke kopir til udklipsholder. :(`)
            })
        },
    }
}
</script>

<style scoped>

</style>

<template>
        <span>Link</span>
</template>

<script>
const ModalForm = {
    props: ['shareUrl', 'copyToClipboard'],
    template: `
        <div class="card">
        <div class="card-content">
            <div class="content">
                <p>Alle som har linket kan kun se holdet, ikke rediger. Man behøver ikke at være logget ind for
                    at se holdet. Hvis holdet opdateres efterfølgende bliver det afspejlet med det samme.</p>
                <pre>{{ shareUrl }}</pre>
            </div>
        </div>
        <footer class="card-footer">
            <a :href="shareUrl" class="card-footer-item" target="_blank">Vis (Nyt vindue)</a>
            <a class="card-footer-item" @click.prevent="copyToClipboard">Kopier</a>
            <a class="card-footer-item" @click.prevent="$emit('close')">Luk</a>
        </footer>
        </div>
        `
}

export default {
    name: "ShareLinkModal",
    props: ['value','teamId'],
    data(){
        return {
            showShareLink: false,
            shareUrl: ''
        }
    },
    watch: {
        value(val){
            if(val === true){
                this.publish()
            }
        }
    },
    methods: {
        openModal(){
            this.$buefy.modal.open({
                parent: this,
                component: ModalForm,
                props: {
                    shareUrl: this.shareUrl,
                    copyToClipboard: this.copyToClipboard
                },
                scroll: "keep",
                width: 640,
                events: {
                        close: () => {
                            this.$emit('input', false)
                        }
                }
            })
        },
        publish() {
            let getUrl = window.location;
            this.shareUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1] + "/team-fight/" + this.teamId + '/public-view';
            this.openModal()
        },
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

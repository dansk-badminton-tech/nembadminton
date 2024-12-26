<template>
    <div>
        <b-field grouped>
            <b-field expanded class="" label="Indsamling link" message="Dette link kan du give til alle i din klub">
                <b-input expanded type="text" readonly :value="shareUrl" @click="$event.target.select()"></b-input>
                <p class="control">
                    <b-button @click="copyToClipboard(shareUrl)">Kopier Link</b-button>
                    <b-button @click="openLink(shareUrl)">Åben Link</b-button>
                </p>
            </b-field>
        </b-field>
        <b-field expanded label="Email" message="Email til notifikationer når et afbud modtages">
            <p>{{cancellationCollector.email}}</p>
        </b-field>
        <b-field expanded label="Klubber" message="Bestemmer hvilke spiller som der kan søges i på afbuds siden">
            <p>{{resolvedClubs}}</p>
        </b-field>
    </div>
</template>
<script>
export default {
    name: 'CancellationCollector',
    props: {
        cancellationCollector: {
            type: Object,
            default: () => ({}) // Default should be a factory function
        }
    },
    computed: {
        shareUrl() {
            let getUrl = window.location;
            return getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1] + "/cancellation/" + this.cancellationCollector?.sharingId + '/public-cancellation';
        },
        resolvedClubs(){
            return this.cancellationCollector?.clubs?.map(club => club.name1).join(',') || []
        }
    },
    methods: {
        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Clipboard copy successful
                console.log('Text copied to clipboard');
            }).catch(err => {
                console.error('Failed to copy text: ', err);
            });
        },
        openLink(url) {
            window.open(url, '_blank');
        }
    }
}
</script>

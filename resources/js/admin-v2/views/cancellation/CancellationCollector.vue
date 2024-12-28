<template>
    <div>
        <b-field grouped>
            <b-field expanded class="" label="Afbudslink" message="Dette link kan du dele med alle spillere i din klub.">
                <b-input expanded type="text" readonly :value="shareUrl" @click="$event.target.select()"></b-input>
                <p class="control">
                    <b-button @click="copyToClipboard(shareUrl)">Kopier link</b-button>
                    <b-button @click="openLink(shareUrl)">Åbn link</b-button>
                </p>
            </b-field>
        </b-field>
        <b-field expanded label="Email" message="Email som notifikationer sendes til, når et afbud modtages.">
            <p>{{resolveEmail}}</p>
        </b-field>
        <b-field expanded label="Klubber" message="Afgrænser hvilke spillere, der kan søges frem via afbudslinket.">
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
            return this.cancellationCollector?.clubs?.map(club => club.name1).join(', ') || []
        },
        resolveEmail(){
            if(this.cancellationCollector.email === null){
                return 'Ingen email angivet.'
            }
            return this.cancellationCollector.email
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

<script>

import {defineComponent} from "vue";
import HeroBar from "@/components/HeroBar.vue";
import TitleBar from "@/components/TitleBar.vue";
import TeamQuery from "../../../queries/team.graphql";
import gql from "graphql-tag";
import {extractErrorMessages} from "@/helpers.js";

export default {
    components: {TitleBar, HeroBar},
    inject: ['clubhouseId', 'user'],
    props: {
        teamFightId: String
    },
    computed: {
        cannotPublish() {
            if (this.loading) return true;
            if (!this.notificationType) return true;

            if (this.recipientType === 'manual_emails') {
                return !this.manualEmails.trim();
            } else if (this.recipientType === 'test_self') {
                return false;
            }
            return true;
        },
        hasValidRecipients() {
            if (!this.notificationType) return false;
            if (this.recipientType === 'manual_emails') {
                return this.manualEmails.trim().length > 0;
            } else if (this.recipientType === 'test_self') {
                return true;
            }
            return false;
        },
        manualEmailsSanitized(){
            return this.manualEmails
                .split(/[,\n]/)
                .map(email => email.trim())
                .filter(email => email.length > 0);
        },
    },
    data() {
        return {
            titleStack: ['Admin', 'Notifikation'],
            loading: false,
            message: '',
            team: {
                squads: []
            },
            activityLogs: [],
            expandedLogs: [],
            expandedEmailLogs: [],
            recipientType: null, // null, 'platform' or 'manual'
            notificationType: 'team_publish',
            manualEmails: '',
            saveManualEmails: true,
            predefinedTexts: [
                {
                    label: 'Holdrunden er klar',
                    text: 'Holdopstillingen er nu klar. Gå ind og se om du er sat på holdet.'
                },
                {
                    label: 'Ændringer foretaget',
                    text: 'Der er foretaget ændringer i holdopstillingen. Tjek venligst det opdaterede hold.'
                },
                {
                    label: 'Vigtig info',
                    text: 'Husk at give besked hvis du er forhindret i at spille. Se holdet her.'
                }
            ]
        }
    },
    created() {
        const savedMessage = localStorage.getItem('team_notification_message');
        if (savedMessage) {
            this.message = savedMessage;
        }
    },
    watch: {
        message(newVal) {
            localStorage.setItem('team_notification_message', newVal);
        }
    },
    apollo: {
        receiver: {
            query: gql`
                query receiver($team_id: ID!){
                    receiver : teamReceiver(team_id: $team_id){
                        id
                        emails
                    }
                }
            `,
            variables(){
                return {
                    team_id: this.teamFightId
                }
            },
            result({data}){
                this.manualEmails = data.receiver.emails.join(', ')
            }
        },
        team: {
            query: TeamQuery,
            variables: function () {
                return {
                    id: this.teamFightId
                }
            }
        },
        activityLogs: {
            query: gql`
                query teamNotificationActivity($id: ID!) {
                    teamNotificationActivity(id: $id) {
                        id
                        action
                        recipientType
                        recipientCount
                        recipientsSummary
                        message
                        metadata
                        createdAt
                        user {
                            name
                        }
                    }
                }
            `,
            variables: function () {
                return {
                    id: this.teamFightId
                }
            },
            update: data => {
                return data.teamNotificationActivity.map(log => {
                    let parsedMetadata = null;
                    if (log.metadata) {
                        try {
                            parsedMetadata = typeof log.metadata === 'string' ? JSON.parse(log.metadata) : log.metadata;
                        } catch (e) {
                            console.error("Failed to parse metadata", e);
                        }
                    }
                    return {
                        ...log,
                        parsedMetadata
                    };
                });
            },
        }
    },
    methods: {
        publish(){
            // Added: loading and guard
            if (this.cannotPublish) return;
            this.loading = true;

            if (this.recipientType === 'test_self') {
                this.$buefy.snackbar.open({
                    duration: 3000,
                    type: 'is-info',
                    message: 'Sender test email til dig selv...'
                });
            }

            this.$apollo.mutate({
                mutation: gql`
                    mutation sendTeamNotification($input: SendTeamNotificationInput!){
                        sendTeamNotification(input: $input){
                            id
                        }
                    }
                `,
                variables: {
                    input: {
                        id: this.teamFightId,
                        type: this.notificationType.toUpperCase(),
                        message: this.message,
                        receivers: {
                            method: this.recipientType.toUpperCase(),
                            saveEmails: this.saveManualEmails,
                            emails: this.recipientType === 'manual_emails' ? this.manualEmailsSanitized : []
                        }
                    }
                }
            }).then(({data}) => {
                localStorage.removeItem('team_notification_message');

                if (this.recipientType === 'test_self') {
                    this.$buefy.snackbar.open({
                        duration: 4000,
                        type: 'is-success',
                        message: `Test email sendt til ${this.user.email}`
                    });
                } else {
                    this.$buefy.snackbar.open({
                        duration: 4000,
                        type: 'is-success',
                        message: 'Holdet er nu publiceret'
                    })
                }
                this.$apollo.queries.activityLogs.refetch();
            }).catch(({graphQLErrors}) => {
                const errorMessages = extractErrorMessages(graphQLErrors);
                const prefix = this.recipientType === 'test_self' ? 'Kunne ikke sende test email: ' : 'Kunne ikke publicere holdet: ';
                this.$buefy.snackbar.open({
                    duration: 5000,
                    type: 'is-danger',
                    message: prefix + errorMessages.join(', ')
                })
            }).finally(() => {
                this.loading = false;
            })
        },
        getMarkerClass(action) {
            const classes = {
                'team_publish': 'is-info',
                'test_email_sent': 'is-warning',
                'team_updated': 'is-primary'
            };
            return classes[action.toLowerCase()] || 'is-grey';
        },
        getIcon(action) {
            const icons = {
                'team_publish': 'email-check',
                'test_email_sent': 'flask-outline',
                'team_updated': 'pencil-circle',
            };
            return icons[action.toLowerCase()] || 'information';
        },
        getLogTitle(log) {
            const action = log.action.toLowerCase();
            if (action === 'team_publish') return 'Holdrunden er klar';
            if (action === 'test_email_sent') return 'Test email afsendt';
            if (action === 'team_updated') return 'Ændringer til holdrunden';
            return log.action;
        },
        formatDateTime(dateString) {
            return new Date(dateString).toLocaleString('da-DK', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        toggleLogDetails(logId) {
            const index = this.expandedLogs.indexOf(logId);
            if (index > -1) {
                this.expandedLogs.splice(index, 1);
            } else {
                this.expandedLogs.push(logId);
            }
        },
        isLogExpanded(logId) {
            return this.expandedLogs.includes(logId);
        },
        toggleEmails(logId) {
            const index = this.expandedEmailLogs.indexOf(logId);
            if (index > -1) {
                this.expandedEmailLogs.splice(index, 1);
            } else {
                this.expandedEmailLogs.push(logId);
            }
        },
        isEmailExpanded(logId) {
            return this.expandedEmailLogs.includes(logId);
        },
        getVisibleEmails(log, limit = 10) {
            if (!log.parsedMetadata?.emails) return [];
            if (this.isEmailExpanded(log.id)) return log.parsedMetadata.emails;
            return log.parsedMetadata.emails.slice(0, limit);
        },
        copyEmailsToClipboard(emails) {
            if (!emails || emails.length === 0) return;
            const emailList = emails.join(', ');
            navigator.clipboard.writeText(emailList).then(() => {
                this.$buefy.snackbar.open({
                    duration: 3000,
                    type: 'is-success',
                    message: 'Email adresser kopieret til udklipsholder'
                });
            }).catch(err => {
                console.error('Failed to copy emails', err);
            });
        },
        usePredefinedText(predefined) {
            const confirmMessage = `
                <div class="content">
                    <p>Er du sikker på, at du vil bruge denne skabelon?</p>
                    <div class="notification is-light italic">
                        "${predefined.text}"
                    </div>
                    ${this.message && this.message.trim() !== '' ? '<p class="has-text-danger"><strong>Bemærk:</strong> Dette vil overskrive din nuværende besked.</p>' : ''}
                </div>
            `;

            this.$buefy.dialog.confirm({
                title: `Brug skabelon: ${predefined.label}`,
                message: confirmMessage,
                confirmText: 'Brug skabelon',
                cancelText: 'Annuller',
                type: 'is-info',
                onConfirm: () => {
                    this.message = predefined.text;
                }
            });
        }
    }
}
</script>

<template>
    <div>
        <title-bar :title-stack="titleStack"/>
        <hero-bar :has-right-visible="false">
            Dashboard
        </hero-bar>
        <div class="section">
            <div class="content">
            <b-button icon-left="arrow-left-circle" tag="router-link" :to="'/c-'+clubhouseId+'/team-fight/'+teamFightId+'/edit'" @click="publish">Tilbage</b-button>
            </div>
            <div class="columns">
                <div class="column is-half">
                    <form @submit.prevent="publish">
                        <!-- Section 1: Message -->
                        <div class="box">
                            <h4 class="title is-5 mb-4">
                                <b-icon icon="mailbox-open" size="is-small"></b-icon>
                                1. Besked
                            </h4>
                            <b-field label="Besked til modtagerne (valgfrit)">
                                <b-input
                                    type="textarea"
                                    v-model="message"
                                    placeholder="Skriv en besked..."
                                    rows="4"
                                    expanded
                                    maxlength="500"
                                    has-counter />
                            </b-field>
                            <div class="mt-4">
                                <p class="is-size-7 has-text-grey mb-2">
                                    <b-icon icon="text-box-multiple-outline" size="is-small" class="mr-1"></b-icon>
                                    Brug en af de foruddefinerede skabeloner:
                                </p>
                                <div class="buttons">
                                    <b-button
                                        v-for="(predefined, index) in predefinedTexts"
                                        :key="index"
                                        size="is-small"
                                        type="is-light"
                                        icon-left="text-box-plus"
                                        @click="usePredefinedText(predefined)">
                                        <b-tooltip :label="predefined.text" multilined position="is-top" size="is-large" type="is-dark">
                                            {{ predefined.label }}
                                        </b-tooltip>
                                    </b-button>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Action Type -->
                        <div class="box">
                            <h4 class="title is-5 mb-4">
                                <b-icon icon="lightning-bolt" size="is-small"></b-icon>
                                2. Type af handling
                            </h4>
                            <p class="mb-4 has-text-grey">Vælg hvad denne besked drejer sig om</p>

                            <div class="recipient-options">
                                <div
                                    class="recipient-option"
                                    :class="{'is-selected': notificationType === 'team_publish'}"
                                    @click="notificationType = 'team_publish'">
                                    <div class="recipient-option-icon">
                                        <b-icon icon="bullhorn" size="is-large"></b-icon>
                                    </div>
                                    <div class="recipient-option-content">
                                        <h5 class="title is-6 mb-2">Holdrunden er klar</h5>
                                        <p class="is-size-7 has-text-grey">
                                            Send besked om at holdrunden er klar og kan sendes til spillerne
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="recipient-option"
                                    :class="{'is-selected': notificationType === 'team_updated'}"
                                    @click="notificationType = 'team_updated'">
                                    <div class="recipient-option-icon">
                                        <b-icon icon="update" size="is-large"></b-icon>
                                    </div>
                                    <div class="recipient-option-content">
                                        <h5 class="title is-6 mb-2">Holdrunden er opdateret</h5>
                                        <p class="is-size-7 has-text-grey">
                                            Send besked om ændringer i en allerede offentliggjort holdrunde
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Recipients -->
                        <div class="box">
                            <h4 class="title is-5 mb-4">
                                <b-icon icon="account-group" size="is-small"></b-icon>
                                3. Modtagere
                            </h4>

                            <p class="mb-4 has-text-grey">Vælg hvordan du vil sende beskeden</p>

                            <div class="recipient-options">
                                <div
                                    class="recipient-option"
                                    :class="{'is-selected': recipientType === 'manual_emails'}"
                                    @click="recipientType = 'manual_emails'">
                                    <div class="recipient-option-icon">
                                        <b-icon icon="mail" size="is-large"></b-icon>
                                    </div>
                                    <div class="recipient-option-content">
                                        <h5 class="title is-6 mb-2">Manuel indtastning</h5>
                                        <p class="is-size-7 has-text-grey">
                                            Indtast email adresser manuelt til modtagere uden for platformen
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="recipient-option"
                                    :class="{'is-selected': recipientType === 'test_self'}"
                                    @click="recipientType = 'test_self'">
                                    <div class="recipient-option-icon">
                                        <b-icon icon="flask" size="is-large"></b-icon>
                                    </div>
                                    <div class="recipient-option-content">
                                        <h5 class="title is-6 mb-2">Test til mig selv</h5>
                                        <p class="is-size-7 has-text-grey">
                                            Send en test email til din egen adresse <strong v-if="user?.email">({{ user?.email }})</strong> for at se hvordan den ser ud
                                        </p>
                                    </div>
                                </div>
                            </div>

                        <!-- Manual email input option -->
                        <div v-if="recipientType === 'manual_emails'" class="mt-4">
                            <b-field label="Email adresser" message="Adskil emails med komma eller linjeskift">
                                <b-input
                                    type="textarea"
                                    v-model="manualEmails"
                                    placeholder="email1@example.com, email2@example.com"
                                    rows="3"
                                    expanded />
                            </b-field>
                            <b-field>
                                <b-checkbox v-model="saveManualEmails">
                                    Gem email adresser til næste gang (denne holdrunde)
                                </b-checkbox>
                            </b-field>
                        </div>
                        </div>

                        <!-- Section 4: Send Options -->
                        <div class="box">
                            <h4 class="title is-5 mb-4">
                                <b-icon icon="send" size="is-small"></b-icon>
                                4. Udsend
                            </h4>

                            <div class="buttons">
                                <b-tooltip
                                    :label="!hasValidRecipients ? 'Ingen modtagere valgt' : null"
                                    :active="!hasValidRecipients">
                                    <b-button
                                        :loading="loading"
                                        :disabled="cannotPublish"
                                        native-type="submit"
                                        type="is-info"
                                        :icon-left="recipientType === 'test_self' ? 'flask' : 'send'">
                                        {{ recipientType === 'test_self' ? 'Send test email' : 'Send til alle modtagere' }}
                                    </b-button>
                                </b-tooltip>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="column is-half">
                    <div class="is-flex is-justify-content-space-between is-align-items-center mb-4">
                        <div>
                            <h1 class="title is-4 mb-1">Aktivitetslog</h1>
                            <p class="subtitle mt-2 is-6 has-text-grey">Historik over handlinger på denne holdkamp</p>
                        </div>
                        <div class="buttons">
                            <b-button
                                size="is-small"
                                icon-left="refresh"
                                :loading="$apollo.queries.activityLogs.loading"
                                @click="$apollo.queries.activityLogs.refetch()">
                                Opdater
                            </b-button>
                        </div>
                    </div>

                    <div v-if="activityLogs.length === 0" class="box has-text-centered has-text-grey py-6">
                        <b-icon icon="history" size="is-large" class="mb-3"></b-icon>
                        <p class="is-size-5">Ingen aktivitet endnu</p>
                        <p class="is-size-7">Her vil du kunne se en historik over sendte notifikationer og ændringer.</p>
                    </div>

                    <div v-else class="activity-feed">
                        <div v-for="(log, index) in activityLogs" :key="log.id" class="activity-item" :class="{'is-latest': index === 0}">
                            <div class="activity-marker" :class="getMarkerClass(log.action)">
                                <b-icon :icon="getIcon(log.action)" size="is-small"></b-icon>
                            </div>
                            <div class="activity-content card">
                                <div class="card-content p-4">
                                    <div class="is-flex is-justify-content-space-between is-align-items-start mb-2">
                                        <div>
                                            <h5 class="title is-6 mb-1">
                                                {{ getLogTitle(log) }}
                                            </h5>
                                            <p v-if="log.parsedMetadata && log.parsedMetadata.emails" class="is-size-7 font-weight-bold">Antal modtagere: {{ log.parsedMetadata.emails.length }}</p>
                                        </div>
                                        <div class="is-flex is-flex-direction-column is-align-items-end">
                                            <span class="is-size-7 has-text-grey whitespace-nowrap mb-1">
                                                {{ formatDateTime(log.createdAt) }}
                                            </span>
                                            <b-button
                                                v-if="log.action === 'team_publish' || log.action === 'test_email_sent' || log.message || log.recipientsSummary"
                                                size="is-small"
                                                type="is-ghost"
                                                class="p-0 height-auto"
                                                @click="toggleLogDetails(log.id)"
                                            >
                                                {{ isLogExpanded(log.id) ? 'Skjul detaljer' : 'Vis detaljer' }}
                                            </b-button>
                                        </div>
                                    </div>

                                    <div v-if="isLogExpanded(log.id)" class="activity-details is-size-7 mt-3 pt-3 border-top-dashed">
                                        <div v-if="log.parsedMetadata" class="mt-2">
                                            <div v-if="log.parsedMetadata.emails && log.parsedMetadata.emails.length > 0">
                                                <div class="is-flex is-justify-content-space-between is-align-items-center mb-1">
                                                    <p class="is-size-7 font-weight-bold">Email modtagere ({{ log.parsedMetadata.emails.length }}):</p>
                                                    <b-button
                                                        size="is-small"
                                                        type="is-text"
                                                        icon-left="content-copy"
                                                        class="p-0 height-auto is-size-7"
                                                        @click="copyEmailsToClipboard(log.parsedMetadata.emails)"
                                                    >
                                                        Kopier alle emails
                                                    </b-button>
                                                </div>
                                                <div class="tags">
                                                    <span v-for="email in getVisibleEmails(log)" :key="email" class="tag is-small is-white border">
                                                        {{ email }}
                                                    </span>
                                                    <b-button
                                                        v-if="log.parsedMetadata.emails.length > 10"
                                                        size="is-small"
                                                        type="is-ghost"
                                                        class="p-0 height-auto ml-2"
                                                        @click="toggleEmails(log.id)"
                                                    >
                                                        {{ isEmailExpanded(log.id) ? 'Vis færre' : 'Vis alle (' + (log.parsedMetadata.emails.length - 10) + ' mere)' }}
                                                    </b-button>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="log.message" class="message-bubble mt-2 p-2 has-background-light is-italic">
                                            "{{ log.message }}"
                                        </div>
                                    </div>

                                    <div class="mt-2 pt-2 border-top is-flex is-align-items-center has-text-grey">
                                        <b-icon icon="account" size="is-small" class="mr-1"></b-icon>
                                        <span class="is-size-7">
                                            Udført af:
                                            <span :class="{'has-text-info font-weight-bold': log.user, 'has-text-grey-light italic': !log.user}">
                                                {{ log.user ? log.user.name : 'System' }}
                                            </span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.recipient-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.recipient-option {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    border: 2px solid #dbdbdb;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: white;
}

.recipient-option:hover {
    border-color: #3e8ed0;
    background-color: #f5f5f5;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.recipient-option.is-selected {
    border-color: #3e8ed0;
    background-color: #eef6fc;
    box-shadow: 0 2px 4px rgba(62, 142, 208, 0.2);
}

.recipient-option-icon {
    color: #7a7a7a;
    flex-shrink: 0;
}

.recipient-option.is-selected .recipient-option-icon {
    color: #3e8ed0;
}

.recipient-option-content {
    flex: 1;
}

.recipient-option-content .title {
    margin-bottom: 0.25rem;
}

.activity-feed {
    position: relative;
    padding-left: 1.5rem;
}

.activity-feed::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #f5f5f5;
}

.activity-item {
    position: relative;
    margin-bottom: 1.5rem;
}

.activity-marker {
    position: absolute;
    left: -1.5rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
    color: white;
    box-shadow: 0 0 0 4px white;
}

.activity-content {
    margin-left: 0.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease;
}

.activity-content:hover {
    transform: translateX(5px);
}

.activity-item.is-latest .activity-content {
    border: 1px solid #3e8ed0;
    box-shadow: 0 4px 12px rgba(62, 142, 208, 0.15);
}

.message-bubble {
    border-radius: 4px;
    border-left: 3px solid #dbdbdb;
}

.border-top {
    border-top: 1px solid #f5f5f5;
}

.border-top-dashed {
    border-top: 1px dashed #dbdbdb;
}

.height-auto {
    height: auto !important;
}

.whitespace-nowrap {
    white-space: nowrap;
}

.activity-marker.is-info { background-color: #3e8ed0; }
.activity-marker.is-warning { background-color: #ffe08a; color: rgba(0, 0, 0, 0.7); }
.activity-marker.is-success { background-color: #48c78e; }
.activity-marker.is-primary { background-color: #00d1b2; }
.activity-marker.is-danger { background-color: #f14668; }
.activity-marker.is-link { background-color: #485fc7; }
.activity-marker.is-grey { background-color: #dbdbdb; }
.border {
    border: 1px solid #dbdbdb;
}

.font-weight-bold {
    font-weight: bold;
}

.italic {
    font-style: italic;
}
</style>

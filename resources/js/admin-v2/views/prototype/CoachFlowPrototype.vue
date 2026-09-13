<template>
    <div class="prototype-container" dusk="coach-flow-prototype">
        <title-bar :title-stack="['Prototype', 'Træner Flow', activeViewTitle]"/>
        <hero-bar :has-right-visible="true">
            <span>PROTOTYPE: Træner & Holdleder Flow</span>
            <template #right>
                <div class="tags has-addons">
                    <span class="tag is-dark">Koncept</span>
                    <span class="tag is-warning has-text-weight-bold">UI Sandbox (In-Memory)</span>
                </div>
            </template>
        </hero-bar>

        <section class="section is-main-section">
            <!-- Top View Tabs -->
            <div class="tabs is-boxed mb-4">
                <ul>
                    <li :class="{'is-active': currentView === 'roster'}" @click="setView('roster')">
                        <a>
                            <b-icon icon="account-group" size="is-small" class="mr-2"></b-icon>
                            <span>1. Holdtrup & Onboarding</span>
                        </a>
                    </li>
                    <li :class="{'is-active': currentView === 'events'}" @click="setView('events')">
                        <a>
                            <b-icon icon="calendar-sync" size="is-small" class="mr-2"></b-icon>
                            <span>2. Kampprogram & Import</span>
                        </a>
                    </li>
                    <li :class="{'is-active': currentView === 'availability'}" @click="setView('availability')">
                        <a>
                            <b-icon icon="checkbox-marked-circle-outline" size="is-small" class="mr-2"></b-icon>
                            <span>3. Tilkendegivelser & Rykker-Hub</span>
                            <span class="tag is-rounded is-danger is-small ml-2">{{ totalPendingCount }} mangler</span>
                        </a>
                    </li>
                    <li :class="{'is-active': currentView === 'lineup'}" @click="setView('lineup')">
                        <a>
                            <b-icon icon="badminton" size="is-small" class="mr-2"></b-icon>
                            <span>4. Holdopstilling med Tilgængelighed</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- ========================================== -->
            <!-- VIEW 1: TRUP & ONBOARDING                  -->
            <!-- ========================================== -->
            <div v-if="currentView === 'roster'" class="view-panel">
                <div class="level mb-4">
                    <div class="level-left">
                        <div class="level-item">
                            <b-field label="Vælg Hold">
                                <b-select v-model="selectedTeamId">
                                    <option v-for="team in teams" :key="team.id" :value="team.id">
                                        {{ team.name }} ({{ team.tier }})
                                    </option>
                                </b-select>
                            </b-field>
                        </div>
                        <div class="level-item">
                            <b-field label="Sæson">
                                <b-select v-model="selectedSeason">
                                    <option value="2025/2026">2025/2026</option>
                                    <option value="2024/2025">2024/2025</option>
                                </b-select>
                            </b-field>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="level-item buttons">
                            <b-button type="is-light" icon-left="content-copy" @click="cloneRosterFromPreviousSeason">
                                Klon fra 2024/2025
                            </b-button>
                            <b-button type="is-primary" icon-left="account-plus" @click="isAddPlayerModalActive = true">
                                Tilføj spiller fra klub
                            </b-button>
                            <b-button type="is-link" icon-left="email-fast" @click="inviteAllPending">
                                Inviter alle uregistrerede ({{ uninvitedCount }})
                            </b-button>
                        </div>
                    </div>
                </div>

                <!-- Group Messenger Outreach Box -->
                <div class="notification is-info is-light mb-4">
                    <div class="level">
                        <div class="level-left">
                            <div>
                                <h4 class="title is-6 mb-1">📢 Del invitation i holdets Facebook Messenger gruppe</h4>
                                <p class="is-size-7">Send ét samlet link til truppen, så spillere kan oprette deres adgang og bekræfte deres spillerprofil.</p>
                            </div>
                        </div>
                        <div class="level-right">
                            <b-button type="is-info" icon-left="facebook-messenger" @click="copyGroupInviteToClipboard">
                                Kopier hold-invitation til Messenger
                            </b-button>
                        </div>
                    </div>
                </div>

                <!-- Roster Table -->
                <card-component :title="'Spillertrup: ' + activeTeam.name + ' (' + activeTeamRoster.length + ' spillere)'" class="has-table">
                    <b-table :data="activeTeamRoster" striped hoverable default-sort="ranking" default-sort-direction="desc">
                        <b-table-column field="name" label="Spiller" v-slot="props">
                            <strong>{{ props.row.name }}</strong>
                            <span v-if="props.row.gender === 'K'" class="tag is-small is-light is-danger ml-2">Dame</span>
                            <span v-else class="tag is-small is-light is-info ml-2">Herre</span>
                        </b-table-column>

                        <b-table-column field="ranking" label="Point (Single / Double)" v-slot="props" numeric sortable>
                            {{ props.row.singlePoints }} / {{ props.row.doublePoints }} p
                        </b-table-column>

                        <b-table-column field="userStatus" label="Brugerstatus" v-slot="props">
                            <b-tag v-if="props.row.isRegistered" type="is-success" rounded>
                                <b-icon icon="check-circle" size="is-small"></b-icon>
                                <span>Bruger aktiv</span>
                            </b-tag>
                            <b-tag v-else type="is-warning" rounded>
                                <b-icon icon="clock-outline" size="is-small"></b-icon>
                                <span>Afventer oprettelse</span>
                            </b-tag>
                        </b-table-column>

                        <b-table-column field="email" label="E-mail" v-slot="props">
                            <span v-if="props.row.email">{{ props.row.email }}</span>
                            <span v-else class="has-text-grey-light">Ingen e-mail fundet</span>
                        </b-table-column>

                        <b-table-column label="1-on-1 Messenger Nudge" v-slot="props">
                            <b-button size="is-small" type="is-light" icon-left="link-variant" @click="copyPersonalInvite(props.row)">
                                Kopier invitationslink
                            </b-button>
                        </b-table-column>

                        <b-table-column label="Handling" v-slot="props" width="80">
                            <b-button size="is-small" type="is-danger" outlined icon-right="delete" @click="removePlayerFromTeam(props.row.id)"></b-button>
                        </b-table-column>
                    </b-table>
                </card-component>
            </div>

            <!-- ========================================== -->
            <!-- VIEW 2: KAMPPROGRAM & IMPORT               -->
            <!-- ========================================== -->
            <div v-if="currentView === 'events'" class="view-panel">
                <div class="level mb-4">
                    <div class="level-left">
                        <div class="level-item">
                            <b-field label="Vælg Hold">
                                <b-select v-model="selectedTeamId">
                                    <option v-for="team in teams" :key="team.id" :value="team.id">
                                        {{ team.name }} ({{ team.tier }})
                                    </option>
                                </b-select>
                            </b-field>
                        </div>
                    </div>
                    <div class="level-right buttons">
                        <b-button type="is-success" icon-left="download" @click="openImportModal">
                            Importer kampprogram fra Badmintonplayer.dk
                        </b-button>
                        <b-button type="is-primary" icon-left="plus" @click="addNewMatchRow">
                            Tilføj kamp manuelt
                        </b-button>
                    </div>
                </div>

                <b-notification type="is-info" :closable="false" class="mb-4">
                    <div class="is-flex is-align-items-center">
                        <b-icon icon="information" class="mr-3"></b-icon>
                        <div>
                            <strong>Automatisk synkronisering:</strong> Kampdatoer og modstandere matcher de officielle spillerunder fra Badminton Danmark. Holdrunder oprettet i NemBadminton trækker automatisk svar fra disse kampe via rundenummeret.
                        </div>
                    </div>
                </b-notification>

                <card-component :title="'Kampprogram for ' + activeTeam.name + ' - ' + activeTeam.tier" class="has-table">
                    <b-table :data="activeTeamEvents" striped>
                        <b-table-column field="roundNumber" label="Runde #" v-slot="props" width="90" numeric>
                            <span class="tag is-primary has-text-weight-bold">Runde {{ props.row.roundNumber }}</span>
                        </b-table-column>

                        <b-table-column field="date" label="Spilledato & Tid" v-slot="props">
                            <strong>{{ formatDate(props.row.date) }}</strong>
                            <div class="is-size-7 has-text-grey">kl. {{ props.row.time }}</div>
                        </b-table-column>

                        <b-table-column field="opponent" label="Modstander" v-slot="props">
                            {{ props.row.opponent }}
                        </b-table-column>

                        <b-table-column field="homeAway" label="Hjemme/Ude" v-slot="props">
                            <span v-if="props.row.isHome" class="tag is-info is-light">Hjemmebane</span>
                            <span v-else class="tag is-warning is-light">Udebane</span>
                        </b-table-column>

                        <b-table-column field="venue" label="Spillested" v-slot="props">
                            {{ props.row.venue }}
                        </b-table-column>

                        <b-table-column field="deadline" label="Svarfrist for spillere" v-slot="props">
                            <span class="has-text-weight-semibold">{{ formatDate(props.row.deadline) }} kl. 20:00</span>
                        </b-table-column>

                        <b-table-column label="Handlinger" v-slot="props" width="120">
                            <b-button size="is-small" icon-left="pencil" @click="editEvent(props.row)"></b-button>
                            <b-button size="is-small" type="is-danger" outlined icon-left="delete" class="ml-1" @click="deleteEvent(props.row.id)"></b-button>
                        </b-table-column>
                    </b-table>
                </card-component>
            </div>

            <!-- ========================================== -->
            <!-- VIEW 3: TILKENDEGIVELSER & RYKKER-HUB       -->
            <!-- ========================================== -->
            <div v-if="currentView === 'availability'" class="view-panel">
                <!-- Controls bar -->
                <div class="card mb-4">
                    <div class="card-content">
                        <div class="level">
                            <div class="level-left">
                                <div class="level-item">
                                    <b-field label="Vælg Hold">
                                        <b-select v-model="selectedTeamId">
                                            <option v-for="team in teams" :key="team.id" :value="team.id">
                                                {{ team.name }} ({{ team.tier }})
                                            </option>
                                        </b-select>
                                    </b-field>
                                </div>
                                <div class="level-item">
                                    <b-field label="Vælg Spillerunde">
                                        <b-select v-model="selectedRoundNumber">
                                            <option v-for="ev in activeTeamEvents" :key="ev.roundNumber" :value="ev.roundNumber">
                                                Runde {{ ev.roundNumber }} ({{ formatDate(ev.date) }} mod {{ ev.opponent }})
                                            </option>
                                        </b-select>
                                    </b-field>
                                </div>
                            </div>

                            <div class="level-right">
                                <!-- Messenger & Email Action Buttons -->
                                <div class="level-item buttons">
                                    <b-button type="is-info" icon-left="facebook-messenger" @click="openGroupCalloutModal">
                                        📢 Kopier hold-opråb til Messenger
                                    </b-button>
                                    <b-button type="is-link" icon-left="email-fast" @click="nudgeViaEmailAll">
                                        ✉️ Send email-påmindelse ({{ currentEventPendingCount }})
                                    </b-button>
                                </div>
                            </div>
                        </div>

                        <!-- Status Counter Badges -->
                        <div class="columns is-multiline mt-2">
                            <div class="column is-3">
                                <div class="notification is-success is-light has-text-centered py-3">
                                    <p class="heading">Bekræftet Tilgængelig</p>
                                    <p class="title is-4 has-text-success">🟢 {{ currentEventAvailableCount }} spillere</p>
                                </div>
                            </div>
                            <div class="column is-3">
                                <div class="notification is-danger is-light has-text-centered py-3">
                                    <p class="heading">Meldt Afbud</p>
                                    <p class="title is-4 has-text-danger">🔴 {{ currentEventUnavailableCount }} spillere</p>
                                </div>
                            </div>
                            <div class="column is-3">
                                <div class="notification is-warning is-light has-text-centered py-3">
                                    <p class="heading">Mangler Svar</p>
                                    <p class="title is-4 has-text-warning-dark">⚪ {{ currentEventPendingCount }} spillere</p>
                                </div>
                            </div>
                            <div class="column is-3">
                                <div class="notification is-danger is-light has-text-centered py-3">
                                    <p class="heading">Svarfrist</p>
                                    <p class="title is-5 has-text-danger">{{ currentEventDeadlinePassed ? '⚠️ UDLØBET' : 'Aktiv (' + formatDate(activeEvent.deadline) + ')' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Availability Matrix Table -->
                <card-component :title="'Tilkendegivelser: ' + activeTeam.name + ' - Runde ' + selectedRoundNumber" class="has-table">
                    <div class="p-3 is-flex is-justify-content-space-between is-align-items-center">
                        <div class="buttons has-addons mb-0">
                            <b-button size="is-small" :type="statusFilter === 'all' ? 'is-dark' : 'is-light'" @click="statusFilter = 'all'">Alle ({{ activeTeamRoster.length }})</b-button>
                            <b-button size="is-small" :type="statusFilter === 'available' ? 'is-success' : 'is-light'" @click="statusFilter = 'available'">🟢 Kan ({{ currentEventAvailableCount }})</b-button>
                            <b-button size="is-small" :type="statusFilter === 'pending' ? 'is-warning' : 'is-light'" @click="statusFilter = 'pending'">⚪ Mangler ({{ currentEventPendingCount }})</b-button>
                            <b-button size="is-small" :type="statusFilter === 'unavailable' ? 'is-danger' : 'is-light'" @click="statusFilter = 'unavailable'">🔴 Afbud ({{ currentEventUnavailableCount }})</b-button>
                        </div>
                        <span class="is-size-7 has-text-grey">💡 Tip: Klik på en spillers status for at ændre den manuelt på spillerens vegne</span>
                    </div>

                    <b-table :data="filteredAvailabilityList" striped hoverable>
                        <b-table-column field="name" label="Spiller" v-slot="props">
                            <strong>{{ props.row.name }}</strong>
                            <span v-if="props.row.gender === 'K'" class="tag is-small is-light is-danger ml-2">D</span>
                            <span v-else class="tag is-small is-light is-info ml-2">H</span>
                            <div class="is-size-7 has-text-grey">Point: {{ props.row.singlePoints }} HS / {{ props.row.doublePoints }} HD</div>
                        </b-table-column>

                        <b-table-column field="status" label="Tilkendegivelse (Klik for at ændre)" v-slot="props">
                            <div class="buttons are-small mb-0">
                                <b-button
                                    :type="props.row.status === 'AVAILABLE' ? 'is-success' : 'is-light'"
                                    icon-left="check"
                                    @click="setAvailability(props.row.id, 'AVAILABLE')">
                                    Kan
                                </b-button>
                                <b-button
                                    :type="props.row.status === 'UNAVAILABLE' ? 'is-danger' : 'is-light'"
                                    icon-left="close"
                                    @click="setAvailability(props.row.id, 'UNAVAILABLE')">
                                    Kan ikke
                                </b-button>
                                <b-button
                                    :type="props.row.status === 'PENDING' ? 'is-warning' : 'is-light'"
                                    icon-left="help"
                                    @click="setAvailability(props.row.id, 'PENDING')">
                                    Afventer
                                </b-button>
                            </div>
                            <div v-if="props.row.overriddenByCoach" class="is-size-7 has-text-info mt-1">
                                ✏️ Sat af holdleder
                            </div>
                        </b-table-column>

                        <b-table-column field="note" label="Bemærkning fra spiller" v-slot="props">
                            <span v-if="props.row.note" class="tag is-warning is-light has-text-weight-semibold">
                                💬 {{ props.row.note }}
                            </span>
                            <span v-else class="has-text-grey-lighter">—</span>
                        </b-table-column>

                        <b-table-column field="lastNudge" label="Opfølgning & Rykker" v-slot="props">
                            <div v-if="props.row.status === 'PENDING'">
                                <div class="is-size-7 mb-1" :class="{'has-text-danger': !props.row.lastNudgedAt}">
                                    {{ props.row.lastNudgedAt ? 'Rykket: ' + props.row.lastNudgedAt : '⚠️ Ikke rykket endnu' }}
                                    <span v-if="props.row.nudgeCount">({{ props.row.nudgeCount }}x)</span>
                                </div>
                                <b-button size="is-small" type="is-info" outlined icon-left="facebook-messenger" @click="copyDirect1on1Link(props.row)">
                                    Kopier direkte link til DM
                                </b-button>
                            </div>
                            <div v-else>
                                <span class="tag is-light is-success">Afklaret</span>
                            </div>
                        </b-table-column>
                    </b-table>
                </card-component>
            </div>

            <!-- ========================================== -->
            <!-- VIEW 4: HOLDOPSTILLING MED TILGÆNGELIGHED  -->
            <!-- ========================================== -->
            <div v-if="currentView === 'lineup'" class="view-panel">
                <div class="notification is-light is-primary mb-4">
                    <div class="level">
                        <div class="level-left">
                            <div>
                                <h3 class="title is-6 mb-1">Runde 3 - Fælles Holdopstilling (Danmarksserien + Sjællandsserien)</h3>
                                <p class="is-size-7">Se hvordan spillersøgningen i holdopstillingen direkte filtrerer og advarer baseret på spillernes tilkendegivelser.</p>
                            </div>
                        </div>
                        <div class="level-right">
                            <span class="tag is-success is-medium">Spilledato: Lørdag d. 25. okt 2025</span>
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <!-- Left Column: Availability-Aware PlayersListSearch -->
                    <div class="column is-5">
                        <div class="card">
                            <header class="card-header has-background-light">
                                <p class="card-header-title">
                                    <b-icon icon="account-search" class="mr-2"></b-icon>
                                    Spillere til rådighed (Runde 3)
                                </p>
                            </header>
                            <div class="card-content">
                                <!-- Filter Chips -->
                                <div class="buttons has-addons mb-3">
                                    <b-button size="is-small" :type="lineupPlayerFilter === 'all' ? 'is-dark' : 'is-light'" @click="lineupPlayerFilter = 'all'">Alle ({{ allPlayersPool.length }})</b-button>
                                    <b-button size="is-small" :type="lineupPlayerFilter === 'available' ? 'is-success' : 'is-light'" @click="lineupPlayerFilter = 'available'">🟢 Kun Kan ({{ availablePlayersPool.length }})</b-button>
                                    <b-button size="is-small" :type="lineupPlayerFilter === 'pending' ? 'is-warning' : 'is-light'" @click="lineupPlayerFilter = 'pending'">⚪ Mangler ({{ pendingPlayersPool.length }})</b-button>
                                    <b-button size="is-small" :type="lineupPlayerFilter === 'unavailable' ? 'is-danger' : 'is-light'" @click="lineupPlayerFilter = 'unavailable'">🔴 Afbud ({{ unavailablePlayersPool.length }})</b-button>
                                </div>

                                <b-input placeholder="Søg spiller..." v-model="lineupSearchText" icon="magnify" size="is-small" class="mb-3"></b-input>

                                <div class="player-search-list" style="max-height: 480px; overflow-y: auto;">
                                    <div
                                        v-for="player in filteredLineupPlayers"
                                        :key="player.id"
                                        class="box p-2 mb-2 is-clickable player-card"
                                        :class="{'has-background-white-ter': isPlayerSelected(player.id)}"
                                        @click="assignPlayerToFirstEmpty(player)">
                                        <div class="is-flex is-justify-content-space-between is-align-items-center">
                                            <div>
                                                <strong>{{ player.name }}</strong>
                                                <span v-if="player.gender === 'K'" class="tag is-small is-light is-danger ml-1">D</span>
                                                <span v-else class="tag is-small is-light is-info ml-1">H</span>
                                                <div class="is-size-7 has-text-grey">{{ player.singlePoints }} HS / {{ player.doublePoints }} HD</div>
                                                <div v-if="player.note" class="is-size-7 has-text-info">💬 {{ player.note }}</div>
                                            </div>

                                            <div class="has-text-right">
                                                <span v-if="player.status === 'AVAILABLE'" class="tag is-success is-small">🟢 Kan spille</span>
                                                <span v-else-if="player.status === 'UNAVAILABLE'" class="tag is-danger is-small">🔴 Afbud</span>
                                                <span v-else class="tag is-warning is-light is-small">⚪ Mangler svar</span>
                                                <b-button size="is-small" type="is-light" class="ml-2" icon-left="plus"></b-button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Lineup Sheet with DH Rules Validation -->
                    <div class="column is-7">
                        <card-component title="1. Hold - Danmarksserien (Runde 3)" class="mb-4">
                            <template #header-right>
                                <span class="tag is-success">Regel § 38: Opstilling lovlig</span>
                            </template>

                            <table class="table is-fullwidth is-striped is-narrow">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Spiller(e)</th>
                                        <th>Point</th>
                                        <th>Handling</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(slot, idx) in lineupSlots" :key="slot.name">
                                        <td class="has-text-weight-semibold">{{ slot.name }}</td>
                                        <td>
                                            <div v-if="slot.player" class="is-flex is-align-items-center">
                                                <span>{{ slot.player.name }}</span>
                                                <span v-if="slot.player.status === 'UNAVAILABLE'" class="tag is-danger is-small ml-2">⚠️ Har meldt afbud!</span>
                                                <span v-else-if="slot.player.status === 'AVAILABLE'" class="tag is-success is-light is-small ml-2">🟢 Klar</span>
                                                <span v-else class="tag is-warning is-light is-small ml-2">⚪ Mangler svar</span>
                                            </div>
                                            <span v-else class="has-text-grey-light is-italic">Ledig plads (klik på spiller til venstre)</span>
                                        </td>
                                        <td>{{ slot.player ? slot.player.singlePoints + ' p' : '—' }}</td>
                                        <td>
                                            <b-button v-if="slot.player" size="is-small" type="is-danger" outlined icon-left="close" @click="clearSlot(idx)"></b-button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </card-component>

                        <!-- Rule Validation Info Box -->
                        <div class="notification is-light is-info">
                            <h4 class="title is-6 mb-1">Badminton Danmarks reglement § 38:</h4>
                            <p class="is-size-7">
                                • <strong>Opfyldning fra oven (§ 38 stk. 1c):</strong> 1. holdet skal altid stilles stærkest muligt med tilgængelige spillere før 2. holdet udtages.<br/>
                                • <strong>Pointmargen (§ 38 stk. 2):</strong> Spillere i singler skal stå efter ranglistepoint (frit valg ved &le; 50 point forskel).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- FLOATING PROTOTYPE SWITCHER (Skill compliant) -->
        <!-- ========================================== -->
        <div class="prototype-floating-bar" dusk="prototype-switcher">
            <button class="switcher-arrow" @click="prevView" title="Forrige visning (Venstre piletast)">◀</button>
            <div class="switcher-label">
                <span class="has-text-weight-bold">{{ activeViewIndex + 1 }}/{{ viewList.length }}:</span>
                <span class="ml-1">{{ activeViewTitle }}</span>
            </div>
            <button class="switcher-arrow" @click="nextView" title="Næste visning (Højre piletast)">▶</button>
        </div>

        <!-- ========================================== -->
        <!-- MODALS (Mock actions)                      -->
        <!-- ========================================== -->
        <!-- Modal: Import from Badmintonplayer -->
        <b-modal v-model="isImportModalActive" :width="640" scroll="keep">
            <div class="card">
                <header class="card-header has-background-success-light">
                    <p class="card-header-title">Importer Kampprogram fra Badmintonplayer.dk</p>
                </header>
                <div class="card-content">
                    <p class="mb-3">Vælg dit hold på Badmintonplayer for automatisk at hente alle runder, spilledatoer og spillesteder:</p>
                    <b-field label="Klub & Række">
                        <b-select expanded v-model="mockSelectedImportTeam">
                            <option value="team1">Hvidovre BC - Danmarksserien Kreds 2 (Hold 1)</option>
                            <option value="team2">Hvidovre BC - Sjællandsserien Kreds 1 (Hold 2)</option>
                            <option value="team3">Hvidovre BC - Serie 1 Kreds 4 (Hold 3)</option>
                        </b-select>
                    </b-field>
                    <div class="notification is-light is-info is-size-7">
                        ✅ 7 officielle spillerunder fundet for sæsonen 2025/2026.
                    </div>
                </div>
                <footer class="card-footer">
                    <button class="card-footer-item button" @click="isImportModalActive = false">Annuller</button>
                    <button class="card-footer-item button is-success" @click="confirmImport">Importer 7 kampe</button>
                </footer>
            </div>
        </b-modal>

        <!-- Modal: Group Callout Messenger Text -->
        <b-modal v-model="isGroupCalloutModalActive" :width="560" scroll="keep">
            <div class="card">
                <header class="card-header has-background-info-light">
                    <p class="card-header-title">📢 Messenger Hold-opråb (Kopieret)</p>
                </header>
                <div class="card-content">
                    <p class="mb-2 is-size-7">Teksten nedenfor er formateret og klar til at blive sat ind i jeres hold-gruppe på Messenger:</p>
                    <div class="box has-background-dark has-text-light p-3 is-family-monospace is-size-7">
                        Hej Senior 1! 🏸<br/><br/>
                        Husk at melde ind til kampen på lørdag d. 25. okt mod Hvidovre BC 2.<br/><br/>
                        Vi mangler svar fra følgende:<br/>
                        <span v-for="p in pendingInCurrentEvent" :key="p.id">• {{ p.name }}<br/></span>
                        <br/>
                        👉 Gå ind og meld 'Kan' eller 'Kan ikke' her: https://nembadminton.dk/svar/senior-1-r3
                    </div>
                </div>
                <footer class="card-footer">
                    <button class="card-footer-item button is-info" @click="copyGroupCalloutText">Kopier til udklipsholder</button>
                    <button class="card-footer-item button" @click="isGroupCalloutModalActive = false">Luk</button>
                </footer>
            </div>
        </b-modal>
    </div>
</template>

<script>
import TitleBar from "@/components/TitleBar.vue";
import HeroBar from "@/components/HeroBar.vue";
import CardComponent from "@/components/CardComponent.vue";

export default {
    name: "CoachFlowPrototype",
    components: { CardComponent, HeroBar, TitleBar },
    data() {
        return {
            currentView: "availability", // 'roster', 'events', 'availability', 'lineup'
            selectedSeason: "2025/2026",
            selectedTeamId: 1,
            selectedRoundNumber: 3,
            statusFilter: "all",
            lineupPlayerFilter: "all",
            lineupSearchText: "",
            isImportModalActive: false,
            isGroupCalloutModalActive: false,
            mockSelectedImportTeam: "team1",

            // Teams
            teams: [
                { id: 1, name: "1. Senior", tier: "Danmarksserien", group: "Pulje 2" },
                { id: 2, name: "2. Senior", tier: "Sjællandsserien", group: "Pulje 1" },
                { id: 3, name: "3. Senior", tier: "Serie 1", group: "Pulje 4" }
            ],

            // Match Schedule / Events (Holdkampe)
            events: [
                { id: 101, teamId: 1, roundNumber: 1, date: "2025-09-13", time: "13:00", opponent: "Solrød Strand 2", isHome: true, venue: "Hal A, Bane 1-4", deadline: "2025-09-10" },
                { id: 102, teamId: 1, roundNumber: 2, date: "2025-09-27", time: "14:00", opponent: "Greve 2", isHome: false, venue: "Greve Idrætscenter", deadline: "2025-09-24" },
                { id: 103, teamId: 1, roundNumber: 3, date: "2025-10-25", time: "13:00", opponent: "Hvidovre BC 2", isHome: true, venue: "Hal A, Bane 1-4", deadline: "2025-10-22" },
                { id: 104, teamId: 1, roundNumber: 4, date: "2025-11-15", time: "13:30", opponent: "Gentofte 3", isHome: false, venue: "Gentoftehallen", deadline: "2025-11-12" },
                { id: 105, teamId: 1, roundNumber: 5, date: "2025-12-06", time: "13:00", opponent: "Skovshoved 2", isHome: true, venue: "Hal A, Bane 1-4", deadline: "2025-12-03" },
                { id: 106, teamId: 1, roundNumber: 6, date: "2026-01-17", time: "12:00", opponent: "Værløse 2", isHome: false, venue: "Værløse Hallerne", deadline: "2026-01-14" },
                { id: 107, teamId: 1, roundNumber: 7, date: "2026-02-07", time: "13:00", opponent: "Odense OBK 2", isHome: true, venue: "Hal A, Bane 1-4", deadline: "2026-02-04" },

                { id: 201, teamId: 2, roundNumber: 1, date: "2025-09-13", time: "15:30", opponent: "Herlev/Hjorten 1", isHome: true, venue: "Hal A, Bane 1-4", deadline: "2025-09-10" },
                { id: 202, teamId: 2, roundNumber: 2, date: "2025-09-27", time: "11:00", opponent: "Ringsted 1", isHome: false, venue: "Ringsted Sportscenter", deadline: "2025-09-24" },
                { id: 203, teamId: 2, roundNumber: 3, date: "2025-10-25", time: "15:30", opponent: "Holbæk 1", isHome: true, venue: "Hal A, Bane 1-4", deadline: "2025-10-22" }
            ],

            // Players Roster & Availability (Trup & Svar)
            players: [
                { id: 1, teamId: 1, name: "Christian Lind Thomsen", gender: "M", singlePoints: 4850, doublePoints: 4720, isRegistered: true, email: "clt@example.com", statusByRound: { 1: "AVAILABLE", 2: "AVAILABLE", 3: "AVAILABLE" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 2, teamId: 1, name: "Rasmus Gemke", gender: "M", singlePoints: 4620, doublePoints: 4300, isRegistered: true, email: "rg@example.com", statusByRound: { 1: "AVAILABLE", 2: "UNAVAILABLE", 3: "AVAILABLE" }, notesByRound: { 3: "Kan køre fra kl. 11" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 3, teamId: 1, name: "Mathias Christiansen", gender: "M", singlePoints: 3950, doublePoints: 4980, isRegistered: true, email: "mc@example.com", statusByRound: { 1: "AVAILABLE", 2: "AVAILABLE", 3: "PENDING" }, notesByRound: { 3: "" }, lastNudgedAt: "2 dage siden", nudgeCount: 1 },
                { id: 4, teamId: 1, name: "Mads Pieler Kolding", gender: "M", singlePoints: 3400, doublePoints: 4890, isRegistered: false, email: null, statusByRound: { 1: "AVAILABLE", 2: "AVAILABLE", 3: "PENDING" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 5, teamId: 1, name: "Kim Astrup", gender: "M", singlePoints: 3200, doublePoints: 5120, isRegistered: true, email: "ka@example.com", statusByRound: { 1: "AVAILABLE", 2: "AVAILABLE", 3: "AVAILABLE" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 6, teamId: 1, name: "Anders Skaarup", gender: "M", singlePoints: 3150, doublePoints: 5090, isRegistered: false, email: "as@example.com", statusByRound: { 1: "AVAILABLE", 2: "PENDING", 3: "PENDING" }, notesByRound: { 3: "" }, lastNudgedAt: "I går", nudgeCount: 2 },
                { id: 7, teamId: 1, name: "Line Kjærsfeldt", gender: "K", singlePoints: 4720, doublePoints: 4400, isRegistered: true, email: "lk@example.com", statusByRound: { 1: "AVAILABLE", 2: "AVAILABLE", 3: "AVAILABLE" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 8, teamId: 1, name: "Mia Blichfeldt", gender: "K", singlePoints: 4680, doublePoints: 3900, isRegistered: true, email: "mb@example.com", statusByRound: { 1: "AVAILABLE", 2: "AVAILABLE", 3: "UNAVAILABLE" }, notesByRound: { 3: "Skadet i lysken" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 9, teamId: 1, name: "Sara Thygesen", gender: "K", singlePoints: 3200, doublePoints: 4850, isRegistered: false, email: null, statusByRound: { 1: "AVAILABLE", 2: "AVAILABLE", 3: "AVAILABLE" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 10, teamId: 1, name: "Maiken Fruergaard", gender: "K", singlePoints: 3100, doublePoints: 4810, isRegistered: true, email: "mf@example.com", statusByRound: { 1: "AVAILABLE", 2: "PENDING", 3: "PENDING" }, notesByRound: { 3: "" }, lastNudgedAt: "I går", nudgeCount: 1 },
                { id: 11, teamId: 1, name: "Amalie Magelund", gender: "K", singlePoints: 2900, doublePoints: 4650, isRegistered: true, email: "am@example.com", statusByRound: { 1: "AVAILABLE", 2: "AVAILABLE", 3: "AVAILABLE" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 12, teamId: 1, name: "Alexandra Bøje", gender: "K", singlePoints: 2850, doublePoints: 4790, isRegistered: false, email: null, statusByRound: { 1: "AVAILABLE", 2: "AVAILABLE", 3: "PENDING" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },

                // 2. Senior players
                { id: 13, teamId: 2, name: "Victor Svendsen", gender: "M", singlePoints: 4200, doublePoints: 3800, isRegistered: true, email: "vs@example.com", statusByRound: { 3: "AVAILABLE" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 14, teamId: 2, name: "Karan Rajan", gender: "M", singlePoints: 4050, doublePoints: 3750, isRegistered: true, email: "kr@example.com", statusByRound: { 3: "AVAILABLE" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 15, teamId: 2, name: "Frederik Søgaard", gender: "M", singlePoints: 2800, doublePoints: 4400, isRegistered: false, email: null, statusByRound: { 3: "PENDING" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 16, teamId: 2, name: "Julie Dawall Jakobsen", gender: "K", singlePoints: 4100, doublePoints: 3600, isRegistered: true, email: "jd@example.com", statusByRound: { 3: "AVAILABLE" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 },
                { id: 17, teamId: 2, name: "Irina Amalie Andersen", gender: "K", singlePoints: 3900, doublePoints: 3700, isRegistered: false, email: null, statusByRound: { 3: "PENDING" }, notesByRound: { 3: "" }, lastNudgedAt: null, nudgeCount: 0 }
            ],

            // Squad Lineup Sheet slots (View 4)
            lineupSlots: [
                { name: "1. Herresingle", player: null },
                { name: "2. Herresingle", player: null },
                { name: "3. Herresingle", player: null },
                { name: "4. Herresingle", player: null },
                { name: "1. Damesingle", player: null },
                { name: "2. Damesingle", player: null },
                { name: "1. Herredouble", player: null },
                { name: "1. Damedouble", player: null },
                { name: "1. Mixeddouble", player: null }
            ],

            viewList: [
                { key: "roster", title: "1. Holdtrup & Onboarding" },
                { key: "events", title: "2. Kampprogram & Import" },
                { key: "availability", title: "3. Tilkendegivelser & Rykker-Hub" },
                { key: "lineup", title: "4. Holdopstilling med Tilgængelighed" }
            ]
        };
    },
    computed: {
        activeTeam() {
            return this.teams.find(t => t.id === this.selectedTeamId) || this.teams[0];
        },
        activeTeamRoster() {
            return this.players.filter(p => p.teamId === this.selectedTeamId);
        },
        uninvitedCount() {
            return this.activeTeamRoster.filter(p => !p.isRegistered).length;
        },
        activeTeamEvents() {
            return this.events.filter(e => e.teamId === this.selectedTeamId);
        },
        activeEvent() {
            return this.activeTeamEvents.find(e => e.roundNumber === this.selectedRoundNumber) || this.activeTeamEvents[0] || {};
        },
        currentEventDeadlinePassed() {
            if (!this.activeEvent.deadline) return false;
            return new Date(this.activeEvent.deadline) < new Date();
        },
        availabilityListForCurrentEvent() {
            return this.activeTeamRoster.map(player => ({
                id: player.id,
                name: player.name,
                gender: player.gender,
                singlePoints: player.singlePoints,
                doublePoints: player.doublePoints,
                status: (player.statusByRound && player.statusByRound[this.selectedRoundNumber]) || "PENDING",
                note: (player.notesByRound && player.notesByRound[this.selectedRoundNumber]) || "",
                lastNudgedAt: player.lastNudgedAt,
                nudgeCount: player.nudgeCount,
                overriddenByCoach: player.overriddenByCoach || false
            }));
        },
        filteredAvailabilityList() {
            if (this.statusFilter === "all") return this.availabilityListForCurrentEvent;
            return this.availabilityListForCurrentEvent.filter(p => p.status.toLowerCase() === this.statusFilter);
        },
        currentEventAvailableCount() {
            return this.availabilityListForCurrentEvent.filter(p => p.status === "AVAILABLE").length;
        },
        currentEventUnavailableCount() {
            return this.availabilityListForCurrentEvent.filter(p => p.status === "UNAVAILABLE").length;
        },
        currentEventPendingCount() {
            return this.availabilityListForCurrentEvent.filter(p => p.status === "PENDING").length;
        },
        totalPendingCount() {
            return this.players.filter(p => {
                const st = (p.statusByRound && p.statusByRound[this.selectedRoundNumber]) || "PENDING";
                return st === "PENDING";
            }).length;
        },
        pendingInCurrentEvent() {
            return this.availabilityListForCurrentEvent.filter(p => p.status === "PENDING");
        },

        // Lineup view computed pools
        allPlayersPool() {
            return this.players.map(p => ({
                id: p.id,
                name: p.name,
                gender: p.gender,
                singlePoints: p.singlePoints,
                doublePoints: p.doublePoints,
                status: (p.statusByRound && p.statusByRound[3]) || "PENDING",
                note: (p.notesByRound && p.notesByRound[3]) || ""
            }));
        },
        availablePlayersPool() {
            return this.allPlayersPool.filter(p => p.status === "AVAILABLE");
        },
        pendingPlayersPool() {
            return this.allPlayersPool.filter(p => p.status === "PENDING");
        },
        unavailablePlayersPool() {
            return this.allPlayersPool.filter(p => p.status === "UNAVAILABLE");
        },
        filteredLineupPlayers() {
            let list = this.allPlayersPool;
            if (this.lineupPlayerFilter === "available") list = this.availablePlayersPool;
            else if (this.lineupPlayerFilter === "pending") list = this.pendingPlayersPool;
            else if (this.lineupPlayerFilter === "unavailable") list = this.unavailablePlayersPool;

            if (this.lineupSearchText) {
                const search = this.lineupSearchText.toLowerCase();
                list = list.filter(p => p.name.toLowerCase().includes(search));
            }
            return list;
        },

        // Switcher computed properties
        activeViewIndex() {
            return this.viewList.findIndex(v => v.key === this.currentView);
        },
        activeViewTitle() {
            const v = this.viewList.find(x => x.key === this.currentView);
            return v ? v.title : "";
        }
    },
    mounted() {
        // Sync with URL query parameter
        if (this.$route.query.view) {
            this.currentView = this.$route.query.view;
        }
        window.addEventListener("keydown", this.handleKeyDown);

        // Populate a few initial lineup slots for realistic look
        this.lineupSlots[0].player = this.players[0]; // Christian Lind Thomsen (Available)
        this.lineupSlots[1].player = this.players[1]; // Rasmus Gemke (Available)
        this.lineupSlots[4].player = this.players[6]; // Line Kjærsfeldt (Available)
    },
    beforeUnmount() {
        window.removeEventListener("keydown", this.handleKeyDown);
    },
    methods: {
        setView(viewKey) {
            this.currentView = viewKey;
            this.$router.replace({ query: { ...this.$route.query, view: viewKey } });
        },
        prevView() {
            let nextIndex = this.activeViewIndex - 1;
            if (nextIndex < 0) nextIndex = this.viewList.length - 1;
            this.setView(this.viewList[nextIndex].key);
        },
        nextView() {
            let nextIndex = this.activeViewIndex + 1;
            if (nextIndex >= this.viewList.length) nextIndex = 0;
            this.setView(this.viewList[nextIndex].key);
        },
        handleKeyDown(e) {
            if (["INPUT", "TEXTAREA", "SELECT"].includes(e.target.tagName)) return;
            if (e.key === "ArrowLeft") {
                this.prevView();
            } else if (e.key === "ArrowRight") {
                this.nextView();
            }
        },
        formatDate(dateStr) {
            if (!dateStr) return "—";
            const d = new Date(dateStr);
            return d.toLocaleDateString("da-DK", { day: "numeric", month: "short", year: "numeric" });
        },

        // View 1 actions
        copyGroupInviteToClipboard() {
            const text = `Hej allesammen! 🏸 Velkommen til sæsonen 2025/2026 for ${this.activeTeam.name}. Gå ind og bekræft din spillerprofil her, så du kan melde tilgængelighed til kampene: https://nembadminton.dk/join/senior-1-2025`;
            navigator.clipboard.writeText(text);
            this.$buefy.toast.open({
                message: "✅ Hold-invitation kopieret! Klar til Messenger.",
                type: "is-success"
            });
        },
        copyPersonalInvite(player) {
            const text = `Hej ${player.name.split(" ")[0]}! Her er dit direkte link til at oprette din profil for ${this.activeTeam.name}: https://nembadminton.dk/invite/p/${player.id}?team=${this.selectedTeamId}`;
            navigator.clipboard.writeText(text);
            this.$buefy.toast.open({
                message: `✅ Direkte invitationslink for ${player.name} kopieret!`,
                type: "is-info"
            });
        },
        inviteAllPending() {
            this.activeTeamRoster.forEach(p => {
                if (!p.isRegistered && p.email) {
                    p.isRegistered = true;
                }
            });
            this.$buefy.toast.open({
                message: "✉️ E-mail invitationer afsendt til alle spillere med e-mail!",
                type: "is-success"
            });
        },
        cloneRosterFromPreviousSeason() {
            this.$buefy.toast.open({
                message: "🔄 Truppen er klonet fra sæsonen 2024/2025 (12 spillere tilføjet)",
                type: "is-primary"
            });
        },
        removePlayerFromTeam(playerId) {
            const idx = this.players.findIndex(p => p.id === playerId);
            if (idx !== -1) {
                this.players.splice(idx, 1);
                this.$buefy.toast.open({ message: "Spiller fjernet fra trup", type: "is-warning" });
            }
        },

        // View 2 actions
        openImportModal() {
            this.isImportModalActive = true;
        },
        confirmImport() {
            this.isImportModalActive = false;
            this.$buefy.toast.open({
                message: "🎉 7 spillerunder succesfuldt importeret fra Badmintonplayer.dk!",
                type: "is-success",
                duration: 4000
            });
        },
        addNewMatchRow() {
            const nextRound = this.activeTeamEvents.length + 1;
            this.events.push({
                id: Date.now(),
                teamId: this.selectedTeamId,
                roundNumber: nextRound,
                date: "2026-03-01",
                time: "13:00",
                opponent: "Nyt Hold",
                isHome: true,
                venue: "Hjemmebane Hal A",
                deadline: "2026-02-26"
            });
            this.$buefy.toast.open({ message: `Runde ${nextRound} oprettet`, type: "is-info" });
        },
        editEvent(event) {
            this.$buefy.toast.open({ message: `Redigerer runde ${event.roundNumber}`, type: "is-light" });
        },
        deleteEvent(eventId) {
            const idx = this.events.findIndex(e => e.id === eventId);
            if (idx !== -1) {
                this.events.splice(idx, 1);
                this.$buefy.toast.open({ message: "Kamp fjernet", type: "is-danger" });
            }
        },

        // View 3 actions
        setAvailability(playerId, status) {
            const player = this.players.find(p => p.id === playerId);
            if (player) {
                if (!player.statusByRound) player.statusByRound = {};
                player.statusByRound[this.selectedRoundNumber] = status;
                player.overriddenByCoach = true;
                const statusMap = { AVAILABLE: "🟢 Kan", UNAVAILABLE: "🔴 Kan ikke", PENDING: "⚪ Mangler svar" };
                this.$buefy.toast.open({
                    message: `${player.name} sat til ${statusMap[status]} af holdleder`,
                    type: "is-info",
                    duration: 2000
                });
            }
        },
        openGroupCalloutModal() {
            this.isGroupCalloutModalActive = true;
        },
        copyGroupCalloutText() {
            this.isGroupCalloutModalActive = false;
            this.$buefy.toast.open({
                message: "📋 Messenger opråb kopieret! Sæt det direkte ind i jeres Messenger tråd.",
                type: "is-success"
            });
        },
        copyDirect1on1Link(player) {
            player.lastNudgedAt = "Lige nu";
            player.nudgeCount = (player.nudgeCount || 0) + 1;
            const text = `Hej ${player.name.split(" ")[0]}! Vi mangler dit svar til kampen på lørdag (Runde ${this.selectedRoundNumber}). Kan du spille? Svar i 1-klik her: https://nembadminton.dk/svar/p/${player.id}?round=${this.selectedRoundNumber}`;
            navigator.clipboard.writeText(text);
            this.$buefy.toast.open({
                message: `✅ Direkte link til ${player.name} kopieret!`,
                type: "is-info"
            });
        },
        nudgeViaEmailAll() {
            this.$buefy.toast.open({
                message: `✉️ E-mail påmindelser sendt til ${this.currentEventPendingCount} spillere!`,
                type: "is-success"
            });
        },

        // View 4 Lineup actions
        isPlayerSelected(playerId) {
            return this.lineupSlots.some(s => s.player && s.player.id === playerId);
        },
        assignPlayerToFirstEmpty(player) {
            const emptySlot = this.lineupSlots.find(s => !s.player);
            if (emptySlot) {
                emptySlot.player = player;
                this.$buefy.toast.open({
                    message: `${player.name} sat på ${emptySlot.name}`,
                    type: "is-success"
                });
            } else {
                this.$buefy.toast.open({
                    message: "Alle pladser er optaget. Fjern en spiller først.",
                    type: "is-warning"
                });
            }
        },
        clearSlot(slotIndex) {
            this.lineupSlots[slotIndex].player = null;
        }
    }
};
</script>

<style scoped>
.prototype-container {
    padding-bottom: 5rem;
}

.player-card:hover {
    border-color: #3273dc;
    transform: translateY(-1px);
    transition: all 0.15s ease-in-out;
}

/* Floating Switcher Bar (Skill compliant) */
.prototype-floating-bar {
    position: fixed;
    bottom: 1.5rem;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: #ffffff;
    border-radius: 9999px;
    padding: 0.5rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
    z-index: 9999;
    font-size: 0.875rem;
    user-select: none;
}

.switcher-arrow {
    background: #374151;
    color: #ffffff;
    border: none;
    border-radius: 9999px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s ease;
}

.switcher-arrow:hover {
    background: #4b5563;
}

.switcher-label {
    white-space: nowrap;
}
</style>

<template>
    <form @submit.prevent>
        <div class="modal-card" style="width: auto">
            <header class="modal-card-head">
                <p class="modal-card-title">Notifikationer</p>
                <button
                    class="delete"
                    type="button"
                    @click="$parent.close()"/>
            </header>
            <section class="modal-card-body">
                <p class="mb-2">Få notifikation når der sker ændringer på holdet.</p>
                <hr/>
                <div v-if="!loggedIn" class="buttons">
                    <b-button v-if="(showLogin || showCreateUser)" expanded @click="showLogin = false; showCreateUser = false">Tilbage</b-button>
                    <b-button v-if="!(showLogin || showCreateUser)" expanded @click="showLogin = !showLogin">Login</b-button>
                    <b-button v-if="!(showLogin || showCreateUser)" expanded @click="showCreateUser = !showCreateUser">Opret bruger</b-button>
                </div>
                <create-user v-if="showCreateUser" :after-register="refresh"></create-user>
                <login v-if="showLogin" :after-login="refresh"></login>
                <notification v-if="loggedIn"></notification>
            </section>
            <footer v-if="loggedIn" class="modal-card-foot">
                <b-button
                    label="Luk"
                    @click="$parent.close()"/>
            </footer>
        </div>
    </form>
</template>

<script>
import Notification from "../notification/Notification"
import CreateUser from "../../views/CreateUser";
import {isLoggedIn} from "../../auth";
import Login from "../../views/Login";

export default {
    name: "CreateNotification",
    components: {Login, CreateUser, Notification},
    data() {
        return {
            email: '',
            loggedIn: false,
            showCreateUser: false,
            showLogin: false
        }
    },
    mounted() {
        this.loggedIn = isLoggedIn();
    },
    methods: {
        refresh() {
            this.loggedIn = true
            this.showLogin = false
            this.showCreateUser = false
        }
    }
}
</script>

<style scoped>

</style>

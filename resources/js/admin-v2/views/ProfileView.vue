<template>
  <div>
    <title-bar :title-stack="titleStack" />
    <hero-bar>
      Profile
    </hero-bar>
    <section class="section is-main-section">
      <div class="tile is-ancestor">
        <div class="tile is-parent">
          <profile-update-form class="tile is-child" />
        </div>
        <div class="tile is-parent">
          <card-component
            title="Profil"
            icon="account"
            class="tile is-child"
          >
            <user-avatar class="image has-max-width is-aligned-center" />
            <hr>
            <b-field label="Name">
              <b-input
                :model-value="userName"
                custom-class="is-static"
                readonly
              />
            </b-field>
            <hr>
            <b-field label="E-mail">
              <b-input
                :model-value="userEmail"
                custom-class="is-static"
                readonly
              />
            </b-field>
          </card-component>
        </div>
      </div>
      <password-update-form />
      <connected-accounts-card />
    </section>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import { currentUser } from '@/store/user'
import CardComponent from '@/components/CardComponent.vue'
import TitleBar from '@/components/TitleBar.vue'
import HeroBar from '@/components/HeroBar.vue'
import ProfileUpdateForm from '@/components/ProfileUpdateForm.vue'
import PasswordUpdateForm from '@/components/PasswordUpdateForm.vue'
import ConnectedAccountsCard from '@/components/ConnectedAccountsCard.vue'
import UserAvatar from '@/components/UserAvatar.vue'

export default defineComponent({
  name: 'ProfileView',
  components: {
    UserAvatar,
    PasswordUpdateForm,
    ProfileUpdateForm,
    ConnectedAccountsCard,
    HeroBar,
    TitleBar,
    CardComponent
  },
  data () {
    return {
      titleStack: ['Admin', 'Profile']
    }
  },
  computed: {
    userName () {
      return currentUser.userName
    },
    userEmail () {
      return currentUser.userEmail
    }
  }
})
</script>

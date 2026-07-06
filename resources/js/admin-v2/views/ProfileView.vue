<template>
  <div>
    <title-bar :title-stack="titleStack" />
    <hero-bar>
      Profile
    </hero-bar>
    <section class="section is-main-section">
      <tiles-block>
        <profile-update-form class="tile is-child" />
        <card-component
          title="Profil"
          icon="account"
          class="tile is-child"
        >
          <user-avatar class="image has-max-width is-aligned-center" />
          <hr>
          <b-field label="Name">
            <b-input
              :value="userName"
              custom-class="is-static"
              readonly
            />
          </b-field>
          <hr>
          <b-field label="E-mail">
            <b-input
              :value="userEmail"
              custom-class="is-static"
              readonly
            />
          </b-field>
        </card-component>
      </tiles-block>
      <password-update-form />
      <connected-accounts-card />
    </section>
  </div>
</template>

<script>
import { defineComponent } from 'vue'
import { mapState } from 'vuex'
import CardComponent from '@/components/CardComponent.vue'
import TitleBar from '@/components/TitleBar.vue'
import HeroBar from '@/components/HeroBar.vue'
import ProfileUpdateForm from '@/components/ProfileUpdateForm.vue'
import PasswordUpdateForm from '@/components/PasswordUpdateForm.vue'
import ConnectedAccountsCard from '@/components/ConnectedAccountsCard.vue'
import TilesBlock from '@/components/TilesBlock.vue'
import UserAvatar from '@/components/UserAvatar.vue'

export default defineComponent({
  name: 'ProfileView',
  components: {
    UserAvatar,
    TilesBlock,
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
    ...mapState([
      'userName',
      'userEmail'
    ])
  }
})
</script>

<template>
    <nav
        v-show="isNavBarVisible"
        id="navbar-main"
        class="navbar is-fixed-top"
    >
        <div class="navbar-brand">
            <a
                class="navbar-item is-hidden-desktop"
                @click.prevent="asideToggleMobile"
            >
                <b-icon :icon="asideMobileIcon"/>
            </a>
            <a
                class="navbar-item is-hidden-touch is-hidden-widescreen is-desktop-icon-only"
                @click.prevent="asideDesktopOnlyToggle"
            >
                <b-icon icon="menu"/>
            </a>
            <!--      <div class="navbar-item has-control no-left-space-touch no-left-space-desktop-only">-->
            <!--        <div class="control">-->
            <!--          <input-->
            <!--            class="input"-->
            <!--            placeholder="Search everywhere..."-->
            <!--          >-->
            <!--        </div>-->
            <!--      </div>-->
        </div>
        <div class="navbar-brand is-right">
            <a
                class="navbar-item navbar-item-menu-toggle is-hidden-desktop"
                @click.prevent="menuToggle"
            >
                <b-icon
                    :icon="menuToggleIcon"
                    custom-size="default"
                />
            </a>
        </div>
        <div
            class="navbar-menu fadeIn animated faster"
            :class="{ 'is-active': isMenuActive }"
        >
            <div class="navbar-end">
                <notification-dropdown/>
                <nav-bar-menu class="has-divider has-user-avatar">
                    <user-avatar/>
                    <div class="is-user-name">
                        <span>{{ userName }}</span>
                    </div>

                    <div
                        slot="dropdown"
                        class="navbar-dropdown is-right"
                    >
                        <router-link
                            :to="{name: 'profile'}"
                            class="navbar-item"
                            exact-active-class="is-active"
                        >
                            <b-icon
                                icon="account"
                                custom-size="default"
                            />
                            <span>Min Profil</span>
                        </router-link>
                        <router-link
                            :to="{path: '/c-'+clubhouseId+'/club-house'}"
                            class="navbar-item"
                            exact-active-class="is-active"
                        >
                            <b-icon
                                icon="home"
                                custom-size="default"
                            />
                            <span>Mit klubhus</span>
                        </router-link>
                        <hr class="navbar-divider">
                        <a @click="logout" class="navbar-item">
                            <b-icon
                                icon="logout"
                                custom-size="default"
                            />
                            <span>Log ud</span>
                        </a>
                    </div>
                </nav-bar-menu>
            </div>
        </div>
    </nav>
</template>

<script>
import {defineComponent} from 'vue'
import {mapState} from 'vuex'
import NavBarMenu from '@/components/NavBarMenu.vue'
import UserAvatar from '@/components/UserAvatar.vue'
import NotificationDropdown from "./NotificationDropdown.vue";

export default defineComponent(
    {
        name: 'NavBar',
        components: {
            NotificationDropdown,
            UserAvatar,
            NavBarMenu
        },
        inject: ['clubhouseId'],
        data() {
            return {
                isMenuActive: false
            }
        },
        computed: {
            asideMobileIcon() {
                return this.isAsideMobileExpanded
                       ? 'backburger'
                       : 'forwardburger'
            },
            menuToggleIcon() {
                return this.isMenuActive
                       ? 'close'
                       : 'dots-vertical'
            },
            ...mapState([
                            'isAsideMobileExpanded',
                            'isNavBarVisible',
                            'userName'
                        ])
        },
        mounted() {
            this.$router.afterEach(() => {
                this.isMenuActive = false
            })
        },
        methods: {
            asideToggleMobile() {
                this.$store.commit('asideMobileStateToggle')
            },
            asideDesktopOnlyToggle() {
                this.$store.dispatch('asideDesktopOnlyToggle')
            },
            menuToggle() {
                this.isMenuActive = !this.isMenuActive
            },
            logout() {
                this.$store.commit('logout')
                this.$router.push('/login')
                this.$buefy.snackbar.open({
                                              message: 'Vi ses!',
                                              queue: false
                                          })
            }
        }
    })
</script>

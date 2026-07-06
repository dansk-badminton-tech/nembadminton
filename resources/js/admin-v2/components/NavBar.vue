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
            v-if="!hideRightNav"
            class="navbar-menu fadeIn animated faster"
            :class="{ 'is-active': isMenuActive }"
        >
            <div class="navbar-end">
                <notification-dropdown/>
                <nav-bar-menu class="has-divider has-user-avatar">
                    <user-avatar/>
                    <div class="is-user-name">
                        <span>{{ user?.name }}</span>
                    </div>

                    <div
                        slot="dropdown"
                        class="navbar-dropdown is-right"
                    >
                        <div v-if="showSwitcher" class="navbar-item is-label">
                            <span class="has-text-grey-light is-size-7">Skift rolle</span>
                        </div>
                        <a
                            v-for="role in switchableRoles"
                            v-if="showSwitcher"
                            :key="role.id"
                            class="navbar-item"
                            :class="{ 'is-active': role.isCurrent }"
                            @click="switchRole(role.id)"
                        >
                            <b-icon
                                :icon="role.isCurrent ? 'check-circle' : 'circle-outline'"
                                custom-size="default"
                            />
                            <span>{{ role.label }}</span>
                        </a>
                        <hr v-if="showSwitcher" class="navbar-divider">
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
                            v-if="!hideClubhouseNav"
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
import {roleLabels} from '@/helpers.js'
import SET_PRIMARY_ROLE from '../../queries/setPrimaryRole.gql'

export default defineComponent(
    {
        name: 'NavBar',
        props: {
            hideRightNav: {
                type: Boolean,
                default: false
            },
            hideClubhouseNav: {
                type: Boolean,
                default: false
            }
        },
        components: {
            NotificationDropdown,
            UserAvatar,
            NavBarMenu
        },
        inject: ['clubhouseId', 'user'],
        data() {
            return {
                isMenuActive: false,
                isSwitching: false
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
            switchableRoles() {
                const roles = this.user?.roles ?? [];
                return roles.map(r => ({
                    id: r.id,
                    name: r.name,
                    label: roleLabels[r.name] ?? r.name,
                    isCurrent: r.id === this.user?.primaryRole?.id
                }));
            },
            showSwitcher() {
                return (this.user?.roles?.length ?? 0) > 1;
            },
            ...mapState([
                            'isAsideMobileExpanded',
                            'isNavBarVisible'
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
            async switchRole(roleId) {
                if (this.isSwitching) {
                    return;
                }
                this.isSwitching = true;
                try {
                    await this.$apollo.mutate({
                        mutation: SET_PRIMARY_ROLE,
                        variables: {roleId: String(roleId)}
                    });
                    this.$root.$emit('loggedIn');
                    this.$router.push({name: 'home', params: {clubhouseId: this.clubhouseId}})
                        .catch(() => {});
                    this.$buefy.snackbar.open({
                        message: 'Rolle skiftet',
                        type: 'is-success',
                        queue: false
                    });
                    this.isMenuActive = false;
                } catch (e) {
                    this.$buefy.snackbar.open({
                        message: 'Kunne ikke skifte rolle',
                        type: 'is-danger',
                        queue: false
                    });
                } finally {
                    this.isSwitching = false;
                }
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

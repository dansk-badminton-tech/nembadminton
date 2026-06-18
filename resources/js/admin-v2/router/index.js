import Vue from 'vue'
import VueRouter from 'vue-router'
import {isLoggedIn} from "../../auth";

Vue.use(VueRouter)

const routes = [
    {
        path: '/full-page',
        component: () => import(/* webpackChunkName: "full-page" */ '@/views/FullPageView.vue'),
        children: [
            {
                meta: {
                    title: 'Login'
                },
                path: '/login',
                name: 'login',
                component: () => import(/* webpackChunkName: "full-page" */ '@/views/full-page/LoginView.vue')
            },
            {
                meta: {
                    title: 'Glemt adgangskode'
                },
                path: '/forgot-password',
                name: 'forgot-password',
                component: () => import(/* webpackChunkName: "full-page" */ '@/views/full-page/ForgotPassword.vue')
            },
            {
                meta: {
                    title: 'Glemt adgangskode'
                },
                path: '/forgot-password-finish',
                name: 'forgot-password-finish',
                component: () => import(/* webpackChunkName: "full-page" */ '@/views/full-page/FinishForgotPassword.vue'),
                props: route => ({token: route.query.token, email: route.query.email})
            },
            {
                meta: {
                    title: 'Opret bruger'
                },
                path: '/sign-up',
                name: 'sign-up',
                component: () => import(/* webpackChunkName: "full-page" */ '@/views/full-page/CreateUser.vue'),
                props: route => ({invitationToken: route.query.invitationToken})
            },
            {
                meta: {
                    title: 'Klub tilknytning'
                },
                path: '/sign-up/finish',
                name: 'sign-up-finish',
                component: () => import(/* webpackChunkName: "full-page" */ '@/views/full-page/SignUpFinish.vue'),
                props: route => ({error: route.query.error})
            },
            {
                meta: {
                    title: 'Accepter invitation'
                },
                path: '/invitation/:token',
                name: 'invitation',
                component: () => import(/* webpackChunkName: "full-page" */ '@/views/full-page/UserInvitation.vue'),
                props: route => ({token: route.params.token})
            },
        ]
    },
    {
        path: '/c-:clubhouseId/player',
        component: () => import (/* webpackChunkName: "player" */ '@/views/PlayerApp.vue'),
        props: route => ({clubhouseId: route.params.clubhouseId}),
        children: [
            {
                path: '',
                name: 'player-home',
                component: () => import("../views/PlayerDashboard.vue"),
                meta: {
                    title: 'Spillerportal'
                }
            },
            {
                path: 'calendar-generator',
                name: 'calendar-generator-public-view',
                component: () => import("../views/calendar/CalendarGenerator.vue"),
                meta: {
                    title: 'Kalendar generator'
                }
            }
        ]
    },
    {
        path: '/full-width-page',
        component: () => import(/* webpackChunkName: "full-page" */ '@/views/FullWidthView.vue'),
        children: [
            {
                path: '/team-fight/:teamUUID/public-view',
                name: 'team-fight-public-view',
                component: () => import("../views/team-fight/TeamFightPublic.vue"),
                props: route => ({teamRoundId: route.params.teamUUID}),
                meta: {
                    title: 'Holdkamp'
                }
            },
            {
                path: '/cancellation/:sharingId/public-cancellation',
                name: 'cancellation-public-view',
                component: () => import("../views/cancellation/CancellationPublic.vue"),
                props: route => ({sharingId: route.params.sharingId}),
                meta: {
                    title: 'Afbud'
                }
            },
            {
                path: '/cancellation/:sharingId/public-cancellation/finish',
                name: 'cancellation-public-finish',
                component: () => import("../views/cancellation/CancellationPublicFinish.vue"),
                props: route => ({sharingId: route.params.sharingId}),
                meta: {
                    title: 'Afbud'
                }
            },
            {
                path: '/team-fight/check',
                name: 'team-fight-check',
                component: () => import("../views/check-team-fight/CheckTeamFight.vue"),
                meta: {
                    title: 'Tidligere Holdkamp'
                }
            }
        ]
    },{
        path: '/main-app',
        component: () => import(/* webpackChunkName: "main-app" */ '@/views/App.vue'),
        children: [
            {
                path: '/home-redirect',
                name: 'home-redirect',
                component: () => import('@/views/HomeView.vue')
            },
            {
                meta: {
                    title: 'Spiller statistik',
                    requiresAuth: true
                },
                path: '/player/:playerID/stats',
                name: 'player-stats',
                component: () => import('@/views/player/Stats.vue'),
                props: route => ({playerId: route.params.playerID})
            },
            {
                path: '/calendar',
                name: 'calendar',
                component: () => import("../views/calendar/Calendar.vue"),
                meta: {
                    title: 'Kalender',
                    requiresAuth: true
                }
            },
            {
                meta: {
                    title: 'Profile',
                    requiresAuth: true
                },
                path: '/profile',
                name: 'profile',
                component: () => import(/* webpackChunkName: "profile" */ '@/views/ProfileView.vue')
            },
            {
                meta: {
                    title: 'Afbud - landing',
                    requiresAuth: true
                },
                path: '/cancellations/landing',
                name: 'cancellation-landing',
                component: () => import('@/views/cancellation/CancellationLanding.vue')
            },
            {
                meta: {
                    title: 'Afbud',
                    requiresAuth: true
                },
                path: '/cancellations/redirect',
                name: 'cancellation-redirect',
                component: () => import('@/views/cancellation/CancellationRedirect.vue')
            },
            {
                meta: {
                    title: 'Afbud',
                    requiresAuth: true
                },
                path: '/cancellations/view/:collectorId',
                name: 'cancellation-view',
                component: () => import('@/views/cancellation/CancellationDashboard.vue'),
                props: route => ({collectorId: route.params.collectorId})
            },
            {
                meta: {
                    title: 'Opret afbudslink',
                    requiresAuth: true
                },
                path: '/cancellations/create',
                name: 'cancellation-create',
                component: () => import('@/views/cancellation/CreateCancellationCollector.vue')
            },
            {
                meta: {
                    title: 'Rediger afbudslink',
                    requiresAuth: true
                },
                path: '/cancellations/edit/:collectorId',
                name: 'cancellation-update',
                component: () => import('@/views/cancellation/UpdateCancellationCollector.vue'),
                props: route => ({collectorId: route.params.collectorId})
            },
            {
                path: '/superadmin/notification',
                name: 'superadmin-notification',
                component: () => import('../views/superadmin/Notification.vue'),
                meta: {
                    title: 'Send Notification',
                    requiresAuth: true
                },
            },
            {
                path: '/faq',
                meta: {
                    title: 'FAQ'
                },
                name: 'faq',
                component: () => import('@/views/faq/Faq.vue')
            },
            {
                path: '/about-us',
                meta: {
                    title: 'Om os'
                },
                name: 'about-us',
                component: () => import('@/views/about/About.vue')
            },
            {
                path: '/onboarding',
                name: 'onboarding',
                component: () => import('../views/onboarding/Onboarding.vue'),
                meta: {
                    requiresAuth: true,
                    title: 'Klargør din klub'
                }
            },
            {
                path: '/c-:clubhouseId',
                component: () => import('@/views/tenant/Default.vue'),
                props: route => ({clubhouseId: route.params.clubhouseId}),
                children: [
                    {
                        meta: {
                            title: 'Dashboard',
                            requiresAuth: true
                        },
                        path: 'home',
                        name: 'home',
                        component: () => import('@/views/dashboard/ClubDashboard.vue')
                    },
                    {
                        meta: {
                            title: 'Holdrunder',
                            requiresAuth: true
                        },
                        path: 'team-fight/dashboard',
                        name: 'team-fight-dashboard',
                        component: () => import("../views/team-fight/TeamFightList.vue")
                    },
                    {
                        meta: {
                            title: 'Klubhus',
                            requiresAuth: true
                        },
                        path: 'club-house',
                        name: 'my-clubhouse',
                        component: () => import('@/views/club-house/ClubHouseDashboard.vue')
                    },
                    {
                        meta: {
                            title: 'Rediger holdrunde',
                            requiresAuth: true
                        },
                        path: 'team-fight/:teamUUID/edit',
                        name: 'team-fight-edit',
                        component: () => import("../views/team-fight/TeamFight.vue"),
                        props: route => ({teamRoundId: route.params.teamUUID})
                    },
                    {
                        meta: {
                            title: 'E-mail notifikationer',
                            requiresAuth: true
                        },
                        path: 'team-fight/:teamUUID/notify',
                        name: 'team-fight-notify',
                        component: () => import("../views/team-fight/TeamFightNotify.vue"),
                        props: route => ({teamRoundId: route.params.teamUUID})
                    },
                    {
                        meta: {
                            title: 'Opret Holdrunde',
                            requiresAuth: true
                        },
                        path: 'team-fight/create',
                        name: 'team-fight-create',
                        component: () => import("../views/team-fight/TeamFightCreate.vue")
                    },
                    {
                        meta: {
                            title: 'Analytics',
                            requiresAuth: true
                        },
                        path: 'analytics',
                        name: 'analytics',
                        component: () => import('@/views/analytics/AnalyticDashboard.vue')
                    },
                    {
                        meta: {
                            title: 'Spillere',
                            requiresAuth: true
                        },
                        path: 'members',
                        name: 'members',
                        component: () => import('@/views/members/MemberManagement.vue')
                    },
                    {
                        meta: {
                            title: 'Hold',
                            requiresAuth: true
                        },
                        path: 'teams',
                        name: 'teams',
                        component: () => import('@/views/team/TeamList.vue'),
                        props: route => ({clubhouseId: route.params.clubhouseId})
                    }
                ]
            },
            {
                path: '/:pathMatch(.*)*',
                component: () => import('@/views/PageNotFound.vue')
            }
        ]
    },
]

const router = new VueRouter({
                                 mode: 'history',
                                 base: '/app',
                                 routes,
                                 scrollBehavior(to, from, savedPosition) {
                                     if (savedPosition) {
                                         return savedPosition
                                     } else if (to.hash) {
                                         return {
                                             selector: to.hash,
                                             behavior: 'smooth'
                                         }
                                     } else {
                                         return {x: 0, y: 0}
                                     }
                                 }
                             })

// Recover from "Failed to fetch dynamically imported module" / ChunkLoadError
// that happens when a deploy replaces hashed asset filenames while a user has
// the SPA open. Reload once so the new bundle is picked up. The sessionStorage
// flag prevents an infinite loop if the new build is also broken.
const CHUNK_RELOAD_FLAG = 'chunk-reload-attempted'

router.onError((error) => {
    const message = error?.message || ''
    const isChunkError =
        error?.name === 'ChunkLoadError' ||
        /Loading chunk [\w-]+ failed/i.test(message) ||
        /Failed to fetch dynamically imported module/i.test(message) ||
        /Importing a module script failed/i.test(message) ||
        /error loading dynamically imported module/i.test(message)

    if (!isChunkError) return

    if (sessionStorage.getItem(CHUNK_RELOAD_FLAG)) {
        // We already tried reloading — don't loop. Let Sentry capture it.
        return
    }
    sessionStorage.setItem(CHUNK_RELOAD_FLAG, '1')
    window.location.reload()
})

router.afterEach(() => {
    // Successful navigation means the current bundle is healthy.
    // Clear the reload guard so future chunk failures can trigger a reload again.
    sessionStorage.removeItem(CHUNK_RELOAD_FLAG)
})

router.beforeEach((to, from, next) => {
    const loggedIn = isLoggedIn();
    const requiresAuth = to.meta.requiresAuth;

    if (to.path === '/') {
        return next({path: '/home-redirect'})
    }

    if (requiresAuth && !loggedIn) {
        return next({
                 path: '/login',
                 query: {redirect: to.fullPath},
             })
    }
    next()
})

export default router

export function useRouter() {
    return router
}

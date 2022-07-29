import VueRouter from 'vue-router'
import {isLoggedIn} from "./auth";
import Vue from "vue";

const router = new VueRouter(
    {
        mode: 'history',
        routes: [
            {
                path: '/home',
                name: 'home',
                component: () => import("./views/Home"),
                meta: {
                    allowAnonymous: true,
                    title: 'Forside'
                }
            },
            {
                path: "/",
                redirect: "/home",
                component: () => import("./views/Base"),
                children: [
                    {
                        path: '/my-profile',
                        name: 'my-profile',
                        component: () => import("./views/MyProfile"),
                        meta: {
                            title: 'Min profil'
                        }
                    },
                    {
                        path: '/rounds-generator',
                        name: 'rounds-generator',
                        component: () => import("./views/RoundsGenerator")
                    },
                    {
                        path: '/team-fight/dashboard',
                        name: 'team-fight-dashboard',
                        component: () => import("./views/TeamFightList"),
                        meta: {
                            title: 'Holdkampe'
                        }
                    },
                    {
                        path: '/team-fight/:teamUUID/edit',
                        name: 'team-fight-edit',
                        component: () => import("./views/TeamFight"),
                        props: route => ({teamFightId: route.params.teamUUID}),
                        meta: {
                            title: 'Rediger holdkamp'
                        }
                    },
                    {
                        path: '/team-fight/:teamUUID/import',
                        name: 'team-fight-import',
                        component: () => import("./views/TeamFightImport"),
                        props: route => ({teamFightId: route.params.teamUUID}),
                        meta: {
                            title: 'Importer fra badmintonplayer.dk'
                        }
                    },
                    {
                        path: '/team-fight/create',
                        name: 'team-fight-create',
                        component: () => import("./views/TeamFightCreate"),
                        meta: {
                            title: 'Opret holdkamp'
                        }
                    },
                    {
                        path: '/new-user',
                        name: 'new-user-create',
                        component: () => import("./views/CreateUser"),
                        meta: {
                            allowAnonymous: true,
                            title: 'Ny bruger'
                        }
                    },
                    {
                        path: '/login',
                        name: 'login',
                        component: () => import("./views/Login"),
                        meta: {
                            allowAnonymous: true,
                            title: 'Login'
                        }
                    },
                    {
                        path: '/team-fight/:teamUUID/public-view',
                        name: 'team-fight-public-view',
                        component: () => import("./views/TeamFightPublic"),
                        props: route => ({teamId: route.params.teamUUID}),
                        meta: {
                            allowAnonymous: true,
                            title: 'Holdkamp'
                        }
                    },
                    {
                        path: '/player-stats/:badmintonId',
                        name: 'player-stats',
                        component: () => import("./views/PlayerStats"),
                        props: route => ({badmintonId: route.params.badmintonId}),
                        meta: {
                            allowAnonymous: false,
                            title: 'Spiller stats'
                        }
                    },
                    {
                        path: '/my-club',
                        name: 'my-club',
                        component: () => import("./views/ClubDashboard"),
                        meta: {
                            allowAnonymous: false,
                            title: 'Min klub'
                        }
                    },
                    {
                        path: '/team-fight/check',
                        name: 'check-team-fight',
                        component: () => import("./views/check-team-fight/CheckTeamFight"),
                        meta: {
                            allowAnonymous: true,
                            title: 'Tidligere holdkampe'
                        }
                    },
                    {
                        path: '/team-fight-v2/check',
                        name: 'check-team-fight-v2',
                        component: () => import("./views/check-team-fight-v2/CheckTeamFight"),
                        meta: {
                            allowAnonymous: true,
                            title: 'Tidligere holdkampe'
                        }
                    },
                    {
                        path: '/team-fight/choice',
                        name: 'check-team-choice',
                        component: () => import("./views/check-team-fight-choice/TeamFightChoice"),
                        meta: {
                            allowAnonymous: true,
                            title: 'Vælge version'
                        }
                    },
                    {
                        path: '/onboarding',
                        name: 'onboarding',
                        component: () => import('./views/onboarding/Onboarding'),
                        meta: {
                            allowAnonymous: false,
                            title: 'Klargør din klub'
                        }
                    },
                    {
                        path: '/playground',
                        name: 'playground',
                        component: () => import("./views/playground/Playground"),
                        meta: {
                            allowAnonymous: true,
                            title: 'Playground'
                        }
                    }
                ]
            }
        ]
    })

const DEFAULT_TITLE = '...';
router.afterEach((to, from) => {
    // Use next tick to handle router history correctly
    // see: https://github.com/vuejs/vue-router/issues/914#issuecomment-384477609
    Vue.nextTick(() => {
        document.title = to.meta.title || DEFAULT_TITLE;
    });
});

router.beforeEach((to, from, next) => {
    if (!to.meta.allowAnonymous && !isLoggedIn()) {
        next({
                 path: '/login',
                 query: {redirect: to.fullPath}
             })
    } else {
        next()
    }
});

export default router;

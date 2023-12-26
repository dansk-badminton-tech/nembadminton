import Vue from 'vue'
import VueRouter from 'vue-router'
import {isLoggedIn} from "../../auth";

Vue.use(VueRouter)

const routes = [
    {
        meta: {
            title: 'Dashboard',
            requiresAuth: true
        },
        path: '/home',
        name: 'home',
        component: () => import('@/views/dashboard/ClubDashboard.vue')
    },
    {
        meta: {
            title: 'Holdrunde',
            requiresAuth: true
        },
        path: '/team-fight/dashboard',
        name: 'team-fight-dashboard',
        component: () => import("../views/team-fight/TeamFightList.vue")
    },
    {
        meta: {
            title: 'Opret Holdkamp',
            requiresAuth: true
        },
        path: '/team-fight/create',
        name: 'team-fight-create',
        component: () => import("../views/team-fight/TeamFightCreate.vue")
    },
    {
        meta: {
            title: 'Rediger holdkamp',
            requiresAuth: true
        },
        path: '/team-fight/:teamUUID/edit',
        name: 'team-fight-edit',
        component: () => import("../views/team-fight/TeamFight.vue"),
        props: route => ({teamFightId: route.params.teamUUID})
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
            title: 'Mine klubber',
            requiresAuth: true
        },
        path: '/my-clubs',
        name: 'my-clubs',
        component: () => import(/* webpackChunkName: "profile" */ '@/views/my-club/MyClubs.vue')
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
                props: route => ({ token: route.query.token, email: route.query.email })
            },
            {
                meta: {
                    title: 'Opret bruger'
                },
                path: '/sign-up',
                name: 'sign-up',
                component: () => import(/* webpackChunkName: "full-page" */ '@/views/full-page/CreateUser.vue')
            },
        ]
    },
    {
        path: '/full-width-page',
        component: () => import(/* webpackChunkName: "full-page" */ '@/views/FullWidthView.vue'),
        children: [
            {
                path: '/team-fight/:teamUUID/public-view',
                name: 'team-fight-public-view',
                component: () => import("../views/team-fight/TeamFightPublic"),
                props: route => ({teamId: route.params.teamUUID}),
                meta: {
                    title: 'Holdkamp'
                }
            },
            {
                path: '/calendar-generator/:teamUUID',
                name: 'calendar-generator-public-view',
                component: () => import("../views/calendar/CalendarGenerator.vue"),
                props: route => ({teamId: route.params.teamUUID}),
                meta: {
                    title: 'Holdkamp'
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
        component: () => import('../views/onboarding/Onboarding'),
        meta: {
            requiresAuth: true,
            title: 'Klargør din klub'
        }
    },
    //    {
//        meta: {
//            title: 'Tables'
//        },
//        path: '/tables',
//        name: 'tables',
//        // route level code-splitting
//        // this generates a separate chunk (about.[hash].js) for this route
//        // which is lazy-loaded when the route is visited.
//        component: () => import(/* webpackChunkName: "tables" */ '@/views/TablesView.vue')
//    },
//    {
//        meta: {
//            title: 'Forms'
//        },
//        path: '/forms',
//        name: 'forms',
//        component: () => import(/* webpackChunkName: "forms" */ '@/views/FormsView.vue')
//    },
//    {
//        meta: {
//            title: 'New Client'
//        },
//        path: '/client/new',
//        name: 'client.new',
//        component: () => import(/* webpackChunkName: "client-form" */ '@/views/ClientFormView.vue')
//    },
//    {
//        meta: {
//            title: 'Edit Client'
//        },
//        path: '/client/:id',
//        name: 'client.edit',
//        component: () => import(/* webpackChunkName: "client-form" */ '@/views/ClientFormView.vue'),
//        props: true
//    },
]

const router = new VueRouter({
                                 mode: 'history',
    base: '/app',
                                 routes,
                                 scrollBehavior(to, from, savedPosition) {
                                     if (savedPosition) {
                                         return savedPosition
                                     } else {
                                         return {x: 0, y: 0}
                                     }
                                 }
                             })

router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !isLoggedIn()) {
        next({
            path: '/login',
            query: { redirect: to.fullPath },
        })
    }else{
        next()
    }
})

export default router

export function useRouter() {
    return router
}

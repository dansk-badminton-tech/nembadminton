import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const routes = [
    {
        // Document title tag
        // We combine it with defaultDocumentTitle set in `src/main.js` on router.afterEach hook
        meta: {
            title: 'Dashboard'
        },
        path: '/',
        name: 'home',
        component: () => import('@/views/dashboard/ClubDashboard.vue')
    },
    {
        // Document title tag
        // We combine it with defaultDocumentTitle set in `src/main.js` on router.afterEach hook
        meta: {
            title: 'Holdkamp'
        },
        path: '/team-fight/dashboard',
        name: 'team-fight-dashboard',
        component: () => import("../views/team-fight/TeamFightList.vue")
    },
    {
        // Document title tag
        // We combine it with defaultDocumentTitle set in `src/main.js` on router.afterEach hook
        meta: {
            title: 'Opret Holdkamp'
        },
        path: '/team-fight/create',
        name: 'team-fight-create',
        component: () => import("../views/team-fight/TeamFightCreate.vue")
    },
    {
        meta: {
            title: 'Rediger holdkamp'
        },
        path: '/team-fight/:teamUUID/edit',
        name: 'team-fight-edit',
        component: () => import("../views/team-fight/TeamFight.vue"),
        props: route => ({teamFightId: route.params.teamUUID})
    },
    {
        meta: {
            title: 'Tables'
        },
        path: '/tables',
        name: 'tables',
        // route level code-splitting
        // this generates a separate chunk (about.[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        component: () => import(/* webpackChunkName: "tables" */ '@/views/TablesView.vue')
    },
    {
        meta: {
            title: 'Forms'
        },
        path: '/forms',
        name: 'forms',
        component: () => import(/* webpackChunkName: "forms" */ '@/views/FormsView.vue')
    },
    {
        meta: {
            title: 'Profile'
        },
        path: '/profile',
        name: 'profile',
        component: () => import(/* webpackChunkName: "profile" */ '@/views/ProfileView.vue')
    },
    {
        meta: {
            title: 'Profile'
        },
        path: '/my-clubs',
        name: 'my-clubs',
        component: () => import(/* webpackChunkName: "profile" */ '@/views/my-club/MyClubs.vue')
    },
    {
        meta: {
            title: 'New Client'
        },
        path: '/client/new',
        name: 'client.new',
        component: () => import(/* webpackChunkName: "client-form" */ '@/views/ClientFormView.vue')
    },
    {
        meta: {
            title: 'Edit Client'
        },
        path: '/client/:id',
        name: 'client.edit',
        component: () => import(/* webpackChunkName: "client-form" */ '@/views/ClientFormView.vue'),
        props: true
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
    }
]

const router = new VueRouter({
                                 mode: 'history',
                                 routes,
                                 scrollBehavior(to, from, savedPosition) {
                                     if (savedPosition) {
                                         return savedPosition
                                     } else {
                                         return {x: 0, y: 0}
                                     }
                                 }
                             })

export default router

export function useRouter() {
    return router
}

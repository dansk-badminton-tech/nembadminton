import VueRouter from 'vue-router'
import {isLoggedIn} from "./auth";

const router = new VueRouter(
    {
        mode: 'history',
        routes: [
            {
                path: '/home',
                name: 'home',
                component: () => import("./views/Home"),
                meta: {
                    allowAnonymous: true
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
                        component: () => import("./views/MyProfile")
                    },
                    {
                        path: '/rounds-generator',
                        name: 'rounds-generator',
                        component: () => import("./views/RoundsGenerator")
                    },
                    {
                        path: '/team-fight/dashboard',
                        name: 'team-fight-dashboard',
                        component: () => import("./views/TeamFightList")
                    },
                    {
                        path: '/team-fight/:teamUUID/edit',
                        name: 'team-fight-edit',
                        component: () => import("./views/TeamFight"),
                        props: route => ({teamFightId: route.params.teamUUID})
                    },
                    {
                        path: '/team-fight/create',
                        name: 'team-fight-create',
                        component: () => import("./views/TeamFightCreate")
                    },
                    {
                        path: '/new-user',
                        name: 'new-user-create',
                        component: () => import("./views/CreateUser"),
                        meta: {
                            allowAnonymous: true
                        }
                    },
                    {
                        path: '/login',
                        name: 'login',
                        component: () => import("./views/Login"),
                        meta: {
                            allowAnonymous: true
                        }
                    }
                ]
            },
            {
                path: '/team-fight/:teamUUID/view',
                name: 'team-fight-view',
                component: () => import("./views/TeamFightPublic"),
                props: route => ({teamFightId: route.params.teamUUID}),
                meta: {
                    allowAnonymous: true
                }
            }
        ]
    })

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

import VueRouter from 'vue-router'
import RoundsGenerator from "./views/RoundsGenerator";
import TeamFight from "./views/TeamFight";
import TeamFightReadOnly from "./views/TeamFightReadOnly";
import TeamFightList from "./views/TeamFightList";
import CreateUser from "./views/CreateUser";
import Login from "./views/Login";
import Home from "./views/Home";
import MyProfile from "./views/MyProfile";

const router = new VueRouter({
                                 mode: 'history',
                                 routes: [
                                     {
                                         path: '/',
                                         name: 'home',
                                         component: Home
                                     },
                                     {
                                         path: '/my-profile',
                                         name: 'my-profile',
                                         component: MyProfile
                                     },
                                     {
                                         path: '/rounds-generator',
                                         name: 'rounds-generator',
                                         component: RoundsGenerator
                                     },
                                     {
                                         path: '/team-fight/dashboard',
                                         name: 'team-fight-dashboard',
                                         component: TeamFightList
                                     },
                                     {
                                         path: '/team-fight/:teamUUID/edit',
                                         name: 'team-fight-edit',
                                         component: TeamFight,
                                         props: route => ({teamFightId: route.params.teamUUID})
                                     },
                                     {
                                         path: '/team-fight/create',
                                         name: 'team-fight-create',
                                         component: TeamFight,
                                         props: {createMode: true}
                                     },
                                     {
                                         path: '/team-fight/:teamUUID/view',
                                         name: 'team-fight-view',
                                         component: TeamFightReadOnly
                                     },
                                     {
                                         path: '/new-user',
                                         name: 'new-user-create',
                                         component: CreateUser
                                     },
                                     {
                                         path: '/login',
                                         name: 'login',
                                         component: Login
                                     },
                                 ],
                             });
export default router;

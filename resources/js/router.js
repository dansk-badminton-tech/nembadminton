import VueRouter from 'vue-router'
import Home from "./views/Home";
import RoundsGenerator from "./views/RoundsGenerator";
import TeamFight from "./views/TeamFight";

const router = new VueRouter({
                                 mode: 'history',
                                 routes: [
                                     {
                                         path: '/',
                                         name: 'home',
                                         component: Home
                                     },
                                     {
                                         path: '/rounds-generator',
                                         name: 'rounds-generator',
                                         component: RoundsGenerator
                                     },
                                     {
                                         path: '/team-fight',
                                         name: 'team-fight',
                                         component: TeamFight
                                     }
                                 ],
                             });
export default router;

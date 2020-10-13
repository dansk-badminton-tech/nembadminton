import VueRouter from 'vue-router'
import RoundsGenerator from "./views/RoundsGenerator";
import TeamFight from "./views/TeamFight";
import TeamFightView from "./views/TeamFightView";
import TeamFightRecent from "./views/TeamFightRecent";

const router = new VueRouter({
                                 mode: 'history',
                                 routes: [
                                     {
                                         path: '/rounds-generator',
                                         name: 'rounds-generator',
                                         component: RoundsGenerator
                                     },
                                     {
                                         path: '/team-fight/recent',
                                         name: 'team-fight-recent',
                                         component: TeamFightRecent,
                                         props: {createMode: true}
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
                                         component: TeamFightView
                                     }
                                 ],
                             });
export default router;

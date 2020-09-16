import VueRouter from 'vue-router'
import Home from "./views/Home";
import RoundsGenerator from "./views/RoundsGenerator";

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
                                     }
                                 ],
                             });
export default router;

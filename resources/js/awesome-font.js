import Buefy from 'buefy'
import Vue from 'vue'
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faCheck,
    faAngleUp,
    faAngleDown,
    faUsers,
    faSave,
    faBrain,
    faArrowsAlt,
    faTrashAlt
} from "@fortawesome/free-solid-svg-icons";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

library.add(faCheck, faAngleUp, faAngleDown, faUsers, faSave, faBrain, faArrowsAlt, faTrashAlt);
Vue.component('vue-fontawesome', FontAwesomeIcon);
Vue.use(Buefy, {
    defaultIconComponent: 'vue-fontawesome',
    defaultIconPack: 'fas',
    customIconPacks: {
        fas: {
            sizes: {
                default: "lg",
                "is-small": "1x",
                "is-medium": "2x",
                "is-large": "3x"
            },
            iconPrefix: ""
        }
    }
});

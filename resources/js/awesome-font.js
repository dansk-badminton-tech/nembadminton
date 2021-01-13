import Buefy from 'buefy'
import Vue from 'vue'
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faCheck,
    faHome,
    faAngleUp,
    faAngleDown,
    faHandRock,
    faUsers,
    faUserAlt,
    faSave,
    faBrain,
    faArrowUp,
    faArrowsAlt,
    faTrashAlt,
    faTimesCircle,
    faExclamationCircle,
    faSignOutAlt,
    faTrash,
    faCalendarAlt,
    faAngleLeft,
    faAngleRight,
    faEye,
    faEllipsisV,
    faShareAlt,
    faBars
} from "@fortawesome/free-solid-svg-icons";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";

library.add(faBars, faShareAlt, faEllipsisV, faEye, faCheck, faAngleUp, faAngleDown, faHandRock, faCalendarAlt, faHome, faAngleLeft, faAngleRight, faTrash, faUsers, faUserAlt, faSave, faBrain, faArrowsAlt, faTrashAlt, faArrowUp, faTimesCircle, faExclamationCircle, faSignOutAlt);
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

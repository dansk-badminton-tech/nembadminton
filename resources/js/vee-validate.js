import {extend, localize} from "vee-validate";
import {required} from "vee-validate/dist/rules";
import da from 'vee-validate/dist/locale/da.json';

localize('da',da);

extend("required", {
    ...required
});

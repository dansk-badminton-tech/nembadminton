(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[4],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                query {\n                 clubs{\n                    id\n                    name1\n                  }\n                }\n               "]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'BadmintonPlayerClubs',
  props: ['value'],
  methods: {
    handleInput: function handleInput(value) {
      this.$emit('input', value);
    }
  },
  apollo: {
    clubs: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject())
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/CreateUser.vue?vue&type=script&lang=js&":
/*!****************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/CreateUser.vue?vue&type=script&lang=js& ***!
  \****************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _helpers__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helpers */ "./resources/js/helpers.js");
/* harmony import */ var _auth__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../auth */ "./resources/js/auth.js");
/* harmony import */ var _components_search_club_ClubSearch__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../components/search-club/ClubSearch */ "./resources/js/components/search-club/ClubSearch.vue");
/* harmony import */ var _components_badminton_player_BadmintonPlayerClubs__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../components/badminton-player/BadmintonPlayerClubs */ "./resources/js/components/badminton-player/BadmintonPlayerClubs.vue");
function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                        mutation ($input: RegisterInput!){\n                          register(input: $input){\n                            status\n                            tokens{access_token}\n                          }\n                        }\n                    "]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//





/* harmony default export */ __webpack_exports__["default"] = ({
  name: "CreateUser",
  components: {
    BadmintonPlayerClubs: _components_badminton_player_BadmintonPlayerClubs__WEBPACK_IMPORTED_MODULE_4__["default"]
  },
  props: {
    afterRegister: Function
  },
  data: function data() {
    return {
      name: null,
      email: null,
      password: null,
      password_confirmation: null,
      clubId: null,
      loading: false,
      playerId: null
    };
  },
  methods: {
    create: function create() {
      var _this = this;

      this.loading = true;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject()),
        variables: {
          input: {
            name: this.name,
            email: this.email,
            organization_id: this.clubId,
            player_id: this.playerId,
            password: this.password,
            password_confirmation: this.password_confirmation
          }
        }
      }).then(function (_ref) {
        var data = _ref.data;
        Object(_auth__WEBPACK_IMPORTED_MODULE_2__["setAuthToken"])(data.register.tokens.access_token);

        _this.$root.$emit('loggedIn');

        if (_this.afterRegister instanceof Function) {
          _this.afterRegister();
        } else {
          _this.$router.push({
            name: 'my-club'
          });
        }
      })["catch"](function (_ref2) {
        var graphQLErrors = _ref2.graphQLErrors;
        var errors = Object(_helpers__WEBPACK_IMPORTED_MODULE_1__["extractErrors"])(graphQLErrors);

        _this.$buefy.snackbar.open({
          duration: 6000,
          type: 'is-danger',
          message: "Kunne ikke oprette bruger. <br />" + errors.join('<br />')
        });
      })["finally"](function () {
        _this.loading = false;
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=template&id=e0d0687c&":
/*!****************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=template&id=e0d0687c& ***!
  \****************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "b-select",
    {
      attrs: {
        loading: _vm.$apollo.queries.clubs.loading,
        expanded: "",
        placeholder: "Vælge klub"
      },
      on: { input: _vm.handleInput }
    },
    _vm._l(_vm.clubs, function(option) {
      return _c("option", { key: option.id, domProps: { value: option.id } }, [
        _vm._v("\n        " + _vm._s(option.name1) + "\n    ")
      ])
    }),
    0
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/CreateUser.vue?vue&type=template&id=4d2515c6&scoped=true&":
/*!********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/CreateUser.vue?vue&type=template&id=4d2515c6&scoped=true& ***!
  \********************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c(
    "div",
    { staticClass: "mb-2" },
    [
      _c(
        "b-field",
        { attrs: { label: "Navn" } },
        [
          _c("b-input", {
            attrs: { icon: "user-alt", placeholder: "Viktor Axelsen" },
            model: {
              value: _vm.name,
              callback: function($$v) {
                _vm.name = $$v
              },
              expression: "name"
            }
          })
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "b-field",
        { attrs: { label: "Email" } },
        [
          _c("b-input", {
            attrs: {
              icon: "envelope",
              placeholder: "viktor@gmail.com",
              type: "email"
            },
            model: {
              value: _vm.email,
              callback: function($$v) {
                _vm.email = $$v
              },
              expression: "email"
            }
          })
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "b-field",
        { attrs: { label: "Badminton Player Klub" } },
        [
          _c("BadmintonPlayerClubs", {
            model: {
              value: _vm.clubId,
              callback: function($$v) {
                _vm.clubId = $$v
              },
              expression: "clubId"
            }
          })
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "b-field",
        { attrs: { label: "Badminton Player ID (Valgfrit)" } },
        [
          _c("b-input", {
            attrs: { icon: "user-alt", placeholder: "900910-17", type: "text" },
            model: {
              value: _vm.playerId,
              callback: function($$v) {
                _vm.playerId = $$v
              },
              expression: "playerId"
            }
          })
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "a",
        {
          staticClass: "is-clearfix",
          attrs: {
            href: "https://www.badmintonplayer.dk/DBF/Ranglister/",
            target: "_blank"
          }
        },
        [_vm._v("Find dit Badminton ID på ranglisten")]
      ),
      _vm._v(" "),
      _c(
        "b-field",
        { attrs: { label: "Adgangskode" } },
        [
          _c("b-input", {
            attrs: { icon: "lock", placeholder: "******", type: "password" },
            model: {
              value: _vm.password,
              callback: function($$v) {
                _vm.password = $$v
              },
              expression: "password"
            }
          })
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "b-field",
        { attrs: { label: "Gentag adgangskode" } },
        [
          _c("b-input", {
            attrs: { icon: "lock", placeholder: "******", type: "password" },
            model: {
              value: _vm.password_confirmation,
              callback: function($$v) {
                _vm.password_confirmation = $$v
              },
              expression: "password_confirmation"
            }
          })
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "b-button",
        { attrs: { loading: _vm.loading }, on: { click: _vm.create } },
        [_vm._v("Opret")]
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/components/badminton-player/BadmintonPlayerClubs.vue":
/*!***************************************************************************!*\
  !*** ./resources/js/components/badminton-player/BadmintonPlayerClubs.vue ***!
  \***************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BadmintonPlayerClubs_vue_vue_type_template_id_e0d0687c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BadmintonPlayerClubs.vue?vue&type=template&id=e0d0687c& */ "./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=template&id=e0d0687c&");
/* harmony import */ var _BadmintonPlayerClubs_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./BadmintonPlayerClubs.vue?vue&type=script&lang=js& */ "./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _BadmintonPlayerClubs_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _BadmintonPlayerClubs_vue_vue_type_template_id_e0d0687c___WEBPACK_IMPORTED_MODULE_0__["render"],
  _BadmintonPlayerClubs_vue_vue_type_template_id_e0d0687c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/badminton-player/BadmintonPlayerClubs.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=script&lang=js&":
/*!****************************************************************************************************!*\
  !*** ./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=script&lang=js& ***!
  \****************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerClubs_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./BadmintonPlayerClubs.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerClubs_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=template&id=e0d0687c&":
/*!**********************************************************************************************************!*\
  !*** ./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=template&id=e0d0687c& ***!
  \**********************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerClubs_vue_vue_type_template_id_e0d0687c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./BadmintonPlayerClubs.vue?vue&type=template&id=e0d0687c& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/badminton-player/BadmintonPlayerClubs.vue?vue&type=template&id=e0d0687c&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerClubs_vue_vue_type_template_id_e0d0687c___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerClubs_vue_vue_type_template_id_e0d0687c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/views/CreateUser.vue":
/*!*******************************************!*\
  !*** ./resources/js/views/CreateUser.vue ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _CreateUser_vue_vue_type_template_id_4d2515c6_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CreateUser.vue?vue&type=template&id=4d2515c6&scoped=true& */ "./resources/js/views/CreateUser.vue?vue&type=template&id=4d2515c6&scoped=true&");
/* harmony import */ var _CreateUser_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CreateUser.vue?vue&type=script&lang=js& */ "./resources/js/views/CreateUser.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _CreateUser_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _CreateUser_vue_vue_type_template_id_4d2515c6_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _CreateUser_vue_vue_type_template_id_4d2515c6_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "4d2515c6",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/CreateUser.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/CreateUser.vue?vue&type=script&lang=js&":
/*!********************************************************************!*\
  !*** ./resources/js/views/CreateUser.vue?vue&type=script&lang=js& ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateUser_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./CreateUser.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/CreateUser.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateUser_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/CreateUser.vue?vue&type=template&id=4d2515c6&scoped=true&":
/*!**************************************************************************************!*\
  !*** ./resources/js/views/CreateUser.vue?vue&type=template&id=4d2515c6&scoped=true& ***!
  \**************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateUser_vue_vue_type_template_id_4d2515c6_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./CreateUser.vue?vue&type=template&id=4d2515c6&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/CreateUser.vue?vue&type=template&id=4d2515c6&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateUser_vue_vue_type_template_id_4d2515c6_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateUser_vue_vue_type_template_id_4d2515c6_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);
//# sourceMappingURL=4.js.map
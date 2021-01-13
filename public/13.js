(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[13],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/MyProfile.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/MyProfile.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
function _templateObject3() {
  var data = _taggedTemplateLiteral(["\n                        mutation updateMe($input: UpdateMe!){\n                            updateMe(input: $input){\n                                id\n                                name\n                                email\n                            }\n                        }\n                    "]);

  _templateObject3 = function _templateObject3() {
    return data;
  };

  return data;
}

function _templateObject2() {
  var data = _taggedTemplateLiteral(["\n                        mutation updatePassword($input: UpdatePassword!){\n                            updatePassword(input: $input){\n                                status\n                                message\n                            }\n                        }\n                    "]);

  _templateObject2 = function _templateObject2() {
    return data;
  };

  return data;
}

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                query{\n                    me{\n                        id\n                        email\n                        name\n                    }\n                }"]);

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

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "MyProfile",
  data: function data() {
    return {
      me: {
        name: '',
        email: ''
      },
      password: '',
      password_confirmation: '',
      old_password: '',
      updatingPassword: false,
      updatingProfile: false
    };
  },
  apollo: {
    me: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject()),
      fetchPolicy: "network-only"
    }
  },
  methods: {
    updatePassword: function updatePassword() {
      var _this = this;

      this.updatingPassword = true;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject2()),
        variables: {
          input: {
            old_password: this.old_password,
            password: this.password,
            password_confirmation: this.password_confirmation
          }
        }
      }).then(function () {
        _this.$buefy.snackbar.open({
          duration: 2000,
          type: 'is-success',
          message: "Din adgangskode er nu opdateret"
        });
      })["finally"](function () {
        _this.updatingPassword = false;
      });
    },
    update: function update() {
      var _this2 = this;

      this.updatingProfile = true;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject3()),
        variables: {
          input: {
            name: this.me.name,
            email: this.me.email
          }
        }
      }).then(function () {
        _this2.$root.$emit('userUpdated');

        _this2.$buefy.snackbar.open({
          duration: 2000,
          type: 'is-success',
          message: "Din profil er nu opdateret"
        });
      })["finally"](function () {
        _this2.updatingProfile = false;
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/MyProfile.vue?vue&type=template&id=8e9c9464&scoped=true&":
/*!*******************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/MyProfile.vue?vue&type=template&id=8e9c9464&scoped=true& ***!
  \*******************************************************************************************************************************************************************************************************************/
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
    [
      _c(
        "b-field",
        { attrs: { label: "Navn" } },
        [
          _c("b-input", {
            model: {
              value: _vm.me.name,
              callback: function($$v) {
                _vm.$set(_vm.me, "name", $$v)
              },
              expression: "me.name"
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
            attrs: { type: "email" },
            model: {
              value: _vm.me.email,
              callback: function($$v) {
                _vm.$set(_vm.me, "email", $$v)
              },
              expression: "me.email"
            }
          })
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "b-button",
        { attrs: { loading: _vm.updatingProfile }, on: { click: _vm.update } },
        [_vm._v("Gem")]
      ),
      _vm._v(" "),
      _c("div", { staticClass: "mt-4" }),
      _vm._v(" "),
      _c(
        "b-field",
        { attrs: { label: "Nuværende Adgangskode" } },
        [
          _c("b-input", {
            attrs: { type: "password" },
            model: {
              value: _vm.old_password,
              callback: function($$v) {
                _vm.old_password = $$v
              },
              expression: "old_password"
            }
          })
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "b-field",
        { attrs: { label: "Ny Adgangskode" } },
        [
          _c("b-input", {
            attrs: { type: "password" },
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
        { attrs: { label: "Gentag ny adgangskode" } },
        [
          _c("b-input", {
            attrs: { type: "password" },
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
        {
          attrs: { loading: _vm.updatingPassword },
          on: { click: _vm.updatePassword }
        },
        [_vm._v("Skift adgangskode")]
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/views/MyProfile.vue":
/*!******************************************!*\
  !*** ./resources/js/views/MyProfile.vue ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _MyProfile_vue_vue_type_template_id_8e9c9464_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./MyProfile.vue?vue&type=template&id=8e9c9464&scoped=true& */ "./resources/js/views/MyProfile.vue?vue&type=template&id=8e9c9464&scoped=true&");
/* harmony import */ var _MyProfile_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./MyProfile.vue?vue&type=script&lang=js& */ "./resources/js/views/MyProfile.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _MyProfile_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _MyProfile_vue_vue_type_template_id_8e9c9464_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _MyProfile_vue_vue_type_template_id_8e9c9464_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "8e9c9464",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/MyProfile.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/MyProfile.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./resources/js/views/MyProfile.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_MyProfile_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./MyProfile.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/MyProfile.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_MyProfile_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/MyProfile.vue?vue&type=template&id=8e9c9464&scoped=true&":
/*!*************************************************************************************!*\
  !*** ./resources/js/views/MyProfile.vue?vue&type=template&id=8e9c9464&scoped=true& ***!
  \*************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_MyProfile_vue_vue_type_template_id_8e9c9464_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./MyProfile.vue?vue&type=template&id=8e9c9464&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/MyProfile.vue?vue&type=template&id=8e9c9464&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_MyProfile_vue_vue_type_template_id_8e9c9464_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_MyProfile_vue_vue_type_template_id_8e9c9464_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);
//# sourceMappingURL=13.js.map
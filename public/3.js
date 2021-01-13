(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[3],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TopMenu.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TopMenu.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _auth__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../auth */ "./resources/js/auth.js");
function _templateObject2() {
  var data = _taggedTemplateLiteral(["mutation{\n                        logout{\n                            status\n                        }\n                    }"]);

  _templateObject2 = function _templateObject2() {
    return data;
  };

  return data;
}

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                query {\n                    me{\n                        id\n                        name\n                        email\n                    }\n                }\n            "]);

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
//
//
//


/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'TopMenu',
  data: function data() {
    return {
      loggedIn: false
    };
  },
  mounted: function mounted() {
    var _this = this;

    this.loggedIn = Object(_auth__WEBPACK_IMPORTED_MODULE_1__["isLoggedIn"])();
    this.$root.$on('loggedIn', function () {
      _this.$apollo.queries.me.refresh(); // This is a hack to make sure apollo is in loading state


      setTimeout(function () {
        _this.loggedIn = true;
      }, 300);
    });
    this.$root.$on('userUpdated', function () {
      _this.$apollo.queries.me.refresh();
    });
  },
  apollo: {
    me: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject())
    }
  },
  methods: {
    logout: function logout() {
      var _this2 = this;

      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject2())
      })["finally"](function () {
        Object(_auth__WEBPACK_IMPORTED_MODULE_1__["logoutUser"])();
        _this2.loggedIn = false;

        _this2.$router.push({
          name: 'home'
        })["catch"](function () {});
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css&":
/*!********************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css& ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n.nav-item-span[data-v-3c68e285] {\n    margin-left: 0.5rem;\n}\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css&":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css& ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TopMenu.vue?vue&type=template&id=3c68e285&scoped=true&":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TopMenu.vue?vue&type=template&id=3c68e285&scoped=true& ***!
  \*****************************************************************************************************************************************************************************************************************/
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
    "b-navbar",
    { staticClass: "container" },
    [
      _c(
        "template",
        { slot: "brand" },
        [
          _c(
            "b-navbar-item",
            { attrs: { to: { path: "/" }, tag: "router-link" } },
            [_c("b-icon", { attrs: { icon: "home", size: "is-large" } })],
            1
          )
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "template",
        { slot: "start" },
        [
          _c(
            "b-navbar-item",
            {
              attrs: {
                to: { path: "/team-fight/dashboard" },
                tag: "router-link"
              }
            },
            [
              _c("b-icon", { attrs: { icon: "hand-rock" } }),
              _vm._v(" "),
              _c("span", { staticClass: "nav-item-span" }, [_vm._v("Holdkamp")])
            ],
            1
          )
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "template",
        { slot: "end" },
        [
          !_vm.$apollo.queries.me.loading && !_vm.loggedIn
            ? _c("b-navbar-item", { attrs: { tag: "div" } }, [
                _c(
                  "div",
                  { staticClass: "buttons" },
                  [
                    _c(
                      "router-link",
                      {
                        staticClass: "button",
                        attrs: { to: { name: "new-user-create" } }
                      },
                      [_c("strong", [_vm._v("Kom i gang")])]
                    ),
                    _vm._v(" "),
                    _c(
                      "router-link",
                      {
                        staticClass: "button",
                        attrs: { to: { name: "login" } }
                      },
                      [_vm._v("\n                    Login\n                ")]
                    )
                  ],
                  1
                )
              ])
            : _vm._e(),
          _vm._v(" "),
          !_vm.$apollo.queries.me.loading && _vm.loggedIn
            ? _c(
                "b-dropdown",
                {
                  attrs: {
                    "append-to-body": "",
                    "aria-role": "menu",
                    expanded: ""
                  }
                },
                [
                  _c(
                    "a",
                    {
                      staticClass: "navbar-item",
                      attrs: { slot: "trigger", role: "button" },
                      slot: "trigger"
                    },
                    [
                      _c(
                        "span",
                        { staticStyle: { "margin-right": "0.5rem" } },
                        [_vm._v(_vm._s(_vm.me.name.split(" ")[0]))]
                      ),
                      _vm._v(" "),
                      _c("b-icon", { attrs: { icon: "user-alt" } })
                    ],
                    1
                  ),
                  _vm._v(" "),
                  _c(
                    "b-dropdown-item",
                    { attrs: { "aria-role": "menuitem", custom: "" } },
                    [
                      _vm._v("\n                Logged ind som "),
                      _c("b", [_vm._v(_vm._s(_vm.me.name))])
                    ]
                  ),
                  _vm._v(" "),
                  _c("hr", { staticClass: "dropdown-divider" }),
                  _vm._v(" "),
                  _c(
                    "b-dropdown-item",
                    { attrs: { "aria-role": "menuitem", "has-link": "" } },
                    [
                      _c(
                        "router-link",
                        { attrs: { to: { name: "my-profile" } } },
                        [
                          _vm._v(
                            "\n                    Min profil\n                "
                          )
                        ]
                      )
                    ],
                    1
                  ),
                  _vm._v(" "),
                  _c(
                    "b-dropdown-item",
                    {
                      attrs: { "aria-role": "menuitem" },
                      on: { click: _vm.logout }
                    },
                    [
                      _c("b-icon", { attrs: { icon: "sign-out-alt" } }),
                      _vm._v("\n                Log ud\n            ")
                    ],
                    1
                  )
                ],
                1
              )
            : _vm._e()
        ],
        1
      )
    ],
    2
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/views/TopMenu.vue":
/*!****************************************!*\
  !*** ./resources/js/views/TopMenu.vue ***!
  \****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _TopMenu_vue_vue_type_template_id_3c68e285_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TopMenu.vue?vue&type=template&id=3c68e285&scoped=true& */ "./resources/js/views/TopMenu.vue?vue&type=template&id=3c68e285&scoped=true&");
/* harmony import */ var _TopMenu_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TopMenu.vue?vue&type=script&lang=js& */ "./resources/js/views/TopMenu.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _TopMenu_vue_vue_type_style_index_0_id_3c68e285_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css& */ "./resources/js/views/TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _TopMenu_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _TopMenu_vue_vue_type_template_id_3c68e285_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _TopMenu_vue_vue_type_template_id_3c68e285_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "3c68e285",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/TopMenu.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/TopMenu.vue?vue&type=script&lang=js&":
/*!*****************************************************************!*\
  !*** ./resources/js/views/TopMenu.vue?vue&type=script&lang=js& ***!
  \*****************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./TopMenu.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TopMenu.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css&":
/*!*************************************************************************************************!*\
  !*** ./resources/js/views/TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css& ***!
  \*************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_style_index_0_id_3c68e285_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TopMenu.vue?vue&type=style&index=0&id=3c68e285&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_style_index_0_id_3c68e285_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_style_index_0_id_3c68e285_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_style_index_0_id_3c68e285_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(["default"].indexOf(__WEBPACK_IMPORT_KEY__) < 0) (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_style_index_0_id_3c68e285_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));


/***/ }),

/***/ "./resources/js/views/TopMenu.vue?vue&type=template&id=3c68e285&scoped=true&":
/*!***********************************************************************************!*\
  !*** ./resources/js/views/TopMenu.vue?vue&type=template&id=3c68e285&scoped=true& ***!
  \***********************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_template_id_3c68e285_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./TopMenu.vue?vue&type=template&id=3c68e285&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TopMenu.vue?vue&type=template&id=3c68e285&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_template_id_3c68e285_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TopMenu_vue_vue_type_template_id_3c68e285_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);
//# sourceMappingURL=3.js.map
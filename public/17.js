(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[17],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFightImport.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TeamFightImport.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
function _templateObject2() {
  var data = _taggedTemplateLiteral(["\n                        mutation badmintonPlayerTeamMatchImport($importInput: TeamMatchImportInput) {\n                          badmintonPlayerTeamMatchImport(input: $importInput)\n                        }"]);

  _templateObject2 = function _templateObject2() {
    return data;
  };

  return data;
}

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                            query badmintonPlayerTeamMatch($badmintonInput: BadmintonPlayerTeamMatchInput) {\n                              badmintonPlayerTeamMatch(input: $badmintonInput) {\n                                guest {\n                                  ...matcheClub\n                                }\n                                home{\n                                  ...matcheClub\n                                }\n                              }\n                            }\n                             fragment matcheClub on ImportTeam {\n                                  name\n                                  squad {\n                                    playerLimit\n                                    categories {\n                                      category\n                                      name\n                                      players {\n                                        name\n                                      }\n                                    }\n                                  }\n                                }"]);

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

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "TeamFightImport",
  props: {
    teamFightId: String
  },
  data: function data() {
    return {
      clubId: "1622",
      leagueMatchId: null,
      season: "2020",
      version: "2021-02-17",
      skipPlayers: true,
      importing: false
    };
  },
  apollo: {
    badmintonPlayerTeamMatch: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject()),
      variables: function variables() {
        return {
          badmintonInput: {
            "clubId": this.clubId,
            "leagueMatchId": this.leagueMatchId,
            "season": this.season,
            "version": this.version
          }
        };
      },
      skip: function skip() {
        return this.skipPlayers;
      }
    }
  },
  methods: {
    fetchPlayers: function fetchPlayers() {
      this.skipPlayers = false;
      this.$apollo.queries.badmintonPlayerTeamMatch.refresh();
    },
    importTeam: function importTeam(side) {
      var _this = this;

      this.importing = true;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject2()),
        variables: {
          importInput: {
            team: this.teamFightId,
            badmintonPlayerTeamMatch: {
              clubId: this.clubId,
              leagueMatchId: this.leagueMatchId,
              season: this.season,
              version: this.version
            },
            side: side
          }
        }
      }).then(function (_ref) {
        var data = _ref.data;

        _this.$buefy.snackbar.open({
          duration: 4000,
          type: 'is-success',
          message: 'Importering færdig'
        });
      })["catch"](function (error) {
        _this.$buefy.snackbar.open({
          duration: 4000,
          type: 'is-dagner',
          message: 'Kunne ikke importer'
        });
      })["finally"](function () {
        _this.importing = false;
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFightImport.vue?vue&type=template&id=c068f72e&scoped=true&":
/*!*************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TeamFightImport.vue?vue&type=template&id=c068f72e&scoped=true& ***!
  \*************************************************************************************************************************************************************************************************************************/
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
    "section",
    [
      _vm._v(
        '\n\n    "team": "o5nuLdBPT8rH4yik1aQUz6GA",\n    "badmintonPlayerTeamMatch": {\n    "clubId": "1622",\n    "leagueMatchId": "385663",\n    "season": "2020",\n    "version": "2021-02-17"\n    },\n    "side": "GUEST"\n    '
      ),
      _c(
        "b-button",
        {
          attrs: {
            tag: "router-link",
            to: "/team-fight/" + this.teamFightId + "/edit",
            type: "is-link"
          }
        },
        [_vm._v("\n        Tilbage\n    ")]
      ),
      _vm._v(" "),
      _c(
        "form",
        {
          on: {
            submit: function($event) {
              $event.preventDefault()
            }
          }
        },
        [
          _c(
            "b-field",
            { attrs: { label: "Klub Id" } },
            [
              _c("b-input", {
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
            { attrs: { label: "Kamp Id" } },
            [
              _c("b-input", {
                model: {
                  value: _vm.leagueMatchId,
                  callback: function($$v) {
                    _vm.leagueMatchId = $$v
                  },
                  expression: "leagueMatchId"
                }
              })
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "b-field",
            { attrs: { label: "Sæson" } },
            [
              _c("b-input", {
                model: {
                  value: _vm.season,
                  callback: function($$v) {
                    _vm.season = $$v
                  },
                  expression: "season"
                }
              })
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "b-field",
            { attrs: { label: "Ranglist version" } },
            [
              _c("b-input", {
                model: {
                  value: _vm.version,
                  callback: function($$v) {
                    _vm.version = $$v
                  },
                  expression: "version"
                }
              })
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "b-button",
            { attrs: { type: "submit" }, on: { click: _vm.fetchPlayers } },
            [_vm._v("Test")]
          )
        ],
        1
      ),
      _vm._v(" "),
      !_vm.skipPlayers && !_vm.$apollo.loading
        ? _c("div", [
            _c(
              "div",
              [
                _vm._v(
                  _vm._s(_vm.badmintonPlayerTeamMatch.home.name) +
                    "\n            "
                ),
                _c(
                  "b-button",
                  {
                    attrs: { loading: _vm.importing },
                    on: {
                      click: function($event) {
                        return _vm.importTeam("HOME")
                      }
                    }
                  },
                  [_vm._v("Import")]
                )
              ],
              1
            ),
            _vm._v(" "),
            _c(
              "div",
              [
                _vm._v(
                  _vm._s(_vm.badmintonPlayerTeamMatch.guest.name) +
                    "\n            "
                ),
                _c(
                  "b-button",
                  {
                    attrs: { loading: _vm.importing },
                    on: {
                      click: function($event) {
                        return _vm.importTeam("GUEST")
                      }
                    }
                  },
                  [_vm._v("Import")]
                )
              ],
              1
            )
          ])
        : _vm._e()
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/views/TeamFightImport.vue":
/*!************************************************!*\
  !*** ./resources/js/views/TeamFightImport.vue ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _TeamFightImport_vue_vue_type_template_id_c068f72e_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TeamFightImport.vue?vue&type=template&id=c068f72e&scoped=true& */ "./resources/js/views/TeamFightImport.vue?vue&type=template&id=c068f72e&scoped=true&");
/* harmony import */ var _TeamFightImport_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TeamFightImport.vue?vue&type=script&lang=js& */ "./resources/js/views/TeamFightImport.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _TeamFightImport_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _TeamFightImport_vue_vue_type_template_id_c068f72e_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _TeamFightImport_vue_vue_type_template_id_c068f72e_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "c068f72e",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/TeamFightImport.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/TeamFightImport.vue?vue&type=script&lang=js&":
/*!*************************************************************************!*\
  !*** ./resources/js/views/TeamFightImport.vue?vue&type=script&lang=js& ***!
  \*************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightImport_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./TeamFightImport.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFightImport.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightImport_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/TeamFightImport.vue?vue&type=template&id=c068f72e&scoped=true&":
/*!*******************************************************************************************!*\
  !*** ./resources/js/views/TeamFightImport.vue?vue&type=template&id=c068f72e&scoped=true& ***!
  \*******************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightImport_vue_vue_type_template_id_c068f72e_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./TeamFightImport.vue?vue&type=template&id=c068f72e&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFightImport.vue?vue&type=template&id=c068f72e&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightImport_vue_vue_type_template_id_c068f72e_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightImport_vue_vue_type_template_id_c068f72e_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);
//# sourceMappingURL=17.js.map
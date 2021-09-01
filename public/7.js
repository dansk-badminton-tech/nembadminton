(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[7],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamTable.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TeamTable.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var vuedraggable__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vuedraggable */ "./node_modules/vuedraggable/dist/vuedraggable.common.js");
/* harmony import */ var vuedraggable__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vuedraggable__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _helpers__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../helpers */ "./resources/js/helpers.js");
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

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
//
//
//
//
//
//
//


/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'TeamTable',
  components: {
    Draggable: vuedraggable__WEBPACK_IMPORTED_MODULE_0___default.a
  },
  props: {
    viewMode: Boolean,
    confirmDelete: Function,
    move: Function,
    deletePlayer: Function,
    copyPlayer: Function,
    playingToHigh: {
      type: Array,
      "default": []
    },
    playingToHighInSquad: {
      type: Array,
      "default": []
    },
    teams: {
      type: Array,
      "default": []
    },
    search: {
      type: String,
      "default": ''
    }
  },
  methods: {
    resolveLabel: function resolveLabel(player) {
      var msg = "";

      if (this.isPlayingToHigh(player)) {
        msg += "Gul: En eller flere spiller har mere end 50/100 point på NIVEAU-ranglisten, på et laverer hold";
      }

      if (this.isPlayingToHighInSquad(player)) {
        msg += "\n Rød: En eller flere spiller har mere end 50 point på kategori-ranglisten, på et laverer hold";
      }

      return msg;
    },
    isPlayingToHigh: function isPlayingToHigh(player) {
      return Object(_helpers__WEBPACK_IMPORTED_MODULE_1__["isPlayingToHigh"])(this.playingToHigh, player);
    },
    isPlayingToHighInSquad: function isPlayingToHighInSquad(player) {
      return Object(_helpers__WEBPACK_IMPORTED_MODULE_1__["isPlayingToHigh"])(this.playingToHighInSquad, player);
    },
    findPositions: _helpers__WEBPACK_IMPORTED_MODULE_1__["findPositions"],
    highlight: function highlight(player) {
      var base = {};

      if (this.viewMode) {
        base = {
          'pointer': false
        };

        if (this.search.trim() !== '') {
          base = _objectSpread(_objectSpread({}, {
            'has-text-white': player.name.toLowerCase().includes(this.search.toLowerCase()),
            'has-background-black': player.name.toLowerCase().includes(this.search.toLowerCase())
          }), base);
        }
      } else {
        if (Object(_helpers__WEBPACK_IMPORTED_MODULE_1__["isPlayingToHigh"])(this.playingToHigh, player)) {
          base = _objectSpread(_objectSpread({}, {
            'has-background-warning': true
          }), base);
        }

        if (Object(_helpers__WEBPACK_IMPORTED_MODULE_1__["isPlayingToHigh"])(this.playingToHighInSquad, player)) {
          base = _objectSpread(_objectSpread({}, {
            'has-background-danger': true
          }), base);
        }
      }

      return base;
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamTable.vue?vue&type=template&id=b585263c&":
/*!*******************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TeamTable.vue?vue&type=template&id=b585263c& ***!
  \*******************************************************************************************************************************************************************************************************/
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
    "fragment",
    _vm._l(_vm.teams, function(team, index) {
      return _c("div", { key: team.id, staticClass: "column is-4" }, [
        _c("table", { staticClass: "table is-striped mt-5 is-fullwidth" }, [
          _c("thead", [
            _c("tr", [
              _c(
                "th",
                { attrs: { colspan: "2" } },
                [
                  _vm._v(
                    "\n                    Hold " +
                      _vm._s(index + 1) +
                      "\n                    "
                  ),
                  !_vm.viewMode
                    ? _c("b-button", {
                        staticClass: "is-pulled-right",
                        attrs: { "icon-left": "trash-alt" },
                        on: {
                          click: function($event) {
                            return _vm.confirmDelete(team)
                          }
                        }
                      })
                    : _vm._e(),
                  _vm._v(" "),
                  !_vm.viewMode
                    ? _c(
                        "b-tooltip",
                        {
                          staticClass: "is-pulled-right",
                          attrs: { label: "Flyt hold op" }
                        },
                        [
                          index !== 0
                            ? _c("b-button", {
                                attrs: { "icon-left": "angle-up" },
                                on: {
                                  click: function($event) {
                                    return _vm.move(index, -1)
                                  }
                                }
                              })
                            : _vm._e()
                        ],
                        1
                      )
                    : _vm._e(),
                  _vm._v(" "),
                  !_vm.viewMode
                    ? _c(
                        "b-tooltip",
                        {
                          staticClass: "is-pulled-right",
                          attrs: { label: "Flyt hold ned" }
                        },
                        [
                          index !== _vm.teams.length - 1
                            ? _c("b-button", {
                                attrs: { "icon-left": "angle-down" },
                                on: {
                                  click: function($event) {
                                    return _vm.move(index, 1)
                                  }
                                }
                              })
                            : _vm._e()
                        ],
                        1
                      )
                    : _vm._e()
                ],
                1
              )
            ])
          ]),
          _vm._v(" "),
          _c(
            "tbody",
            _vm._l(team.categories, function(category) {
              return _c(
                "tr",
                { key: category.name },
                [
                  _c("th", [_vm._v(_vm._s(category.name))]),
                  _vm._v(" "),
                  _c(
                    "draggable",
                    {
                      attrs: {
                        disabled: _vm.viewMode,
                        list: category.players,
                        group: "players",
                        handle: ".handle",
                        tag: "td"
                      },
                      on: {
                        end: function($event) {
                          return _vm.$emit("end")
                        }
                      }
                    },
                    [
                      _vm._l(category.players, function(player) {
                        return _c(
                          "div",
                          { staticClass: "is-clearfix mt-1" },
                          [
                            _c(
                              "b-tooltip",
                              {
                                attrs: {
                                  label: _vm.resolveLabel(player),
                                  active:
                                    _vm.isPlayingToHigh(player) ||
                                    _vm.isPlayingToHighInSquad(player),
                                  multilined: ""
                                }
                              },
                              [
                                _c(
                                  "p",
                                  {
                                    staticClass: "fa-pull-left handle",
                                    class: _vm.highlight(player)
                                  },
                                  [
                                    !_vm.viewMode
                                      ? _c("b-icon", {
                                          attrs: {
                                            icon: "bars",
                                            size: "is-small"
                                          }
                                        })
                                      : _vm._e(),
                                    _vm._v(
                                      "\n                                " +
                                        _vm._s(player.name) +
                                        " (" +
                                        _vm._s(
                                          _vm.findPositions(player, "N") +
                                            " " +
                                            _vm.findPositions(
                                              player,
                                              category.category
                                            )
                                        ) +
                                        ")\n                            "
                                    )
                                  ],
                                  1
                                )
                              ]
                            ),
                            _vm._v(" "),
                            _c(
                              "b-dropdown",
                              {
                                staticClass: "is-pulled-right",
                                attrs: { "aria-role": "list" }
                              },
                              [
                                category.players.length && !_vm.viewMode
                                  ? _c("b-button", {
                                      attrs: {
                                        slot: "trigger",
                                        "icon-left": "ellipsis-v",
                                        size: "is-small"
                                      },
                                      slot: "trigger"
                                    })
                                  : _vm._e(),
                                _vm._v(" "),
                                _c(
                                  "b-dropdown-item",
                                  {
                                    attrs: {
                                      "aria-role": "menuitem",
                                      "has-link": ""
                                    }
                                  },
                                  [
                                    _c(
                                      "a",
                                      {
                                        attrs: { href: "#" },
                                        on: {
                                          click: function($event) {
                                            $event.preventDefault()
                                            return _vm.deletePlayer(
                                              category,
                                              player
                                            )
                                          }
                                        }
                                      },
                                      [
                                        _c("b-icon", {
                                          attrs: { icon: "times-circle" }
                                        }),
                                        _vm._v(
                                          "\n                                    Slet\n                                "
                                        )
                                      ],
                                      1
                                    )
                                  ]
                                ),
                                _vm._v(" "),
                                _c(
                                  "b-dropdown-item",
                                  {
                                    attrs: {
                                      "aria-role": "menuitem",
                                      "has-link": ""
                                    }
                                  },
                                  [
                                    _c(
                                      "a",
                                      {
                                        attrs: { href: "#" },
                                        on: {
                                          click: function($event) {
                                            $event.preventDefault()
                                            return _vm.copyPlayer(
                                              category,
                                              player
                                            )
                                          }
                                        }
                                      },
                                      [
                                        _c("b-icon", {
                                          attrs: { icon: "copy" }
                                        }),
                                        _vm._v(
                                          "\n                                    Kopier\n                                "
                                        )
                                      ],
                                      1
                                    )
                                  ]
                                )
                              ],
                              1
                            )
                          ],
                          1
                        )
                      }),
                      _vm._v(" "),
                      !category.players.length
                        ? _c("p", [_vm._v("---------------")])
                        : _vm._e()
                    ],
                    2
                  )
                ],
                1
              )
            }),
            0
          )
        ])
      ])
    }),
    0
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/views/TeamTable.vue":
/*!******************************************!*\
  !*** ./resources/js/views/TeamTable.vue ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _TeamTable_vue_vue_type_template_id_b585263c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TeamTable.vue?vue&type=template&id=b585263c& */ "./resources/js/views/TeamTable.vue?vue&type=template&id=b585263c&");
/* harmony import */ var _TeamTable_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TeamTable.vue?vue&type=script&lang=js& */ "./resources/js/views/TeamTable.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _TeamTable_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _TeamTable_vue_vue_type_template_id_b585263c___WEBPACK_IMPORTED_MODULE_0__["render"],
  _TeamTable_vue_vue_type_template_id_b585263c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/TeamTable.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/TeamTable.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./resources/js/views/TeamTable.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamTable_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./TeamTable.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamTable.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamTable_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/TeamTable.vue?vue&type=template&id=b585263c&":
/*!*************************************************************************!*\
  !*** ./resources/js/views/TeamTable.vue?vue&type=template&id=b585263c& ***!
  \*************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamTable_vue_vue_type_template_id_b585263c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./TeamTable.vue?vue&type=template&id=b585263c& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamTable.vue?vue&type=template&id=b585263c&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamTable_vue_vue_type_template_id_b585263c___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamTable_vue_vue_type_template_id_b585263c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);
//# sourceMappingURL=7.js.map
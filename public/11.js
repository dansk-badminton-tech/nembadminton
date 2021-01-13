(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[11],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFightPublic.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TeamFightPublic.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _TeamTable__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TeamTable */ "./resources/js/views/TeamTable.vue");
function _templateObject() {
  var data = _taggedTemplateLiteral([" query ($id: ID!){\n                  team(id: $id){\n                    id\n                    name\n                    gameDate\n                    squads{\n                        id\n                        playerLimit\n                        categories{\n                            category\n                            name\n                            players{\n                                gender\n                                id\n                                name\n                                refId\n                                points{\n                                    category\n                                    points\n                                    position\n                                }\n                            }\n                        }\n                    }\n                    club {\n                        id\n                        name1\n                    }\n                  }\n                }"]);

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


/* harmony default export */ __webpack_exports__["default"] = ({
  name: "TeamFightPublic",
  components: {
    TeamTable: _TeamTable__WEBPACK_IMPORTED_MODULE_1__["default"]
  },
  data: function data() {
    return {
      searchPlayer: ''
    };
  },
  props: {
    teamFightId: String
  },
  apollo: {
    team: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject()),
      fetchPolicy: "network-only",
      variables: function variables() {
        return {
          id: this.teamFightId
        };
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFightPublic.vue?vue&type=template&id=daba7da6&scoped=true&":
/*!*************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TeamFightPublic.vue?vue&type=template&id=daba7da6&scoped=true& ***!
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
  return !_vm.$apollo.loading
    ? _c(
        "section",
        { staticClass: "section" },
        [
          _c("h1", { staticClass: "is-size-1" }, [
            _vm._v(_vm._s(_vm.team.name))
          ]),
          _vm._v(" "),
          _c("p", [_vm._v("Spille dato: " + _vm._s(_vm.team.gameDate))]),
          _vm._v(" "),
          _c(
            "b-field",
            { attrs: { label: "Søg efter spiller" } },
            [
              _c("b-input", {
                model: {
                  value: _vm.searchPlayer,
                  callback: function($$v) {
                    _vm.searchPlayer = $$v
                  },
                  expression: "searchPlayer"
                }
              })
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "div",
            { staticClass: "columns is-multiline" },
            [
              _c("TeamTable", {
                attrs: {
                  search: this.searchPlayer,
                  teams: this.team.squads,
                  viewMode: true
                }
              })
            ],
            1
          )
        ],
        1
      )
    : _vm._e()
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/helpers.js":
/*!*********************************!*\
  !*** ./resources/js/helpers.js ***!
  \*********************************/
/*! exports provided: chunk, defaultIfUndefined, debounce, findPlayersInCategory, findLevel, findPositions, extractErrors */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "chunk", function() { return chunk; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "defaultIfUndefined", function() { return defaultIfUndefined; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "debounce", function() { return debounce; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "findPlayersInCategory", function() { return findPlayersInCategory; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "findLevel", function() { return findLevel; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "findPositions", function() { return findPositions; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "extractErrors", function() { return extractErrors; });
function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function chunk(array, size) {
  var chunked_arr = [];

  for (var i = 0; i < array.length; i++) {
    var last = chunked_arr[chunked_arr.length - 1];

    if (!last || last.length === size) {
      chunked_arr.push([array[i]]);
    } else {
      last.push(array[i]);
    }
  }

  return chunked_arr;
}
function defaultIfUndefined(test, defaultValue) {
  if (test === undefined) {
    return defaultValue;
  }

  return test;
}
function debounce(fn, delay) {
  var timeoutID = null;
  return function () {
    clearTimeout(timeoutID);
    var args = arguments;
    var that = this;
    timeoutID = setTimeout(function () {
      fn.apply(that, args);
    }, delay);
  };
}
function findPlayersInCategory(categories, searchCategory, gender) {
  var players = [];

  var _iterator = _createForOfIteratorHelper(categories),
      _step;

  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var categoryItem = _step.value;

      if (categoryItem.category === searchCategory) {
        var _iterator2 = _createForOfIteratorHelper(categoryItem.players),
            _step2;

        try {
          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
            var player = _step2.value;

            if (player.gender === gender) {
              players.push(player);
            }
          }
        } catch (err) {
          _iterator2.e(err);
        } finally {
          _iterator2.f();
        }
      }
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }

  return players;
}
function findLevel(member, category) {
  if (!member.points) {
    return 0;
  }

  var _iterator3 = _createForOfIteratorHelper(member.points),
      _step3;

  try {
    for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
      var point = _step3.value;

      if (category === 'MD') {
        if (member.gender === 'M' && point.category === 'MxH') {
          return point.points;
        }

        if (member.gender === 'K' && point.category === 'MxD') {
          return point.points;
        }
      }

      if (point.category === category) {
        return point.points;
      }
    }
  } catch (err) {
    _iterator3.e(err);
  } finally {
    _iterator3.f();
  }

  return 0;
}
function findPositions(member) {
  if (!member.points) {
    return '';
  }

  var summary = [];

  var _iterator4 = _createForOfIteratorHelper(member.points),
      _step4;

  try {
    for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
      var point = _step4.value;

      if (point.category === null && point.points !== null) {
        summary.push('N:' + point.points);
      } //                if (point.category === 'HS') {
      //                    summary.push('HS:' + point.points)
      //                }
      //                if (point.category === 'HD') {
      //                    summary.push('HD:' + point.points)
      //                }
      //                if (point.category === 'DS') {
      //                    summary.push('DS:' + point.points)
      //                }
      //                if (point.category === 'MxH') {
      //                    summary.push('MxH:' + point.points)
      //                }
      //                if (point.category === 'MxD') {
      //                    summary.push('MxD:' + point.points)
      //                }

    }
  } catch (err) {
    _iterator4.e(err);
  } finally {
    _iterator4.f();
  }

  return '(' + summary.join(', ') + ')';
}
function extractErrors(graphqlErrors) {
  var errors = [];

  var _iterator5 = _createForOfIteratorHelper(graphqlErrors),
      _step5;

  try {
    for (_iterator5.s(); !(_step5 = _iterator5.n()).done;) {
      var graphqlError = _step5.value;

      if (graphqlError.extensions.category === 'validation') {
        for (var validationKey in graphqlError.extensions.validation) {
          var _iterator6 = _createForOfIteratorHelper(graphqlError.extensions.validation[validationKey]),
              _step6;

          try {
            for (_iterator6.s(); !(_step6 = _iterator6.n()).done;) {
              var error = _step6.value;
              errors.push(error);
            }
          } catch (err) {
            _iterator6.e(err);
          } finally {
            _iterator6.f();
          }
        }
      }
    }
  } catch (err) {
    _iterator5.e(err);
  } finally {
    _iterator5.f();
  }

  return errors;
}

/***/ }),

/***/ "./resources/js/views/TeamFightPublic.vue":
/*!************************************************!*\
  !*** ./resources/js/views/TeamFightPublic.vue ***!
  \************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _TeamFightPublic_vue_vue_type_template_id_daba7da6_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TeamFightPublic.vue?vue&type=template&id=daba7da6&scoped=true& */ "./resources/js/views/TeamFightPublic.vue?vue&type=template&id=daba7da6&scoped=true&");
/* harmony import */ var _TeamFightPublic_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TeamFightPublic.vue?vue&type=script&lang=js& */ "./resources/js/views/TeamFightPublic.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _TeamFightPublic_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _TeamFightPublic_vue_vue_type_template_id_daba7da6_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _TeamFightPublic_vue_vue_type_template_id_daba7da6_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "daba7da6",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/TeamFightPublic.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/TeamFightPublic.vue?vue&type=script&lang=js&":
/*!*************************************************************************!*\
  !*** ./resources/js/views/TeamFightPublic.vue?vue&type=script&lang=js& ***!
  \*************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightPublic_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./TeamFightPublic.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFightPublic.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightPublic_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/TeamFightPublic.vue?vue&type=template&id=daba7da6&scoped=true&":
/*!*******************************************************************************************!*\
  !*** ./resources/js/views/TeamFightPublic.vue?vue&type=template&id=daba7da6&scoped=true& ***!
  \*******************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightPublic_vue_vue_type_template_id_daba7da6_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./TeamFightPublic.vue?vue&type=template&id=daba7da6&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFightPublic.vue?vue&type=template&id=daba7da6&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightPublic_vue_vue_type_template_id_daba7da6_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFightPublic_vue_vue_type_template_id_daba7da6_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);
//# sourceMappingURL=11.js.map
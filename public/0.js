(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[0],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/search-club/ClubSearch.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/search-club/ClubSearch.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _helpers__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../helpers */ "./resources/js/helpers.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_1__);
function _templateObject() {
  var data = _taggedTemplateLiteral(["query ClubsSearch($name: String){\n                      clubsSearch(name: $name, orderBy: { column: NAME1, order: ASC }) {\n                        data {\n                          id\n                          name1\n                        }\n                        paginatorInfo {\n                          count\n                          total\n                        }\n                      }\n                    }\n                "]);

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


/* harmony default export */ __webpack_exports__["default"] = ({
  name: "ClubSearch",
  props: {
    selectClub: Function
  },
  methods: {
    setClub: function setClub(option) {
      if (!option) {
        return;
      }

      if (typeof this.selectClub === 'function') {
        this.selectClub(parseInt(option.id));
      }

      this.selectedClub = option;
    },
    unsetClub: function unsetClub() {
      this.selectedClub = null;
      this.selectClub(null);
    },
    searchClubs: Object(_helpers__WEBPACK_IMPORTED_MODULE_0__["debounce"])(function (name) {
      if (!name.length) {
        this.clubs.data = [];
        return;
      }

      this.searchName = name;
      this.$apollo.queries.clubs.refresh();
    }, 200)
  },
  data: function data() {
    return {
      querySearchName: '',
      searchName: '',
      selectedClub: null,
      clubs: {
        data: []
      }
    };
  },
  apollo: {
    clubs: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_1___default()(_templateObject()),
      update: function update(data) {
        return data.clubsSearch;
      },
      variables: function variables() {
        return {
          name: '%' + this.searchName + '%'
        };
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/search-club/ClubSearch.vue?vue&type=template&id=e97bec06&scoped=true&":
/*!*************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/search-club/ClubSearch.vue?vue&type=template&id=e97bec06&scoped=true& ***!
  \*************************************************************************************************************************************************************************************************************************************/
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
        { attrs: { label: _vm.$t("roundsGenerator.findClub") } },
        [
          !_vm.selectedClub
            ? _c(
                "b-autocomplete",
                {
                  attrs: {
                    icon: "search",
                    "clear-on-select": true,
                    clearable: true,
                    data: this.clubs.data,
                    "keep-first": true,
                    loading: _vm.$apollo.queries.clubs.loading,
                    placeholder: _vm.$t("roundsGenerator.findClubPlaceholder"),
                    field: "name1"
                  },
                  on: { select: _vm.setClub, typing: _vm.searchClubs },
                  model: {
                    value: _vm.querySearchName,
                    callback: function($$v) {
                      _vm.querySearchName = $$v
                    },
                    expression: "querySearchName"
                  }
                },
                [
                  _c("template", { slot: "empty" }, [
                    _vm._v("No results for " + _vm._s(_vm.querySearchName))
                  ])
                ],
                2
              )
            : _vm._e()
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "div",
        { staticClass: "field" },
        [
          _vm.selectedClub
            ? _c(
                "b-tag",
                {
                  attrs: {
                    "aria-close-label": "Fjern",
                    attached: "",
                    closable: "",
                    "close-type": "is-danger",
                    size: "is-large"
                  },
                  on: { close: _vm.unsetClub }
                },
                [
                  _vm._v(
                    "\n            " +
                      _vm._s(_vm.selectedClub.name1) +
                      "\n        "
                  )
                ]
              )
            : _vm._e()
        ],
        1
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/components/search-club/ClubSearch.vue":
/*!************************************************************!*\
  !*** ./resources/js/components/search-club/ClubSearch.vue ***!
  \************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _ClubSearch_vue_vue_type_template_id_e97bec06_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ClubSearch.vue?vue&type=template&id=e97bec06&scoped=true& */ "./resources/js/components/search-club/ClubSearch.vue?vue&type=template&id=e97bec06&scoped=true&");
/* harmony import */ var _ClubSearch_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ClubSearch.vue?vue&type=script&lang=js& */ "./resources/js/components/search-club/ClubSearch.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _ClubSearch_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _ClubSearch_vue_vue_type_template_id_e97bec06_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _ClubSearch_vue_vue_type_template_id_e97bec06_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "e97bec06",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/search-club/ClubSearch.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/search-club/ClubSearch.vue?vue&type=script&lang=js&":
/*!*************************************************************************************!*\
  !*** ./resources/js/components/search-club/ClubSearch.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ClubSearch_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./ClubSearch.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/search-club/ClubSearch.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ClubSearch_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/search-club/ClubSearch.vue?vue&type=template&id=e97bec06&scoped=true&":
/*!*******************************************************************************************************!*\
  !*** ./resources/js/components/search-club/ClubSearch.vue?vue&type=template&id=e97bec06&scoped=true& ***!
  \*******************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ClubSearch_vue_vue_type_template_id_e97bec06_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./ClubSearch.vue?vue&type=template&id=e97bec06&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/search-club/ClubSearch.vue?vue&type=template&id=e97bec06&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ClubSearch_vue_vue_type_template_id_e97bec06_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ClubSearch_vue_vue_type_template_id_e97bec06_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



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
  var show = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'all';

  if (!member.points) {
    return '';
  }

  var summary = [];

  var _iterator4 = _createForOfIteratorHelper(member.points),
      _step4;

  try {
    for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
      var point = _step4.value;

      if (point.category === null && point.points !== null && (show === 'all' || show === 'N')) {
        summary.push('N:' + point.points);
      }

      if (point.category === 'HS' && (show === 'all' || show === 'HS')) {
        summary.push('HS:' + point.points);
      }

      if (point.category === 'HD' && (show === 'all' || show === 'HD')) {
        summary.push('HD:' + point.points);
      }

      if (point.category === 'DS' && (show === 'all' || show === 'DS')) {
        summary.push('DS:' + point.points);
      }

      if (point.category === 'DD' && (show === 'all' || show === 'DD')) {
        summary.push('DD:' + point.points);
      }

      if (point.category === 'MxH' && member.gender === 'M' && (show === 'all' || show === 'MD')) {
        summary.push('MxH:' + point.points);
      }

      if (point.category === 'MxD' && member.gender === 'K' && (show === 'all' || show === 'MD')) {
        summary.push('MxD:' + point.points);
      }
    }
  } catch (err) {
    _iterator4.e(err);
  } finally {
    _iterator4.f();
  }

  return summary.join(', ');
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

/***/ })

}]);
//# sourceMappingURL=0.js.map
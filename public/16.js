(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[16],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                query($input: BadmintonPlayerTeamsInput){\n                    badmintonPlayerTeams(input: $input){\n                        leagueGroupId\n                        ageGroupId\n                        name\n                        league\n                    }\n                }\n            "]);

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

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "BadmintonPlayerTeamsMultiSelect",
  props: ['value', 'clubId', 'season'],
  methods: {
    isRowCheckable: function isRowCheckable(row) {
      return !(new RegExp('u[0-9]+', 'gmi').test(row.league) || new RegExp('sen\\+[0-9]+', 'gmi').test(row.league) || new RegExp('senior motion', 'gmi').test(row.league) || new RegExp('DMU', 'gmi').test(row.league) || new RegExp('1\\. division', 'gmi').test(row.league) || new RegExp('liga', 'gmi').test(row.league) || new RegExp('4 spillere', 'gmi').test(row.league));
    }
  },
  watch: {
    teams: function teams(newValue, oldValue) {
      this.$emit('input', newValue);
    }
  },
  data: function data() {
    return {
      columns: [{
        field: 'name',
        label: 'Navn'
      }, {
        field: 'league',
        label: 'Række'
      }],
      teams: []
    };
  },
  apollo: {
    badmintonPlayerTeams: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject()),
      result: function result(ApolloQueryResult, key) {
        this.teams = [];
      },
      variables: function variables() {
        return {
          input: {
            clubId: this.clubId,
            season: this.season
          }
        };
      },
      skip: function skip() {
        return this.clubId === null || this.season === null;
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/CheckTeamFight.vue?vue&type=script&lang=js&":
/*!********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/CheckTeamFight.vue?vue&type=script&lang=js& ***!
  \********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_badminton_player_BadmintonPlayerClubs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../components/badminton-player/BadmintonPlayerClubs */ "./resources/js/components/badminton-player/BadmintonPlayerClubs.vue");
/* harmony import */ var _components_badminton_player_BadmintonPlayerTeams__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../components/badminton-player/BadmintonPlayerTeams */ "./resources/js/components/badminton-player/BadmintonPlayerTeams.vue");
/* harmony import */ var _components_badminton_player_BadmintonPlayerTeamFights__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/badminton-player/BadmintonPlayerTeamFights */ "./resources/js/components/badminton-player/BadmintonPlayerTeamFights.vue");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var omit_deep__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! omit-deep */ "./node_modules/omit-deep/index.js");
/* harmony import */ var omit_deep__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(omit_deep__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _helpers__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../helpers */ "./resources/js/helpers.js");
/* harmony import */ var _components_badminton_player_BadmintonPlayerTeamsMultiSelect__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../components/badminton-player/BadmintonPlayerTeamsMultiSelect */ "./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue");
/* harmony import */ var vuedraggable__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! vuedraggable */ "./node_modules/vuedraggable/dist/vuedraggable.common.js");
/* harmony import */ var vuedraggable__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(vuedraggable__WEBPACK_IMPORTED_MODULE_7__);
function _templateObject2() {
  var data = _taggedTemplateLiteral(["mutation validateTeamMatch($input: [ValidateTeam!]!){\n                      validateTeamMatch(input: $input){\n                        name\n                        id\n                      }\n                    }\n                    "]);

  _templateObject2 = function _templateObject2() {
    return data;
  };

  return data;
}

function _templateObject() {
  var data = _taggedTemplateLiteral(["mutation ($input: BadmintonPlayerTeamMatchInput!){\n                        badmintonPlayerTeamMatchesImport(input: $input){\n                            name\n                            squad{\n                              playerLimit\n                              categories{\n                                category\n                                name\n                                players{\n                                  refId\n                                  name\n                                  gender\n                                  points{\n                                    points\n                                    position\n                                    category\n                                    version\n                                  }\n                                }\n                              }\n                            }\n                          }\n                        }\n                    "]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

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
  name: "CheckTeamFight",
  components: {
    BadmintonPlayerTeamsMultiSelect: _components_badminton_player_BadmintonPlayerTeamsMultiSelect__WEBPACK_IMPORTED_MODULE_6__["default"],
    BadmintonPlayerTeamFights: _components_badminton_player_BadmintonPlayerTeamFights__WEBPACK_IMPORTED_MODULE_2__["default"],
    BadmintonPlayerTeams: _components_badminton_player_BadmintonPlayerTeams__WEBPACK_IMPORTED_MODULE_1__["default"],
    BadmintonPlayerClubs: _components_badminton_player_BadmintonPlayerClubs__WEBPACK_IMPORTED_MODULE_0__["default"],
    Draggable: vuedraggable__WEBPACK_IMPORTED_MODULE_7___default.a
  },
  data: function data() {
    return {
      columns: [{
        field: 'team.name',
        label: 'Hold'
      }],
      clubId: null,
      playerTeams: [],
      season: null,
      teamFight: null,
      selectedTeamMatches: {},
      teams: [],
      playingToHigh: [],
      playingToHighInSquad: [],
      rankingList: null,
      activeStep: 0,
      fetchingAndValidating: false,
      done: false,
      sortingConfirmed: false,
      draggingRow: null,
      draggingRowIndex: null,
      draggingColumn: null,
      draggingColumnIndex: null,
      errorImporting: false
    };
  },
  computed: {
    hasViolations: function hasViolations() {
      return this.playingToHigh.length > 0 || this.playingToHighInSquad.length > 0;
    }
  },
  methods: {
    maybeMoveDown: function maybeMoveDown(index) {
      return this.castToArray(this.selectedTeamMatches).length - 1 === index;
    },
    moveUp: function moveUp(index) {
      Object(_helpers__WEBPACK_IMPORTED_MODULE_5__["swapObject"])(this.selectedTeamMatches, index, index - 1);
    },
    moveDown: function moveDown(index) {
      Object(_helpers__WEBPACK_IMPORTED_MODULE_5__["swapObject"])(this.selectedTeamMatches, index, index + 1);
    },
    castToArray: function castToArray(object) {
      return Object.values(object);
    },
    dragstart: function dragstart(payload) {
      this.draggingRow = payload.row;
      this.draggingRowIndex = payload.index;
      payload.event.dataTransfer.effectAllowed = 'copy';
    },
    dragover: function dragover(payload) {
      payload.event.dataTransfer.dropEffect = 'copy';
      payload.event.target.closest('tr').classList.add('is-selected');
      payload.event.preventDefault();
    },
    dragleave: function dragleave(payload) {
      payload.event.target.closest('tr').classList.remove('is-selected');
      payload.event.preventDefault();
    },
    drop: function drop(payload) {
      payload.event.target.closest('tr').classList.remove('is-selected');
      var droppedOnRowIndex = payload.index;
      Object(_helpers__WEBPACK_IMPORTED_MODULE_5__["swapObject"])(this.selectedTeamMatches, this.draggingRowIndex, droppedOnRowIndex);
    },
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
      return Object(_helpers__WEBPACK_IMPORTED_MODULE_5__["isPlayingToHighByName"])(this.playingToHigh, player);
    },
    isPlayingToHighInSquad: function isPlayingToHighInSquad(player) {
      return Object(_helpers__WEBPACK_IMPORTED_MODULE_5__["isPlayingToHighByName"])(this.playingToHighInSquad, player);
    },
    nextStep: function nextStep() {
      this.activeStep = 1;
    },
    highlight: function highlight(player) {
      var base = {};

      if (Object(_helpers__WEBPACK_IMPORTED_MODULE_5__["isPlayingToHighByName"])(this.playingToHigh, player)) {
        base = _objectSpread(_objectSpread({}, {
          'has-background-warning': true
        }), base);
      }

      if (Object(_helpers__WEBPACK_IMPORTED_MODULE_5__["isPlayingToHighByName"])(this.playingToHighInSquad, player)) {
        base = _objectSpread(_objectSpread({}, {
          'has-background-danger': true
        }), base);
      }

      return base;
    },
    findPositions: _helpers__WEBPACK_IMPORTED_MODULE_5__["findPositions"],
    badmintonPlayerTeamMatchesImport: function badmintonPlayerTeamMatchesImport() {
      var _this = this;

      this.fetchingAndValidating = true;
      this.errorImporting = false;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject()),
        variables: {
          input: {
            clubId: parseInt(this.clubId),
            leagueMatchIds: this.castToArray(this.selectedTeamMatches).map(function (teamMatch) {
              return teamMatch.teamMatch.matchId;
            }),
            season: parseInt(this.season),
            version: this.rankingList //"2020-08-01"

          }
        }
      }).then(function (_ref) {
        var data = _ref.data;
        _this.teams = data.badmintonPlayerTeamMatchesImport;

        _this.validate();
      })["catch"](function () {
        _this.$buefy.toast.open({
          duration: 5000,
          message: "Et eller flere hold kunne ikke hentes",
          position: 'is-bottom',
          type: 'is-danger'
        });

        _this.errorImporting = true;
        _this.fetchingAndValidating = false;
      });
    },
    validate: function validate() {
      var _this2 = this;

      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject2()),
        variables: {
          input: omit_deep__WEBPACK_IMPORTED_MODULE_4___default()(this.teams, ['__typename'])
        }
      }).then(function (_ref2) {
        var data = _ref2.data;
        _this2.playingToHigh = data.validateTeamMatch;
        _this2.done = true;
      })["finally"](function () {
        _this2.fetchingAndValidating = false;
      });
    },
    goToStart: function goToStart() {
      this.done = false;
      this.activeStep = 0;
    },
    goToStepTeamsStep: function goToStepTeamsStep() {
      if (!(this.clubId === null || this.season === null)) {
        this.clearTeamFights();
        this.activeStep = 1;
      }
    },
    clearTeams: function clearTeams() {
      this.clearTeamFights();
      this.playerTeams = [];
    },
    clearTeamFights: function clearTeamFights() {
      this.selectedTeamMatches = {};
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=template&id=6323d7ca&scoped=true&":
/*!***************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=template&id=6323d7ca&scoped=true& ***!
  \***************************************************************************************************************************************************************************************************************************************************************/
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
  return _c("b-table", {
    attrs: {
      "checked-rows": _vm.teams,
      columns: _vm.columns,
      data: _vm.badmintonPlayerTeams,
      loading: _vm.$apollo.queries.badmintonPlayerTeams.loading,
      checkable: "",
      "is-row-checkable": _vm.isRowCheckable
    },
    on: {
      "update:checkedRows": function($event) {
        _vm.teams = $event
      },
      "update:checked-rows": function($event) {
        _vm.teams = $event
      }
    },
    scopedSlots: _vm._u([
      {
        key: "empty",
        fn: function() {
          return [
            _c("div", { staticClass: "has-text-centered" }, [
              _vm._v(
                "Ingen hold fundet. Har du valgt den rigtige sæson og klub?"
              )
            ])
          ]
        },
        proxy: true
      }
    ])
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/CheckTeamFight.vue?vue&type=template&id=16be90ea&scoped=true&":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/CheckTeamFight.vue?vue&type=template&id=16be90ea&scoped=true& ***!
  \************************************************************************************************************************************************************************************************************************/
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
      _c("b-loading", {
        attrs: { "is-full-page": true },
        model: {
          value: this.fetchingAndValidating,
          callback: function($$v) {
            _vm.$set(this, "fetchingAndValidating", $$v)
          },
          expression: "this.fetchingAndValidating"
        }
      }),
      _vm._v(" "),
      !_vm.done
        ? _c(
            "form",
            [
              _c(
                "b-steps",
                {
                  model: {
                    value: _vm.activeStep,
                    callback: function($$v) {
                      _vm.activeStep = $$v
                    },
                    expression: "activeStep"
                  }
                },
                [
                  [
                    _c(
                      "b-step-item",
                      { attrs: { label: "Basis" } },
                      [
                        _c(
                          "b-field",
                          { attrs: { label: "Klub" } },
                          [
                            _c("BadmintonPlayerClubs", {
                              on: { input: _vm.clearTeams },
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
                          { attrs: { label: "Sæson" } },
                          [
                            _c(
                              "b-select",
                              {
                                attrs: {
                                  expanded: "",
                                  placeholder: "Vælge sæson"
                                },
                                on: { input: _vm.goToStepTeamsStep },
                                model: {
                                  value: _vm.season,
                                  callback: function($$v) {
                                    _vm.season = $$v
                                  },
                                  expression: "season"
                                }
                              },
                              [
                                _c("option", { attrs: { value: "2020" } }, [
                                  _vm._v("2019/2020")
                                ]),
                                _vm._v(" "),
                                _c("option", { attrs: { value: "2021" } }, [
                                  _vm._v("2021/2022")
                                ])
                              ]
                            )
                          ],
                          1
                        )
                      ],
                      1
                    ),
                    _vm._v(" "),
                    _c(
                      "b-step-item",
                      { attrs: { label: "Hold" } },
                      [
                        _c("h1", { staticClass: "title" }, [_vm._v("Hold")]),
                        _vm._v(" "),
                        _c("h2", { staticClass: "subtitle" }, [
                          _vm._v(
                            "Vælge hvilke hold som skal være med i spillerunden."
                          )
                        ]),
                        _vm._v(" "),
                        _c(
                          "b-message",
                          { attrs: { title: "Vigtig!", type: "is-warning" } },
                          [
                            _vm._v(
                              "\n                        Understøtter pt kun SEN (ikke SEN+XX, UX, 4 spillere hold, div. 1 + Liga). Så alle hold som falder under "
                            ),
                            _c(
                              "a",
                              {
                                attrs: {
                                  href:
                                    "https://badminton.dk/wp-content/uploads/2019/09/Vejledning-for-holds%C3%A6tning-2.-div-og-nedefter-020919.pdf"
                                }
                              },
                              [_vm._v("disse regler")]
                            ),
                            _vm._v(
                              ". Det ligger på roadmap at udvikle de andre, men skriv gerne hvis du vil påvirke prioriteten.\n                    "
                            )
                          ]
                        ),
                        _vm._v(" "),
                        _c("BadmintonPlayerTeamsMultiSelect", {
                          attrs: { clubId: _vm.clubId, season: _vm.season },
                          on: { input: _vm.clearTeamFights },
                          model: {
                            value: _vm.playerTeams,
                            callback: function($$v) {
                              _vm.playerTeams = $$v
                            },
                            expression: "playerTeams"
                          }
                        })
                      ],
                      1
                    ),
                    _vm._v(" "),
                    _c(
                      "b-step-item",
                      { attrs: { label: "Kampe" } },
                      [
                        _c("h1", { staticClass: "title" }, [
                          _vm._v("Rangliste")
                        ]),
                        _vm._v(" "),
                        _c("h2", { staticClass: "subtitle" }, [
                          _vm._v(
                            "§ 38. Den først offentliggjorte rangliste i en ny måned er gældende for holdsætning fra den 10. i den pågældende måned til og med den 9. i den efterfølgende måned. "
                          )
                        ]),
                        _vm._v(" "),
                        _c(
                          "b-field",
                          [
                            _c(
                              "b-select",
                              {
                                attrs: {
                                  expanded: "",
                                  placeholder: "Vælge rangliste"
                                },
                                model: {
                                  value: _vm.rankingList,
                                  callback: function($$v) {
                                    _vm.rankingList = $$v
                                  },
                                  expression: "rankingList"
                                }
                              },
                              [
                                _c(
                                  "option",
                                  { attrs: { value: "2021-10-01" } },
                                  [_vm._v("2021-10-01")]
                                ),
                                _vm._v(" "),
                                _c(
                                  "option",
                                  { attrs: { value: "2021-09-01" } },
                                  [_vm._v("2021-09-01")]
                                ),
                                _vm._v(" "),
                                _c(
                                  "option",
                                  { attrs: { value: "2021-08-01" } },
                                  [_vm._v("2021-08-01")]
                                ),
                                _vm._v(" "),
                                _c(
                                  "option",
                                  { attrs: { value: "2021-07-01" } },
                                  [_vm._v("2021-07-01")]
                                ),
                                _vm._v(" "),
                                _c(
                                  "option",
                                  { attrs: { value: "2020-12-01" } },
                                  [_vm._v("2020-12-01")]
                                ),
                                _vm._v(" "),
                                _c(
                                  "option",
                                  { attrs: { value: "2020-11-01" } },
                                  [_vm._v("2020-11-01")]
                                ),
                                _vm._v(" "),
                                _c(
                                  "option",
                                  { attrs: { value: "2020-10-01" } },
                                  [_vm._v("2020-10-01")]
                                ),
                                _vm._v(" "),
                                _c(
                                  "option",
                                  { attrs: { value: "2020-09-01" } },
                                  [_vm._v("2020-09-01")]
                                ),
                                _vm._v(" "),
                                _c(
                                  "option",
                                  { attrs: { value: "2020-08-01" } },
                                  [_vm._v("2020-08-01")]
                                ),
                                _vm._v(" "),
                                _c(
                                  "option",
                                  { attrs: { value: "2020-07-01" } },
                                  [_vm._v("2020-07-01")]
                                )
                              ]
                            )
                          ],
                          1
                        ),
                        _vm._v(" "),
                        _c("h1", { staticClass: "title" }, [
                          _vm._v("Hold kampe")
                        ]),
                        _vm._v(" "),
                        _c("h2", { staticClass: "subtitle" }, [
                          _vm._v(
                            "Vælge den specifikke hold kamp. Husk ranglisten skal passe med holdkamps runden"
                          )
                        ]),
                        _vm._v(" "),
                        _vm._l(_vm.playerTeams, function(team, index) {
                          return _c(
                            "b-field",
                            {
                              key: team.leagueGroupId + _vm.playerTeams.length,
                              attrs: { label: team.name }
                            },
                            [
                              _c("BadmintonPlayerTeamFights", {
                                attrs: {
                                  clubId: _vm.clubId,
                                  "player-team": team,
                                  season: _vm.season
                                },
                                model: {
                                  value: _vm.selectedTeamMatches[index],
                                  callback: function($$v) {
                                    _vm.$set(
                                      _vm.selectedTeamMatches,
                                      index,
                                      $$v
                                    )
                                  },
                                  expression: "selectedTeamMatches[index]"
                                }
                              })
                            ],
                            1
                          )
                        })
                      ],
                      2
                    ),
                    _vm._v(" "),
                    _c(
                      "b-step-item",
                      { attrs: { label: "Bekræft" } },
                      [
                        _c("h1", { staticClass: "title" }, [
                          _vm._v("Hold sortering")
                        ]),
                        _vm._v(" "),
                        _c("h2", { staticClass: "subtitle" }, [
                          _vm._v(
                            "Sortering er vigtig når spillerunden skal tjekkes. Drag and Drop holdene rundt eller via knapperne, så styrkeordenen passer"
                          )
                        ]),
                        _vm._v(" "),
                        _c(
                          "b-table",
                          {
                            attrs: {
                              data: _vm.castToArray(_vm.selectedTeamMatches),
                              draggable: true
                            },
                            on: {
                              dragstart: _vm.dragstart,
                              drop: _vm.drop,
                              dragover: _vm.dragover,
                              dragleave: _vm.dragleave
                            }
                          },
                          [
                            _c("b-table-column", {
                              attrs: { label: "#", width: "20", numeric: "" },
                              scopedSlots: _vm._u(
                                [
                                  {
                                    key: "default",
                                    fn: function(props) {
                                      return [
                                        _vm._v(
                                          "\n                            " +
                                            _vm._s(props.index + 1) +
                                            "\n                        "
                                        )
                                      ]
                                    }
                                  }
                                ],
                                null,
                                false,
                                27432829
                              )
                            }),
                            _vm._v(" "),
                            _c("b-table-column", {
                              attrs: { field: "team.name", label: "Hold" },
                              scopedSlots: _vm._u(
                                [
                                  {
                                    key: "default",
                                    fn: function(props) {
                                      return [
                                        _vm._v(
                                          "\n                            " +
                                            _vm._s(props.row.team.name) +
                                            "\n                        "
                                        )
                                      ]
                                    }
                                  }
                                ],
                                null,
                                false,
                                2163058057
                              )
                            }),
                            _vm._v(" "),
                            _c("b-table-column", {
                              attrs: { field: "team.league", label: "Række" },
                              scopedSlots: _vm._u(
                                [
                                  {
                                    key: "default",
                                    fn: function(props) {
                                      return [
                                        _vm._v(
                                          "\n                            " +
                                            _vm._s(props.row.team.league) +
                                            "\n                        "
                                        )
                                      ]
                                    }
                                  }
                                ],
                                null,
                                false,
                                3248475505
                              )
                            }),
                            _vm._v(" "),
                            _c("b-table-column", {
                              attrs: {
                                field: "teamMatch.teams",
                                label: "Kamp"
                              },
                              scopedSlots: _vm._u(
                                [
                                  {
                                    key: "default",
                                    fn: function(props) {
                                      return [
                                        _vm._v(
                                          "\n                            " +
                                            _vm._s(
                                              props.row.teamMatch.teams.join(
                                                " - "
                                              )
                                            ) +
                                            " " +
                                            _vm._s(
                                              props.row.teamMatch.gameTime
                                            ) +
                                            "\n                        "
                                        )
                                      ]
                                    }
                                  }
                                ],
                                null,
                                false,
                                2215928449
                              )
                            }),
                            _vm._v(" "),
                            _c("b-table-column", {
                              scopedSlots: _vm._u(
                                [
                                  {
                                    key: "default",
                                    fn: function(props) {
                                      return [
                                        _c(
                                          "b-button",
                                          {
                                            attrs: {
                                              disabled: props.index === 0,
                                              type: "is-success"
                                            },
                                            on: {
                                              click: function($event) {
                                                return _vm.moveUp(props.index)
                                              }
                                            }
                                          },
                                          [_vm._v("Op")]
                                        ),
                                        _vm._v(" "),
                                        _c(
                                          "b-button",
                                          {
                                            attrs: {
                                              disabled: _vm.maybeMoveDown(
                                                props.index
                                              ),
                                              i: "",
                                              type: "is-success"
                                            },
                                            on: {
                                              click: function($event) {
                                                return _vm.moveDown(props.index)
                                              }
                                            }
                                          },
                                          [_vm._v("Ned")]
                                        ),
                                        _vm._v(" "),
                                        _c(
                                          "b-button",
                                          {
                                            attrs: {
                                              tag: "a",
                                              target: "_blank",
                                              href:
                                                "https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5," +
                                                _vm.season +
                                                ",,,,," +
                                                props.row.teamMatch.matchId +
                                                ",,",
                                              type: "is-success"
                                            }
                                          },
                                          [_vm._v("Se på BP")]
                                        )
                                      ]
                                    }
                                  }
                                ],
                                null,
                                false,
                                3475663734
                              )
                            })
                          ],
                          1
                        ),
                        _vm._v(" "),
                        _c("hr"),
                        _vm._v(" "),
                        _c(
                          "b-field",
                          [
                            _c(
                              "b-checkbox",
                              {
                                model: {
                                  value: _vm.sortingConfirmed,
                                  callback: function($$v) {
                                    _vm.sortingConfirmed = $$v
                                  },
                                  expression: "sortingConfirmed"
                                }
                              },
                              [
                                _vm._v(
                                  "Holdene står i den rigtige sortering. (Flyt hold rundt via Drag&Drop eller via knapperne)"
                                )
                              ]
                            )
                          ],
                          1
                        ),
                        _vm._v(" "),
                        _c(
                          "b-button",
                          {
                            attrs: {
                              size: "is-large mt-2",
                              disabled: !_vm.sortingConfirmed
                            },
                            on: { click: _vm.badmintonPlayerTeamMatchesImport }
                          },
                          [_vm._v("Tjek spillerunden")]
                        ),
                        _vm._v(" "),
                        _vm.errorImporting
                          ? _c(
                              "b-message",
                              {
                                staticClass: "mt-2",
                                attrs: {
                                  title: "Fejl ved import",
                                  type: "is-danger"
                                }
                              },
                              [
                                _vm._v(
                                  "\n                        En eller flere hold kunne ikke importeres. Prøv at tjek på badmintonplayer.dk om der er indrapporteret spiller på alle holde?\n                    "
                                )
                              ]
                            )
                          : _vm._e()
                      ],
                      1
                    )
                  ]
                ],
                2
              )
            ],
            1
          )
        : _vm._e(),
      _vm._v(" "),
      _vm.done
        ? _c(
            "b-button",
            { staticClass: "mb-2", on: { click: _vm.goToStart } },
            [_vm._v("Tjek nyt hold")]
          )
        : _vm._e(),
      _vm._v(" "),
      _vm.done && !_vm.hasViolations
        ? _c(
            "b-message",
            {
              attrs: { title: "Fandt ingen overtrædelser", type: "is-success" }
            },
            [_vm._v("\n        Fandt ingen fejl.\n    ")]
          )
        : _vm._e(),
      _vm._v(" "),
      _vm.done && _vm.hasViolations
        ? _c(
            "b-message",
            { attrs: { title: "Fandt overtrædelser", type: "is-warning" } },
            [
              _vm._v(
                "\n        Fandt fejl. Kig efter spillere som er markeret gule eller rød.\n    "
              )
            ]
          )
        : _vm._e(),
      _vm._v(" "),
      _vm.done
        ? _c(
            "div",
            { staticClass: "columns is-multiline" },
            _vm._l(_vm.teams, function(team) {
              return _c(
                "div",
                { staticClass: "column is-4" },
                [
                  _c("h1", { staticClass: "title" }, [
                    _vm._v(_vm._s(team.name))
                  ]),
                  _vm._v(" "),
                  _c(
                    "b-table",
                    { attrs: { data: team.squad.categories } },
                    [
                      _c("b-table-column", {
                        attrs: { field: "name", label: "Kategori" },
                        scopedSlots: _vm._u(
                          [
                            {
                              key: "default",
                              fn: function(props) {
                                return [
                                  _vm._v(
                                    "\n                    " +
                                      _vm._s(props.row.name) +
                                      "\n                "
                                  )
                                ]
                              }
                            }
                          ],
                          null,
                          true
                        )
                      }),
                      _vm._v(" "),
                      _c("b-table-column", {
                        attrs: { field: "players", label: "Spillere" },
                        scopedSlots: _vm._u(
                          [
                            {
                              key: "default",
                              fn: function(props) {
                                return _vm._l(props.row.players, function(
                                  player
                                ) {
                                  return _c(
                                    "b-tooltip",
                                    {
                                      key: player.name + props.row.category,
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
                                        { class: _vm.highlight(player) },
                                        [
                                          _vm._v(
                                            _vm._s(player.name) +
                                              " (" +
                                              _vm._s(
                                                _vm.findPositions(player, "N") +
                                                  " " +
                                                  _vm.findPositions(
                                                    player,
                                                    props.row.category
                                                  )
                                              ) +
                                              ")"
                                          )
                                        ]
                                      )
                                    ]
                                  )
                                })
                              }
                            }
                          ],
                          null,
                          true
                        )
                      })
                    ],
                    1
                  )
                ],
                1
              )
            }),
            0
          )
        : _vm._e()
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue":
/*!**************************************************************************************!*\
  !*** ./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue ***!
  \**************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _BadmintonPlayerTeamsMultiSelect_vue_vue_type_template_id_6323d7ca_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BadmintonPlayerTeamsMultiSelect.vue?vue&type=template&id=6323d7ca&scoped=true& */ "./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=template&id=6323d7ca&scoped=true&");
/* harmony import */ var _BadmintonPlayerTeamsMultiSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./BadmintonPlayerTeamsMultiSelect.vue?vue&type=script&lang=js& */ "./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _BadmintonPlayerTeamsMultiSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _BadmintonPlayerTeamsMultiSelect_vue_vue_type_template_id_6323d7ca_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _BadmintonPlayerTeamsMultiSelect_vue_vue_type_template_id_6323d7ca_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "6323d7ca",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************!*\
  !*** ./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerTeamsMultiSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./BadmintonPlayerTeamsMultiSelect.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerTeamsMultiSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=template&id=6323d7ca&scoped=true&":
/*!*********************************************************************************************************************************!*\
  !*** ./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=template&id=6323d7ca&scoped=true& ***!
  \*********************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerTeamsMultiSelect_vue_vue_type_template_id_6323d7ca_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./BadmintonPlayerTeamsMultiSelect.vue?vue&type=template&id=6323d7ca&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/badminton-player/BadmintonPlayerTeamsMultiSelect.vue?vue&type=template&id=6323d7ca&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerTeamsMultiSelect_vue_vue_type_template_id_6323d7ca_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_BadmintonPlayerTeamsMultiSelect_vue_vue_type_template_id_6323d7ca_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/views/CheckTeamFight.vue":
/*!***********************************************!*\
  !*** ./resources/js/views/CheckTeamFight.vue ***!
  \***********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _CheckTeamFight_vue_vue_type_template_id_16be90ea_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CheckTeamFight.vue?vue&type=template&id=16be90ea&scoped=true& */ "./resources/js/views/CheckTeamFight.vue?vue&type=template&id=16be90ea&scoped=true&");
/* harmony import */ var _CheckTeamFight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CheckTeamFight.vue?vue&type=script&lang=js& */ "./resources/js/views/CheckTeamFight.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _CheckTeamFight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _CheckTeamFight_vue_vue_type_template_id_16be90ea_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _CheckTeamFight_vue_vue_type_template_id_16be90ea_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "16be90ea",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/CheckTeamFight.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/CheckTeamFight.vue?vue&type=script&lang=js&":
/*!************************************************************************!*\
  !*** ./resources/js/views/CheckTeamFight.vue?vue&type=script&lang=js& ***!
  \************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CheckTeamFight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./CheckTeamFight.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/CheckTeamFight.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CheckTeamFight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/CheckTeamFight.vue?vue&type=template&id=16be90ea&scoped=true&":
/*!******************************************************************************************!*\
  !*** ./resources/js/views/CheckTeamFight.vue?vue&type=template&id=16be90ea&scoped=true& ***!
  \******************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CheckTeamFight_vue_vue_type_template_id_16be90ea_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./CheckTeamFight.vue?vue&type=template&id=16be90ea&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/CheckTeamFight.vue?vue&type=template&id=16be90ea&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CheckTeamFight_vue_vue_type_template_id_16be90ea_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CheckTeamFight_vue_vue_type_template_id_16be90ea_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);
//# sourceMappingURL=16.js.map
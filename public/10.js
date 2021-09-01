(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[10],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
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
  name: 'RankingListDatePicker',
  props: ['value'],
  computed: {
    version: {
      get: function get() {
        return this.value;
      },
      set: function set(newValue) {
        this.$emit('input', newValue);
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=script&lang=js&":
/*!******************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=script&lang=js& ***!
  \******************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                    query{\n                        rankingVersions\n                    }\n                "]);

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

/* harmony default export */ __webpack_exports__["default"] = ({
  name: "RankingVersionSelect",
  props: ['value', 'expanded'],
  computed: {
    version: {
      get: function get() {
        return this.value;
      },
      set: function set(newValue) {
        this.$emit('input', newValue);
      }
    }
  },
  apollo: {
    rankingVersions: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject())
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFight.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TeamFight.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_search_player_PlayerSearch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../components/search-player/PlayerSearch */ "./resources/js/components/search-player/PlayerSearch.vue");
/* harmony import */ var _PlayerList__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./PlayerList */ "./resources/js/views/PlayerList.vue");
/* harmony import */ var vuedraggable__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vuedraggable */ "./node_modules/vuedraggable/dist/vuedraggable.common.js");
/* harmony import */ var vuedraggable__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(vuedraggable__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _ValidateTeams__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ValidateTeams */ "./resources/js/views/ValidateTeams.vue");
/* harmony import */ var _TeamTable__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./TeamTable */ "./resources/js/views/TeamTable.vue");
/* harmony import */ var omit_deep__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! omit-deep */ "./node_modules/omit-deep/index.js");
/* harmony import */ var omit_deep__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(omit_deep__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _components_team_fight_teams__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../components/team-fight/teams */ "./resources/js/components/team-fight/teams.js");
/* harmony import */ var _components_team_fight_RankingListDatePicker__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../components/team-fight/RankingListDatePicker */ "./resources/js/components/team-fight/RankingListDatePicker.vue");
/* harmony import */ var _components_team_fight_RankingVersionSelect__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../components/team-fight/RankingVersionSelect */ "./resources/js/components/team-fight/RankingVersionSelect.vue");
function _templateObject7() {
  var data = _taggedTemplateLiteral(["\n                                    mutation($id: ID!){\n                                        notify(id: $id)\n                                    }\n                                "]);

  _templateObject7 = function _templateObject7() {
    return data;
  };

  return data;
}

function _templateObject6() {
  var data = _taggedTemplateLiteral(["\n                                    mutation ($id: ID!){\n                                        deleteTeam(id: $id){\n                                            id\n                                        }\n                                    }\n                                "]);

  _templateObject6 = function _templateObject6() {
    return data;
  };

  return data;
}

function _templateObject5() {
  var data = _taggedTemplateLiteral(["\n                        mutation ($input: UpdateTeamInput!){\n                          updateTeam(input: $input){\n                            id\n                          }\n                        }\n                    "]);

  _templateObject5 = function _templateObject5() {
    return data;
  };

  return data;
}

function _templateObject4() {
  var data = _taggedTemplateLiteral(["\n                        mutation ($input: UpdateTeamInput!){\n                          validate(input: $input){\n                            name\n                            id\n                          }\n                        }\n                    "]);

  _templateObject4 = function _templateObject4() {
    return data;
  };

  return data;
}

function _templateObject3() {
  var data = _taggedTemplateLiteral(["\n                        mutation ($input: UpdateTeamInput!){\n                          validateSquad(input: $input){\n                            name\n                            id\n                          }\n                        }\n                    "]);

  _templateObject3 = function _templateObject3() {
    return data;
  };

  return data;
}

function _templateObject2() {
  var data = _taggedTemplateLiteral(["\n                        mutation ($id: ID!, $version: String!){\n                          updatePoints(id: $id, version: $version)\n                        }\n                    "]);

  _templateObject2 = function _templateObject2() {
    return data;
  };

  return data;
}

function _templateObject() {
  var data = _taggedTemplateLiteral([" query ($id: ID!){\n                  team(id: $id){\n                    id\n                    squads{\n                        id\n                        playerLimit\n                        categories{\n                            category\n                            name\n                            players{\n                                gender\n                                id\n                                name\n                                refId\n                                points{\n                                    category\n                                    points\n                                    position\n                                }\n                            }\n                        }\n                    }\n                    name\n                    gameDate\n                    version\n                    club {\n                        id\n                        name1\n                    }\n                  }\n                }"]);

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
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: "TeamFight",
  components: {
    RankingVersionSelect: _components_team_fight_RankingVersionSelect__WEBPACK_IMPORTED_MODULE_9__["default"],
    RankingListDatePicker: _components_team_fight_RankingListDatePicker__WEBPACK_IMPORTED_MODULE_8__["default"],
    TeamTable: _TeamTable__WEBPACK_IMPORTED_MODULE_5__["default"],
    ValidateTeams: _ValidateTeams__WEBPACK_IMPORTED_MODULE_4__["default"],
    PlayerList: _PlayerList__WEBPACK_IMPORTED_MODULE_1__["default"],
    PlayerSearch: _components_search_player_PlayerSearch__WEBPACK_IMPORTED_MODULE_0__["default"],
    Draggable: vuedraggable__WEBPACK_IMPORTED_MODULE_2___default.a
  },
  props: {
    teamFightId: String
  },
  data: function data() {
    return {
      playingToHighList: [],
      playingToHighSquadList: [],
      teamCount: 1,
      players: [],
      showShareLink: false,
      saving: false,
      updating: false,
      shareUrl: '',
      gameDate: new Date(),
      version: null,
      versionDate: null,
      team: {
        squads: [],
        club: {}
      }
    };
  },
  apollo: {
    team: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject()),
      variables: function variables() {
        return {
          id: this.teamFightId
        };
      },
      fetchPolicy: "network-only",
      result: function result(_ref) {
        var data = _ref.data;
        this.gameDate = new Date(data.team.gameDate);
        this.version = data.team.version;
        this.versionDate = new Date(data.team.version);
      }
    }
  },
  methods: {
    updateToRankingList: function updateToRankingList() {
      var _this = this;

      this.updating = true;
      var version = this.version;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject2()),
        variables: {
          id: this.teamFightId,
          version: version
        }
      }).then(function (_ref2) {
        var data = _ref2.data;

        _this.$apollo.queries.team.refresh();

        _this.$buefy.snackbar.open({
          duration: 4000,
          type: 'is-success',
          message: "Points er nu " + version + ' ranglisten'
        });
      })["catch"](function (error) {
        _this.$buefy.snackbar.open({
          duration: 4000,
          type: 'is-dagner',
          message: "Kunne ikke opdater points :("
        });
      })["finally"](function () {
        _this.updating = false;
      });
    },
    validTeams: function validTeams() {
      var _this2 = this;

      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject3()),
        variables: {
          input: {
            id: this.teamFightId,
            name: this.team.name,
            version: this.version,
            gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
            squads: omit_deep__WEBPACK_IMPORTED_MODULE_6___default()(this.team.squads, ['__typename'])
          }
        }
      }).then(function (_ref3) {
        var data = _ref3.data;
        _this2.playingToHighSquadList = data.validateSquad;
      });
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject4()),
        variables: {
          input: {
            id: this.teamFightId,
            name: this.team.name,
            version: this.version,
            gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
            squads: omit_deep__WEBPACK_IMPORTED_MODULE_6___default()(this.team.squads, ['__typename'])
          }
        }
      }).then(function (_ref4) {
        var data = _ref4.data;
        _this2.playingToHighList = data.validate;
      });
    },
    copyShareLink: function copyShareLink() {
      var _this3 = this;

      this.$copyText(this.shareUrl).then(function (e) {
        _this3.$buefy.snackbar.open("Kopiret til udklipsholder");

        _this3.showShareLink = false;
      }, function (e) {
        _this3.$buefy.snackbar.open("Kunne ikke kopir til udklipsholder. :(");
      });
    },
    publish: function publish() {
      var getUrl = window.location;
      this.shareUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1] + "/" + this.teamFightId + '/view';
      this.showShareLink = !this.showShareLink;
    },
    deletePlayer: function deletePlayer(category, player) {
      category.players.splice(category.players.indexOf(player), 1);
    },
    copyPlayer: function copyPlayer(category, player) {
      category.players.push(Object.assign({}, player));
    },
    deleteTeam: function deleteTeam(team) {
      var _this4 = this;

      this.$buefy.dialog.confirm({
        message: 'Sikker på du vil slette hold ' + (this.team.squads.indexOf(team) + 1) + '?',
        onConfirm: function onConfirm() {
          _this4.team.squads.splice(_this4.team.squads.indexOf(team), 1);
        }
      });
    },
    selectClub: function selectClub(id) {
      this.clubId = id;
    },
    addPlayer: function addPlayer(player) {
      this.players.push(player);
    },
    move: function move(index, offset) {
      var teams = this.team.squads.slice();
      var temp = teams[index];
      teams[index] = teams[index + offset];
      teams[index + offset] = temp;
      this.team.squads = teams;
    },
    addTeam10: function addTeam10() {
      var players = _components_team_fight_teams__WEBPACK_IMPORTED_MODULE_7__["TeamFightHelper"].generate10Players();
      players.id = this.teamCount++;
      this.team.squads.push(players);
    },
    addTeam8: function addTeam8() {
      var players = _components_team_fight_teams__WEBPACK_IMPORTED_MODULE_7__["TeamFightHelper"].generate8Players();
      players.id = this.teamCount++;
      this.team.squads.push(players);
    },
    loadTeamFromCache: function loadTeamFromCache() {
      this.team.squads = JSON.parse(localStorage.getItem('teams'));
    },
    saveTeams: function saveTeams() {
      var _this5 = this;

      localStorage.setItem('teams', JSON.stringify(this.teams));
      this.saving = true;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject5()),
        variables: {
          input: {
            id: this.teamFightId,
            name: this.team.name,
            version: this.version,
            gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
            squads: omit_deep__WEBPACK_IMPORTED_MODULE_6___default()(this.team.squads, ['__typename'])
          }
        }
      }).then(function (_ref5) {
        var data = _ref5.data;
        _this5.saving = false;

        _this5.$buefy.snackbar.open({
          duration: 2000,
          type: 'is-success',
          message: "Dit hold er gemt"
        });
      })["catch"](function (error) {
        _this5.saving = false;

        _this5.$buefy.snackbar.open({
          duration: 2000,
          type: 'is-dagner',
          message: "Kunne ikke gemme dit hold :("
        });
      });
    },
    deleteTeamFight: function deleteTeamFight() {
      var _this6 = this;

      this.$buefy.dialog.confirm({
        message: 'Sikker på du vil slette helt holdet?',
        onConfirm: function onConfirm() {
          _this6.$apollo.mutate({
            mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject6()),
            variables: {
              id: _this6.teamFightId
            }
          }).then(function () {
            _this6.$router.push({
              name: 'team-fight-dashboard'
            });
          });
        }
      });
    },
    notify: function notify() {
      var _this7 = this;

      this.$buefy.dialog.confirm({
        message: 'Sikker på du vil notificer spillerne omkring ændringer?<br /><br /><strong>OSB</strong>: Det er kun spiller som har tilmeldt sig notifikationer, der vil modtage dem.',
        onConfirm: function onConfirm() {
          _this7.$apollo.mutate({
            mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject7()),
            variables: {
              id: _this7.teamFightId
            }
          }).then(function (_ref6) {
            var data = _ref6.data;

            _this7.$buefy.snackbar.open({
              duration: 2000,
              type: 'is-success',
              message: "Dine spiller er nu notificeret"
            });
          })["catch"](function (error) {
            _this7.$buefy.snackbar.open({
              duration: 2000,
              type: 'is-dagner',
              message: "Kunne ikke notificer spillerne"
            });
          });
        }
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/ValidateTeams.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/ValidateTeams.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _helpers__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../helpers */ "./resources/js/helpers.js");
function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'ValidateTeams',
  props: {
    teams: Array
  },
  data: function data() {
    return {
      logs: [],
      showLinesModal: false
    };
  },
  methods: {
    validTeams: function validTeams() {
      var _this = this;

      this.logs = [];
      var limit = 50;
      var lowToHighTeams = this.teams.slice().reverse();
      lowToHighTeams.forEach(function (team, index) {
        var _iterator = _createForOfIteratorHelper(team.categories),
            _step;

        try {
          for (_iterator.s(); !(_step = _iterator.n()).done;) {
            var category = _step.value;

            var _iterator2 = _createForOfIteratorHelper(category.players),
                _step2;

            try {
              for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
                var player = _step2.value;
                var controlTeams = lowToHighTeams.slice();
                controlTeams = controlTeams.slice(1 + index, controlTeams.length);

                var _iterator3 = _createForOfIteratorHelper(controlTeams),
                    _step3;

                try {
                  for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
                    var checkTeam = _step3.value;
                    var players = Object(_helpers__WEBPACK_IMPORTED_MODULE_0__["findPlayersInCategory"])(checkTeam.categories, category.category, player.gender);

                    var _iterator4 = _createForOfIteratorHelper(players),
                        _step4;

                    try {
                      for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
                        var controlPlayer = _step4.value;
                        var controlPlayerLevel = Object(_helpers__WEBPACK_IMPORTED_MODULE_0__["findLevel"])(controlPlayer, category.category);
                        var playerLevel = Object(_helpers__WEBPACK_IMPORTED_MODULE_0__["findLevel"])(player, category.category);

                        if (controlPlayerLevel < playerLevel) {
                          var playerMinusThreshold = playerLevel - limit;

                          if (controlPlayerLevel < playerMinusThreshold) {
                            _this.logs.push(category.category + ': ' + player.name + '(' + playerLevel + '-' + limit + ') has higher level then ' + controlPlayer.name + ' (' + controlPlayerLevel + ')');
                          }
                        }
                      }
                    } catch (err) {
                      _iterator4.e(err);
                    } finally {
                      _iterator4.f();
                    }
                  }
                } catch (err) {
                  _iterator3.e(err);
                } finally {
                  _iterator3.f();
                }
              }
            } catch (err) {
              _iterator2.e(err);
            } finally {
              _iterator2.f();
            }
          }
        } catch (err) {
          _iterator.e(err);
        } finally {
          _iterator.f();
        }
      });

      if (this.logs.length === 0) {
        this.logs.push('Alle hold er gyldig');
      }

      this.showLinesModal = true;
    }
  }
});

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=template&id=3a978a6c&":
/*!***********************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=template&id=3a978a6c& ***!
  \***********************************************************************************************************************************************************************************************************************************/
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
  return _c("b-datepicker", {
    attrs: {
      "first-day-of-week": 1,
      "max-date": new Date(),
      "show-week-number": true,
      icon: "calendar-alt",
      locale: "da-DK",
      placeholder: "Vælge rangliste",
      "trap-focus": ""
    },
    model: {
      value: _vm.version,
      callback: function($$v) {
        _vm.version = $$v
      },
      expression: "version"
    }
  })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=template&id=3dba4992&scoped=true&":
/*!**********************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=template&id=3dba4992&scoped=true& ***!
  \**********************************************************************************************************************************************************************************************************************************************/
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
      attrs: { expanded: _vm.expanded, placeholder: "Vælge rankliste" },
      model: {
        value: _vm.version,
        callback: function($$v) {
          _vm.version = $$v
        },
        expression: "version"
      }
    },
    _vm._l(_vm.rankingVersions, function(version) {
      return _c("option", { key: version, domProps: { value: version } }, [
        _vm._v("\n        " + _vm._s(version) + "\n    ")
      ])
    }),
    0
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFight.vue?vue&type=template&id=69f02fb8&":
/*!*******************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/TeamFight.vue?vue&type=template&id=69f02fb8& ***!
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
    "div",
    [
      _c("b-loading", {
        attrs: { "can-cancel": true, "is-full-page": true },
        model: {
          value: _vm.$apollo.loading || this.updating,
          callback: function($$v) {
            _vm.$set(_vm.$apollo.loading || this, "updating", $$v)
          },
          expression: "$apollo.loading || this.updating"
        }
      }),
      _vm._v(" "),
      _c(
        "b-button",
        {
          attrs: { loading: _vm.saving, "icon-left": "save" },
          on: { click: _vm.saveTeams }
        },
        [_vm._v("Gem")]
      ),
      _vm._v(" "),
      _c(
        "b-button",
        { attrs: { "icon-left": "share-alt" }, on: { click: _vm.publish } },
        [_vm._v("Del")]
      ),
      _vm._v(" "),
      _c(
        "b-button",
        { attrs: { "icon-left": "bell" }, on: { click: _vm.notify } },
        [_vm._v("Notificer")]
      ),
      _vm._v(" "),
      _c(
        "b-dropdown",
        {
          attrs: { "aria-role": "list" },
          scopedSlots: _vm._u([
            {
              key: "trigger",
              fn: function(ref) {
                var active = ref.active
                return _c(
                  "button",
                  { staticClass: "button is-primary" },
                  [
                    _c("span", [_vm._v("Tilføj hold")]),
                    _vm._v(" "),
                    _c("b-icon", {
                      attrs: { icon: active ? "angle-up" : "angle-down" }
                    })
                  ],
                  1
                )
              }
            }
          ])
        },
        [
          _vm._v(" "),
          _c(
            "b-dropdown-item",
            { attrs: { "aria-role": "listitem" }, on: { click: _vm.addTeam8 } },
            [
              _c("b-icon", { attrs: { icon: "users", size: "is-small" } }),
              _vm._v("\n            8 personer\n        ")
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "b-dropdown-item",
            {
              attrs: { "aria-role": "listitem" },
              on: { click: _vm.addTeam10 }
            },
            [
              _c("b-icon", { attrs: { icon: "users", size: "is-small" } }),
              _vm._v("\n            10 personer\n        ")
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "b-dropdown-item",
            { attrs: { "aria-role": "listitem", "has-link": "" } },
            [
              _c(
                "router-link",
                { attrs: { to: "/team-fight/" + _vm.teamFightId + "/import" } },
                [
                  _c("b-icon", { attrs: { icon: "users", size: "is-small" } }),
                  _vm._v(
                    "\n                Import fra BadmintonPlayer\n            "
                  )
                ],
                1
              )
            ],
            1
          )
        ],
        1
      ),
      _vm._v(" "),
      _c("ValidateTeams", {
        ref: "validateTeams",
        attrs: { teams: _vm.team.squads }
      }),
      _vm._v(" "),
      _c(
        "b-dropdown",
        {
          attrs: { "aria-role": "list" },
          scopedSlots: _vm._u([
            {
              key: "trigger",
              fn: function(ref) {
                var active = ref.active
                return _c(
                  "button",
                  { staticClass: "button is-primary" },
                  [
                    _c("span", [_vm._v("Indstillinger")]),
                    _vm._v(" "),
                    _c("b-icon", {
                      attrs: { icon: active ? "angle-up" : "angle-down" }
                    })
                  ],
                  1
                )
              }
            }
          ])
        },
        [
          _vm._v(" "),
          _c(
            "b-dropdown-item",
            {
              attrs: { "aria-role": "listitem" },
              on: { click: _vm.validTeams }
            },
            [
              _c("b-icon", { attrs: { icon: "brain" } }),
              _vm._v("\n            Validere hold\n        ")
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "b-dropdown-item",
            {
              attrs: { "aria-role": "listitem" },
              on: { click: _vm.updateToRankingList }
            },
            [
              _c("b-icon", { attrs: { icon: "brain" } }),
              _vm._v("\n            Update player points\n        ")
            ],
            1
          ),
          _vm._v(" "),
          _c(
            "b-dropdown-item",
            {
              attrs: { "aria-role": "listitem" },
              on: { click: _vm.deleteTeamFight }
            },
            [
              _c("b-icon", { attrs: { icon: "trash" } }),
              _vm._v("\n            Slet holdet\n        ")
            ],
            1
          )
        ],
        1
      ),
      _vm._v(" "),
      _c("div", { staticClass: "columns mt-2" }, [
        _c(
          "div",
          { staticClass: "column" },
          [
            _c(
              "b-field",
              { attrs: { label: "Navn" } },
              [
                _c("b-input", {
                  attrs: { placeholder: "fx. Runde 1" },
                  model: {
                    value: _vm.team.name,
                    callback: function($$v) {
                      _vm.$set(_vm.team, "name", $$v)
                    },
                    expression: "team.name"
                  }
                })
              ],
              1
            )
          ],
          1
        ),
        _vm._v(" "),
        _c(
          "div",
          { staticClass: "column" },
          [
            _c(
              "b-field",
              { attrs: { label: "Spille dato" } },
              [
                _c("b-datepicker", {
                  attrs: {
                    icon: "calendar-alt",
                    locale: "da-DK",
                    placeholder: "Klik for at vælge dato...",
                    "first-day-of-week": 1,
                    "trap-focus": ""
                  },
                  model: {
                    value: _vm.gameDate,
                    callback: function($$v) {
                      _vm.gameDate = $$v
                    },
                    expression: "gameDate"
                  }
                })
              ],
              1
            )
          ],
          1
        ),
        _vm._v(" "),
        _c(
          "div",
          { staticClass: "column" },
          [
            _c(
              "b-field",
              { attrs: { label: "Rangliste" } },
              [
                _c("RankingVersionSelect", {
                  attrs: { expanded: "" },
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
            )
          ],
          1
        ),
        _vm._v(" "),
        _c("div", { staticClass: "column" }, [
          _c("div", { staticClass: "field" }, [
            _c("label", { staticClass: "label" }, [_vm._v("Klub")]),
            _vm._v(" "),
            _c("div", { staticClass: "control is-clearfix" }, [
              _vm._v(
                "\n                    " +
                  _vm._s(_vm.team.club.name1) +
                  "\n                "
              )
            ])
          ])
        ])
      ]),
      _vm._v(" "),
      _c("h1", { staticClass: "title" }, [_vm._v("Holdet")]),
      _vm._v(" "),
      _c("h1", { staticClass: "subtitle" }, [
        _vm._v("Træk spillerne rundt ved at drag-and-drop")
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "columns" }, [
        _c(
          "div",
          { staticClass: "column" },
          [
            _c("PlayerSearch", {
              attrs: {
                "add-player": _vm.addPlayer,
                "club-id": _vm.team.club.id,
                "exclude-players": [],
                version: _vm.versionDate
              }
            })
          ],
          1
        )
      ]),
      _vm._v(" "),
      _c("PlayerList", { attrs: { players: _vm.players } }),
      _vm._v(" "),
      _vm.team.squads.length === 0
        ? _c(
            "div",
            { staticClass: "content has-text-grey has-text-centered" },
            [
              _c(
                "p",
                [_c("b-icon", { attrs: { icon: "users", size: "is-large" } })],
                1
              ),
              _vm._v(" "),
              _c("p", [
                _vm._v("Kom i gang med din næste holdkamp planlægning her")
              ]),
              _vm._v(" "),
              _c(
                "b-button",
                { attrs: { type: "is-primary" }, on: { click: _vm.addTeam8 } },
                [_vm._v("\n            Tilføj 8 personers hold\n        ")]
              ),
              _vm._v(" "),
              _c(
                "b-button",
                { attrs: { type: "is-primary" }, on: { click: _vm.addTeam10 } },
                [_vm._v("\n            Tilføj 10 personers hold\n        ")]
              )
            ],
            1
          )
        : _vm._e(),
      _vm._v(" "),
      _c(
        "draggable",
        {
          staticClass: "columns is-multiline",
          attrs: { list: _vm.team.squads, handle: ".handle" }
        },
        [
          _c("TeamTable", {
            attrs: {
              "confirm-delete": _vm.deleteTeam,
              "copy-player": _vm.copyPlayer,
              "delete-player": _vm.deletePlayer,
              move: _vm.move,
              "playing-to-high": _vm.playingToHighList,
              "playing-to-high-in-squad": _vm.playingToHighSquadList,
              teams: _vm.team.squads
            },
            on: { end: _vm.validTeams }
          })
        ],
        1
      ),
      _vm._v(" "),
      _c(
        "b-modal",
        {
          attrs: { width: 640, scroll: "keep" },
          model: {
            value: _vm.showShareLink,
            callback: function($$v) {
              _vm.showShareLink = $$v
            },
            expression: "showShareLink"
          }
        },
        [
          _c("div", { staticClass: "card" }, [
            _c("div", { staticClass: "card-content" }, [
              _c("div", { staticClass: "content" }, [
                _c("p", [
                  _vm._v(
                    "Alle som har linket kan kun se holdet, ikke rediger. Man behøver ikke at være logget ind for\n                        at se holdet."
                  )
                ]),
                _vm._v(" "),
                _c("pre", [_vm._v(_vm._s(_vm.shareUrl))])
              ])
            ]),
            _vm._v(" "),
            _c("footer", { staticClass: "card-footer" }, [
              _c(
                "a",
                {
                  staticClass: "card-footer-item",
                  attrs: { href: _vm.shareUrl, target: "_blank" }
                },
                [_vm._v("Vis (Nyt vindue)")]
              ),
              _vm._v(" "),
              _c(
                "a",
                {
                  staticClass: "card-footer-item",
                  on: {
                    click: function($event) {
                      $event.preventDefault()
                      return _vm.copyShareLink($event)
                    }
                  }
                },
                [_vm._v("Kopier")]
              ),
              _vm._v(" "),
              _c(
                "a",
                {
                  staticClass: "card-footer-item",
                  on: {
                    click: function($event) {
                      $event.preventDefault()
                      _vm.showShareLink = !_vm.showShareLink
                    }
                  }
                },
                [_vm._v("Luk")]
              )
            ])
          ])
        ]
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/ValidateTeams.vue?vue&type=template&id=16f5489e&":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/ValidateTeams.vue?vue&type=template&id=16f5489e& ***!
  \***********************************************************************************************************************************************************************************************************/
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
    "span",
    [
      _c(
        "b-modal",
        {
          attrs: { width: 940 },
          model: {
            value: _vm.showLinesModal,
            callback: function($$v) {
              _vm.showLinesModal = $$v
            },
            expression: "showLinesModal"
          }
        },
        [
          _vm.logs.length
            ? _c("pre", [_vm._v(_vm._s(_vm.logs.join("\n")))])
            : _vm._e()
        ]
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./resources/js/components/team-fight/RankingListDatePicker.vue":
/*!**********************************************************************!*\
  !*** ./resources/js/components/team-fight/RankingListDatePicker.vue ***!
  \**********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _RankingListDatePicker_vue_vue_type_template_id_3a978a6c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./RankingListDatePicker.vue?vue&type=template&id=3a978a6c& */ "./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=template&id=3a978a6c&");
/* harmony import */ var _RankingListDatePicker_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./RankingListDatePicker.vue?vue&type=script&lang=js& */ "./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _RankingListDatePicker_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _RankingListDatePicker_vue_vue_type_template_id_3a978a6c___WEBPACK_IMPORTED_MODULE_0__["render"],
  _RankingListDatePicker_vue_vue_type_template_id_3a978a6c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/team-fight/RankingListDatePicker.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************!*\
  !*** ./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingListDatePicker_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./RankingListDatePicker.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingListDatePicker_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=template&id=3a978a6c&":
/*!*****************************************************************************************************!*\
  !*** ./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=template&id=3a978a6c& ***!
  \*****************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingListDatePicker_vue_vue_type_template_id_3a978a6c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./RankingListDatePicker.vue?vue&type=template&id=3a978a6c& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/RankingListDatePicker.vue?vue&type=template&id=3a978a6c&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingListDatePicker_vue_vue_type_template_id_3a978a6c___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingListDatePicker_vue_vue_type_template_id_3a978a6c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/team-fight/RankingVersionSelect.vue":
/*!*********************************************************************!*\
  !*** ./resources/js/components/team-fight/RankingVersionSelect.vue ***!
  \*********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _RankingVersionSelect_vue_vue_type_template_id_3dba4992_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./RankingVersionSelect.vue?vue&type=template&id=3dba4992&scoped=true& */ "./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=template&id=3dba4992&scoped=true&");
/* harmony import */ var _RankingVersionSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./RankingVersionSelect.vue?vue&type=script&lang=js& */ "./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _RankingVersionSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _RankingVersionSelect_vue_vue_type_template_id_3dba4992_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _RankingVersionSelect_vue_vue_type_template_id_3dba4992_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "3dba4992",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/team-fight/RankingVersionSelect.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************!*\
  !*** ./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingVersionSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./RankingVersionSelect.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingVersionSelect_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=template&id=3dba4992&scoped=true&":
/*!****************************************************************************************************************!*\
  !*** ./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=template&id=3dba4992&scoped=true& ***!
  \****************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingVersionSelect_vue_vue_type_template_id_3dba4992_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./RankingVersionSelect.vue?vue&type=template&id=3dba4992&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/RankingVersionSelect.vue?vue&type=template&id=3dba4992&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingVersionSelect_vue_vue_type_template_id_3dba4992_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_RankingVersionSelect_vue_vue_type_template_id_3dba4992_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/team-fight/teams.js":
/*!*****************************************************!*\
  !*** ./resources/js/components/team-fight/teams.js ***!
  \*****************************************************/
/*! exports provided: TeamFightHelper */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "TeamFightHelper", function() { return TeamFightHelper; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var TeamFightHelper = /*#__PURE__*/function () {
  function TeamFightHelper() {
    _classCallCheck(this, TeamFightHelper);
  }

  _createClass(TeamFightHelper, null, [{
    key: "generate10Players",
    value: function generate10Players() {
      return {
        playerLimit: 10,
        categories: [{
          name: "1. MD",
          category: "MD",
          players: []
        }, {
          name: "2. MD",
          category: "MD",
          players: []
        }, {
          name: "1. DS",
          category: "DS",
          players: []
        }, {
          name: "2. DS",
          category: "DS",
          players: []
        }, {
          name: "1. HS",
          category: "HS",
          players: []
        }, {
          name: "2. HS",
          category: "HS",
          players: []
        }, {
          name: "3. HS",
          category: "HS",
          players: []
        }, {
          name: "4. HS",
          category: "HS",
          players: []
        }, {
          name: "1. DD",
          category: "DD",
          players: []
        }, {
          name: "2. DD",
          category: "DD",
          players: []
        }, {
          name: "1. HD",
          category: "HD",
          players: []
        }, {
          name: "2. HD",
          category: "HD",
          players: []
        }, {
          name: "3. HD",
          category: "HD",
          players: []
        }]
      };
    }
  }, {
    key: "generate8Players",
    value: function generate8Players() {
      return {
        playerLimit: 8,
        categories: [{
          name: "1. MD",
          category: "MD",
          players: []
        }, {
          name: "2. MD",
          category: "MD",
          players: []
        }, {
          name: "1. DS",
          category: "DS",
          players: []
        }, {
          name: "2. DS",
          category: "DS",
          players: []
        }, {
          name: "1. HS",
          category: "HS",
          players: []
        }, {
          name: "2. HS",
          category: "HS",
          players: []
        }, {
          name: "3. HS",
          category: "HS",
          players: []
        }, {
          name: "4. HS",
          category: "HS",
          players: []
        }, {
          name: "1. DD",
          category: "DD",
          players: []
        }, {
          name: "1. HD",
          category: "HD",
          players: []
        }, {
          name: "2. HD",
          category: "HD",
          players: []
        }]
      };
    }
  }]);

  return TeamFightHelper;
}();

/***/ }),

/***/ "./resources/js/views/TeamFight.vue":
/*!******************************************!*\
  !*** ./resources/js/views/TeamFight.vue ***!
  \******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _TeamFight_vue_vue_type_template_id_69f02fb8___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TeamFight.vue?vue&type=template&id=69f02fb8& */ "./resources/js/views/TeamFight.vue?vue&type=template&id=69f02fb8&");
/* harmony import */ var _TeamFight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TeamFight.vue?vue&type=script&lang=js& */ "./resources/js/views/TeamFight.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _TeamFight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _TeamFight_vue_vue_type_template_id_69f02fb8___WEBPACK_IMPORTED_MODULE_0__["render"],
  _TeamFight_vue_vue_type_template_id_69f02fb8___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/TeamFight.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/TeamFight.vue?vue&type=script&lang=js&":
/*!*******************************************************************!*\
  !*** ./resources/js/views/TeamFight.vue?vue&type=script&lang=js& ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./TeamFight.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFight.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFight_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/TeamFight.vue?vue&type=template&id=69f02fb8&":
/*!*************************************************************************!*\
  !*** ./resources/js/views/TeamFight.vue?vue&type=template&id=69f02fb8& ***!
  \*************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFight_vue_vue_type_template_id_69f02fb8___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./TeamFight.vue?vue&type=template&id=69f02fb8& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/TeamFight.vue?vue&type=template&id=69f02fb8&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFight_vue_vue_type_template_id_69f02fb8___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_TeamFight_vue_vue_type_template_id_69f02fb8___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/views/ValidateTeams.vue":
/*!**********************************************!*\
  !*** ./resources/js/views/ValidateTeams.vue ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _ValidateTeams_vue_vue_type_template_id_16f5489e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ValidateTeams.vue?vue&type=template&id=16f5489e& */ "./resources/js/views/ValidateTeams.vue?vue&type=template&id=16f5489e&");
/* harmony import */ var _ValidateTeams_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ValidateTeams.vue?vue&type=script&lang=js& */ "./resources/js/views/ValidateTeams.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _ValidateTeams_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _ValidateTeams_vue_vue_type_template_id_16f5489e___WEBPACK_IMPORTED_MODULE_0__["render"],
  _ValidateTeams_vue_vue_type_template_id_16f5489e___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/ValidateTeams.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/ValidateTeams.vue?vue&type=script&lang=js&":
/*!***********************************************************************!*\
  !*** ./resources/js/views/ValidateTeams.vue?vue&type=script&lang=js& ***!
  \***********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ValidateTeams_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./ValidateTeams.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/ValidateTeams.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ValidateTeams_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/ValidateTeams.vue?vue&type=template&id=16f5489e&":
/*!*****************************************************************************!*\
  !*** ./resources/js/views/ValidateTeams.vue?vue&type=template&id=16f5489e& ***!
  \*****************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ValidateTeams_vue_vue_type_template_id_16f5489e___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./ValidateTeams.vue?vue&type=template&id=16f5489e& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/ValidateTeams.vue?vue&type=template&id=16f5489e&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ValidateTeams_vue_vue_type_template_id_16f5489e___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ValidateTeams_vue_vue_type_template_id_16f5489e___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ })

}]);
//# sourceMappingURL=10.js.map
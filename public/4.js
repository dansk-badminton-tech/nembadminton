(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[4],{

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
function _templateObject4() {
  var data = _taggedTemplateLiteral(["\n                                    mutation($id: ID!){\n                                        notify(id: $id)\n                                    }\n                                "]);

  _templateObject4 = function _templateObject4() {
    return data;
  };

  return data;
}

function _templateObject3() {
  var data = _taggedTemplateLiteral(["\n                                    mutation ($id: ID!){\n                                        deleteTeam(id: $id){\n                                            id\n                                        }\n                                    }\n                                "]);

  _templateObject3 = function _templateObject3() {
    return data;
  };

  return data;
}

function _templateObject2() {
  var data = _taggedTemplateLiteral(["\n                        mutation ($input: UpdateTeamInput){\n                          updateTeam(input: $input){\n                            id\n                          }\n                        }\n                    "]);

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









/* harmony default export */ __webpack_exports__["default"] = ({
  name: "TeamFight",
  components: {
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
      teamCount: 1,
      players: [],
      showShareLink: false,
      saving: false,
      shareUrl: '',
      gameDate: new Date(),
      version: null,
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
      result: function result(_ref) {
        var data = _ref.data;
        this.gameDate = new Date(data.team.gameDate);
        this.version = new Date(data.team.version);
      }
    }
  },
  methods: {
    copyShareLink: function copyShareLink() {
      var _this = this;

      this.$copyText(this.shareUrl).then(function (e) {
        _this.$buefy.snackbar.open("Kopiret til udklipsholder");

        _this.showShareLink = false;
      }, function (e) {
        _this.$buefy.snackbar.open("Kunne ikke kopir til udklipsholder. :(");
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
      var _this2 = this;

      this.$buefy.dialog.confirm({
        message: 'Sikker på du vil slette hold ' + (this.team.squads.indexOf(team) + 1) + '?',
        onConfirm: function onConfirm() {
          _this2.team.squads.splice(_this2.team.squads.indexOf(team), 1);
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
      var _this3 = this;

      localStorage.setItem('teams', JSON.stringify(this.teams));
      this.saving = true;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject2()),
        variables: {
          input: {
            id: this.teamFightId,
            name: this.team.name,
            version: this.version.getFullYear() + "-" + (this.version.getMonth() + 1) + "-" + this.version.getDate(),
            gameDate: this.gameDate.getFullYear() + "-" + (this.gameDate.getMonth() + 1) + "-" + this.gameDate.getDate(),
            squads: omit_deep__WEBPACK_IMPORTED_MODULE_6___default()(this.team.squads, ['__typename'])
          }
        }
      }).then(function (_ref2) {
        var data = _ref2.data;
        _this3.saving = false;

        _this3.$buefy.snackbar.open({
          duration: 2000,
          type: 'is-success',
          message: "Dit hold er gemt"
        });
      })["catch"](function (error) {
        _this3.saving = false;

        _this3.$buefy.snackbar.open({
          duration: 2000,
          type: 'is-dagner',
          message: "Kunne ikke gemme dit hold :("
        });
      });
    },
    deleteTeamFight: function deleteTeamFight() {
      var _this4 = this;

      this.$buefy.dialog.confirm({
        message: 'Sikker på du vil slette helt holdet?',
        onConfirm: function onConfirm() {
          _this4.$apollo.mutate({
            mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject3()),
            variables: {
              id: _this4.teamFightId
            }
          }).then(function () {
            _this4.$router.push({
              name: 'team-fight-dashboard'
            });
          });
        }
      });
    },
    notify: function notify() {
      var _this5 = this;

      this.$buefy.dialog.confirm({
        message: 'Sikker på du vil notificer spillerne omkring ændringer?<br /><br /><strong>OSB</strong>: Det er kun spiller som har tilmeldt sig notifikationer, der vil modtage dem.',
        onConfirm: function onConfirm() {
          _this5.$apollo.mutate({
            mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_3___default()(_templateObject4()),
            variables: {
              id: _this5.teamFightId
            }
          }).then(function (_ref3) {
            var data = _ref3.data;

            _this5.$buefy.snackbar.open({
              duration: 2000,
              type: 'is-success',
              message: "Dine spiller er nu notificeret"
            });
          })["catch"](function (error) {
            _this5.$buefy.snackbar.open({
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

/***/ "./node_modules/get-value/index.js":
/*!*****************************************!*\
  !*** ./node_modules/get-value/index.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/*!
 * get-value <https://github.com/jonschlinkert/get-value>
 *
 * Copyright (c) 2014-2015, Jon Schlinkert.
 * Licensed under the MIT License.
 */

module.exports = function(obj, prop, a, b, c) {
  if (!isObject(obj) || !prop) {
    return obj;
  }

  prop = toString(prop);

  // allowing for multiple properties to be passed as
  // a string or array, but much faster (3-4x) than doing
  // `[].slice.call(arguments)`
  if (a) prop += '.' + toString(a);
  if (b) prop += '.' + toString(b);
  if (c) prop += '.' + toString(c);

  if (prop in obj) {
    return obj[prop];
  }

  var segs = prop.split('.');
  var len = segs.length;
  var i = -1;

  while (obj && (++i < len)) {
    var key = segs[i];
    while (key[key.length - 1] === '\\') {
      key = key.slice(0, -1) + '.' + segs[++i];
    }
    obj = obj[key];
  }
  return obj;
};

function isObject(val) {
  return val !== null && (typeof val === 'object' || typeof val === 'function');
}

function toString(val) {
  if (!val) return '';
  if (Array.isArray(val)) {
    return val.join('.');
  }
  return val;
}


/***/ }),

/***/ "./node_modules/has-value/index.js":
/*!*****************************************!*\
  !*** ./node_modules/has-value/index.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*!
 * has-value <https://github.com/jonschlinkert/has-value>
 *
 * Copyright (c) 2014-2016, Jon Schlinkert.
 * Licensed under the MIT License.
 */



var isObject = __webpack_require__(/*! isobject */ "./node_modules/has-value/node_modules/isobject/index.js");
var hasValues = __webpack_require__(/*! has-values */ "./node_modules/has-values/index.js");
var get = __webpack_require__(/*! get-value */ "./node_modules/get-value/index.js");

module.exports = function(obj, prop, noZero) {
  if (isObject(obj)) {
    return hasValues(get(obj, prop), noZero);
  }
  return hasValues(obj, prop);
};


/***/ }),

/***/ "./node_modules/has-value/node_modules/isobject/index.js":
/*!***************************************************************!*\
  !*** ./node_modules/has-value/node_modules/isobject/index.js ***!
  \***************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*!
 * isobject <https://github.com/jonschlinkert/isobject>
 *
 * Copyright (c) 2014-2015, Jon Schlinkert.
 * Licensed under the MIT License.
 */



var isArray = __webpack_require__(/*! isarray */ "./node_modules/isarray/index.js");

module.exports = function isObject(val) {
  return val != null && typeof val === 'object' && isArray(val) === false;
};


/***/ }),

/***/ "./node_modules/has-values/index.js":
/*!******************************************!*\
  !*** ./node_modules/has-values/index.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*!
 * has-values <https://github.com/jonschlinkert/has-values>
 *
 * Copyright (c) 2014-2015, Jon Schlinkert.
 * Licensed under the MIT License.
 */



module.exports = function hasValue(o, noZero) {
  if (o === null || o === undefined) {
    return false;
  }

  if (typeof o === 'boolean') {
    return true;
  }

  if (typeof o === 'number') {
    if (o === 0 && noZero === true) {
      return false;
    }
    return true;
  }

  if (o.length !== undefined) {
    return o.length !== 0;
  }

  for (var key in o) {
    if (o.hasOwnProperty(key)) {
      return true;
    }
  }
  return false;
};


/***/ }),

/***/ "./node_modules/is-plain-object/index.js":
/*!***********************************************!*\
  !*** ./node_modules/is-plain-object/index.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*!
 * is-plain-object <https://github.com/jonschlinkert/is-plain-object>
 *
 * Copyright (c) 2014-2017, Jon Schlinkert.
 * Released under the MIT License.
 */



var isObject = __webpack_require__(/*! isobject */ "./node_modules/isobject/index.js");

function isObjectObject(o) {
  return isObject(o) === true
    && Object.prototype.toString.call(o) === '[object Object]';
}

module.exports = function isPlainObject(o) {
  var ctor,prot;

  if (isObjectObject(o) === false) return false;

  // If has modified constructor
  ctor = o.constructor;
  if (typeof ctor !== 'function') return false;

  // If has modified prototype
  prot = ctor.prototype;
  if (isObjectObject(prot) === false) return false;

  // If constructor does not have an Object-specific method
  if (prot.hasOwnProperty('isPrototypeOf') === false) {
    return false;
  }

  // Most likely a plain Object
  return true;
};


/***/ }),

/***/ "./node_modules/isarray/index.js":
/*!***************************************!*\
  !*** ./node_modules/isarray/index.js ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var toString = {}.toString;

module.exports = Array.isArray || function (arr) {
  return toString.call(arr) == '[object Array]';
};


/***/ }),

/***/ "./node_modules/isobject/index.js":
/*!****************************************!*\
  !*** ./node_modules/isobject/index.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*!
 * isobject <https://github.com/jonschlinkert/isobject>
 *
 * Copyright (c) 2014-2017, Jon Schlinkert.
 * Released under the MIT License.
 */



module.exports = function isObject(val) {
  return val != null && typeof val === 'object' && Array.isArray(val) === false;
};


/***/ }),

/***/ "./node_modules/omit-deep/index.js":
/*!*****************************************!*\
  !*** ./node_modules/omit-deep/index.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var isObject = __webpack_require__(/*! is-plain-object */ "./node_modules/is-plain-object/index.js");
var unset = __webpack_require__(/*! unset-value */ "./node_modules/unset-value/index.js");

module.exports = function omitDeep(value, keys) {
  if (typeof value === 'undefined') {
    return {};
  }

  if (Array.isArray(value)) {
    for (var i = 0; i < value.length; i++) {
      value[i] = omitDeep(value[i], keys);
    }
    return value;
  }

  if (!isObject(value)) {
    return value;
  }

  if (typeof keys === 'string') {
    keys = [keys];
  }

  if (!Array.isArray(keys)) {
    return value;
  }

  for (var j = 0; j < keys.length; j++) {
    unset(value, keys[j]);
  }

  for (var key in value) {
    if (value.hasOwnProperty(key)) {
      value[key] = omitDeep(value[key], keys);
    }
  }

  return value;
};


/***/ }),

/***/ "./node_modules/unset-value/index.js":
/*!*******************************************!*\
  !*** ./node_modules/unset-value/index.js ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*!
 * unset-value <https://github.com/jonschlinkert/unset-value>
 *
 * Copyright (c) 2015, 2017, Jon Schlinkert.
 * Released under the MIT License.
 */



var isObject = __webpack_require__(/*! isobject */ "./node_modules/isobject/index.js");
var has = __webpack_require__(/*! has-value */ "./node_modules/has-value/index.js");

module.exports = function unset(obj, prop) {
  if (!isObject(obj)) {
    throw new TypeError('expected an object.');
  }
  if (obj.hasOwnProperty(prop)) {
    delete obj[prop];
    return true;
  }

  if (has(obj, prop)) {
    var segs = prop.split('.');
    var last = segs.pop();
    while (segs.length && segs[segs.length - 1].slice(-1) === '\\') {
      last = segs.pop().slice(0, -1) + '.' + last;
    }
    while (segs.length) obj = obj[prop = segs.shift()];
    return (delete obj[last]);
  }
  return true;
};


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
      "unselectable-days-of-week": [0, 2, 4, 6],
      icon: "calendar-alt",
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
          value: _vm.$apollo.loading,
          callback: function($$v) {
            _vm.$set(_vm.$apollo, "loading", $$v)
          },
          expression: "$apollo.loading"
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
              on: {
                click: function($event) {
                  return _vm.$refs.validateTeams.validTeams()
                }
              }
            },
            [
              _c("b-icon", { attrs: { icon: "brain" } }),
              _vm._v("\n            Validere hold (eksperimentel)\n        ")
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
                _c("RankingListDatePicker", {
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
                version: _vm.version
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
              teams: _vm.team.squads
            }
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

      if (point.category === 'MxH' && (show === 'all' || show === 'MxH')) {
        summary.push('MxH:' + point.points);
      }

      if (point.category === 'MxD' && (show === 'all' || show === 'MxD')) {
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
//# sourceMappingURL=4.js.map
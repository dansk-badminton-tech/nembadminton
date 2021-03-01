(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[6],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/notification/Notification.vue?vue&type=script&lang=js&":
/*!************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/notification/Notification.vue?vue&type=script&lang=js& ***!
  \************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _service_worker__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../service-worker */ "./resources/js/service-worker.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _views_ChangeProfile__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../views/ChangeProfile */ "./resources/js/views/ChangeProfile.vue");
function _templateObject2() {
  var data = _taggedTemplateLiteral(["\n                                mutation updateMe($input: UpdateMe!){\n                                    updateMe(input: $input){\n                                        id\n                                        name\n                                        email\n                                        subscriptionSettings{\n                                            id\n                                            email\n                                        }\n                                    }\n                                }\n                            "]);

  _templateObject2 = function _templateObject2() {
    return data;
  };

  return data;
}

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                query {\n                    me{\n                        id\n                        email\n                        player_id\n                        subscriptionSettings{\n                            id\n                            email\n                        }\n                    }\n                }\n            "]);

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



/* harmony default export */ __webpack_exports__["default"] = ({
  name: "Notification",
  components: {
    ChangeProfile: _views_ChangeProfile__WEBPACK_IMPORTED_MODULE_2__["default"]
  },
  data: function data() {
    return {
      isPushEnabled: false,
      pushButtonDisabled: false,
      subscribingWebPushLoading: false,
      unsubscribingWebPushLoading: false,
      updateSubscriptionEmailLoading: false,
      isPushPossible: true
    };
  },
  computed: {
    isEmailSubscribed: function isEmailSubscribed() {
      var _this$me$subscription;

      return ((_this$me$subscription = this.me.subscriptionSettings) === null || _this$me$subscription === void 0 ? void 0 : _this$me$subscription.email) || false;
    },
    hasBadmintonId: function hasBadmintonId() {
      var _this$me;

      return ((_this$me = this.me) === null || _this$me === void 0 ? void 0 : _this$me.player_id) || false;
    }
  },
  mounted: function mounted() {
    var _this = this;

    var serviceWorker = _service_worker__WEBPACK_IMPORTED_MODULE_0__["ServiceWorkerHelper"].registerServiceWorker();

    if (serviceWorker) {
      serviceWorker.then(function (registration) {
        registration.pushManager.getSubscription().then(function (subscription) {
          _this.pushButtonDisabled = false;

          if (!subscription) {
            return;
          }

          _service_worker__WEBPACK_IMPORTED_MODULE_0__["ServiceWorkerHelper"].updateSubscription(subscription);
          _this.isPushEnabled = true;
        })["catch"](function (e) {
          console.log('Error during getSubscription()', e);
        });
      });
    } else {
      this.isPushPossible = false;
    }
  },
  apollo: {
    me: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_1___default()(_templateObject())
    }
  },
  methods: {
    subscribe: function subscribe() {
      var _this2 = this;

      this.subscribingWebPushLoading = true;
      _service_worker__WEBPACK_IMPORTED_MODULE_0__["ServiceWorkerHelper"].subscribe().then(function (subscription) {
        _this2.isPushEnabled = true;
        _service_worker__WEBPACK_IMPORTED_MODULE_0__["ServiceWorkerHelper"].updateSubscription(subscription);
      })["finally"](function () {
        _this2.subscribingWebPushLoading = false;
      });
    },
    unsubscribe: function unsubscribe() {
      var _this3 = this;

      this.unsubscribingWebPushLoading = true;
      _service_worker__WEBPACK_IMPORTED_MODULE_0__["ServiceWorkerHelper"].unsubscribe().then(function (subscription) {
        if (!subscription) {
          _this3.isPushEnabled = false;
          return;
        }

        subscription.unsubscribe().then(function () {
          _service_worker__WEBPACK_IMPORTED_MODULE_0__["ServiceWorkerHelper"].deleteSubscription(subscription);
          _this3.isPushEnabled = false;
        })["catch"](function (e) {
          console.log('Unsubscription error: ', e);
          _this3.pushButtonDisabled = false;
        })["finally"](function () {
          _this3.unsubscribingWebPushLoading = false;
        });
      });
    },
    emailSubscribe: function emailSubscribe(subscribe) {
      var _this$me$subscription2,
          _this4 = this;

      this.unsubscribingEmailLoading = true;
      var variables = {
        input: {
          subscriptionSettings: {
            upsert: {
              email: subscribe
            }
          }
        }
      };

      if ((_this$me$subscription2 = this.me.subscriptionSettings) !== null && _this$me$subscription2 !== void 0 && _this$me$subscription2.id) {
        variables.input.subscriptionSettings.upsert.id = this.me.subscriptionSettings.id;
      }

      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_1___default()(_templateObject2()),
        variables: variables
      }).then(function () {
        _this4.$apollo.queries.me.refresh();
      })["finally"](function () {
        _this4.updateSubscriptionEmailLoading = false;
      });
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/CreateNotification.vue?vue&type=script&lang=js&":
/*!****************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/team-fight/CreateNotification.vue?vue&type=script&lang=js& ***!
  \****************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _notification_Notification__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../notification/Notification */ "./resources/js/components/notification/Notification.vue");
/* harmony import */ var _views_CreateUser__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../views/CreateUser */ "./resources/js/views/CreateUser.vue");
/* harmony import */ var _auth__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../auth */ "./resources/js/auth.js");
/* harmony import */ var _views_Login__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../views/Login */ "./resources/js/views/Login.vue");
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
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
  name: "CreateNotification",
  components: {
    Login: _views_Login__WEBPACK_IMPORTED_MODULE_3__["default"],
    CreateUser: _views_CreateUser__WEBPACK_IMPORTED_MODULE_1__["default"],
    Notification: _notification_Notification__WEBPACK_IMPORTED_MODULE_0__["default"]
  },
  data: function data() {
    return {
      email: '',
      loggedIn: false,
      showCreateUser: false,
      showLogin: false
    };
  },
  mounted: function mounted() {
    this.loggedIn = Object(_auth__WEBPACK_IMPORTED_MODULE_2__["isLoggedIn"])();
  },
  methods: {
    refresh: function refresh() {
      this.loggedIn = true;
      this.showLogin = false;
      this.showCreateUser = false;
    }
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/ChangeProfile.vue?vue&type=script&lang=js&":
/*!*******************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/ChangeProfile.vue?vue&type=script&lang=js& ***!
  \*******************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
function _templateObject2() {
  var data = _taggedTemplateLiteral(["\n                        mutation updateMe($input: UpdateMe!){\n                            updateMe(input: $input){\n                                id\n                                name\n                                email\n                                player_id\n                            }\n                        }\n                    "]);

  _templateObject2 = function _templateObject2() {
    return data;
  };

  return data;
}

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                query{\n                    me{\n                        id\n                        email\n                        name\n                        player_id\n                    }\n                }"]);

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

/* harmony default export */ __webpack_exports__["default"] = ({
  name: 'ChangeProfile',
  apollo: {
    me: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject()),
      fetchPolicy: "network-only"
    }
  },
  props: {
    onlyBadmintonId: Boolean
  },
  methods: {
    update: function update() {
      var _this = this;

      this.updatingProfile = true;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject2()),
        variables: {
          input: {
            name: this.me.name,
            email: this.me.email,
            player_id: this.me.player_id
          }
        }
      }).then(function () {
        _this.$root.$emit('userUpdated');

        _this.$buefy.snackbar.open({
          duration: 2000,
          type: 'is-success',
          message: "Din profil er nu opdateret"
        });
      })["finally"](function () {
        _this.updatingProfile = false;
      });
    }
  },
  data: function data() {
    return {
      me: {
        name: '',
        email: ''
      },
      updatingProfile: false
    };
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




/* harmony default export */ __webpack_exports__["default"] = ({
  name: "CreateUser",
  components: {
    ClubSearch: _components_search_club_ClubSearch__WEBPACK_IMPORTED_MODULE_3__["default"]
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
    selectClub: function selectClub(clubId) {
      this.clubId = clubId;
    },
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
            name: 'home'
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

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/Login.vue?vue&type=script&lang=js&":
/*!***********************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/Login.vue?vue&type=script&lang=js& ***!
  \***********************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _auth__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../auth */ "./resources/js/auth.js");
function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                        mutation ($input: LoginInput){\n                          login(input: $input){\n                            access_token\n                          }\n                        }\n                    "]);

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


/* harmony default export */ __webpack_exports__["default"] = ({
  name: "Login",
  data: function data() {
    return {
      email: null,
      password: null,
      loading: false
    };
  },
  props: {
    afterLogin: Function
  },
  methods: {
    login: function login() {
      var _this = this;

      this.loading = true;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject()),
        variables: {
          input: {
            username: this.email,
            password: this.password
          }
        }
      }).then(function (_ref) {
        var data = _ref.data;
        Object(_auth__WEBPACK_IMPORTED_MODULE_1__["setAuthToken"])(data.login.access_token);

        _this.$root.$emit('loggedIn');

        if (_this.afterLogin instanceof Function) {
          _this.afterLogin();
        } else {
          _this.$router.push({
            name: 'team-fight-dashboard'
          });
        }
      })["catch"](function (_ref2) {
        var graphQLErrors = _ref2.graphQLErrors;

        _this.$buefy.snackbar.open({
          duration: 6000,
          type: 'is-danger',
          message: "Forkert brugernavn eller adgangskode."
        });
      })["finally"](function () {
        _this.loading = false;
      });
    }
  }
});

/***/ }),

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
/* harmony import */ var _components_team_fight_CreateNotification__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../components/team-fight/CreateNotification */ "./resources/js/components/team-fight/CreateNotification.vue");
function _templateObject2() {
  var data = _taggedTemplateLiteral([" query ($id: ID!){\n                  team(id: $id){\n                    id\n                    name\n                    gameDate\n                    squads{\n                        id\n                        playerLimit\n                        categories{\n                            category\n                            name\n                            players{\n                                gender\n                                id\n                                name\n                                refId\n                                points{\n                                    category\n                                    points\n                                    position\n                                }\n                            }\n                        }\n                    }\n                    club {\n                        id\n                        name1\n                    }\n                  }\n                }"]);

  _templateObject2 = function _templateObject2() {
    return data;
  };

  return data;
}

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                                    mutation{\n                                        sendNotification\n                                    }\n                                "]);

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



/* harmony default export */ __webpack_exports__["default"] = ({
  name: "TeamFightPublic",
  components: {
    CreateNotification: _components_team_fight_CreateNotification__WEBPACK_IMPORTED_MODULE_2__["default"],
    TeamTable: _TeamTable__WEBPACK_IMPORTED_MODULE_1__["default"]
  },
  methods: {
    sendNotification: function sendNotification() {
      this.loading = true;
      this.$apollo.mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject())
      });
    }
  },
  data: function data() {
    return {
      searchPlayer: '',
      showNotificationPopUp: false
    };
  },
  props: {
    teamFightId: String
  },
  apollo: {
    team: {
      query: graphql_tag__WEBPACK_IMPORTED_MODULE_0___default()(_templateObject2()),
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

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/notification/Notification.vue?vue&type=template&id=b968a718&scoped=true&":
/*!****************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/notification/Notification.vue?vue&type=template&id=b968a718&scoped=true& ***!
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
  return _c("fragment", [
    !_vm.$apollo.loading && !_vm.hasBadmintonId
      ? _c(
          "div",
          [
            _c("p", [
              _vm._v(
                "For at notificationer virker skal du angive dit Badminton ID"
              )
            ]),
            _vm._v(" "),
            _c("ChangeProfile", { attrs: { "only-badminton-id": true } })
          ],
          1
        )
      : _vm._e(),
    _vm._v(" "),
    !_vm.$apollo.loading && _vm.hasBadmintonId
      ? _c(
          "div",
          [
            _c("h1", { staticClass: "title is-5" }, [_vm._v("Web Push")]),
            _vm._v(" "),
            !_vm.isPushEnabled
              ? _c(
                  "b-button",
                  {
                    attrs: {
                      loading: this.subscribingWebPushLoading,
                      disabled: !_vm.isPushPossible,
                      type: "is-primary"
                    },
                    on: { click: _vm.subscribe }
                  },
                  [_vm._v("\n            Aktiver\n        ")]
                )
              : _vm._e(),
            _vm._v(" "),
            _vm.isPushEnabled
              ? _c(
                  "b-button",
                  {
                    attrs: {
                      disabled: !_vm.isPushPossible,
                      loading: this.unsubscribingWebPushLoading,
                      type: "is-primary"
                    },
                    on: { click: _vm.unsubscribe }
                  },
                  [_vm._v("\n            Deaktiver\n        ")]
                )
              : _vm._e(),
            _vm._v(" "),
            _c("p", { staticClass: "mt-2" }, [
              _vm._v("Personlig push beskeder direkte til dig fra din browser.")
            ]),
            _vm._v(" "),
            _c("hr"),
            _vm._v(" "),
            _c("h1", { staticClass: "title is-5" }, [_vm._v("Email")]),
            _vm._v(" "),
            !_vm.$apollo.loading && !_vm.isEmailSubscribed
              ? _c(
                  "b-button",
                  {
                    attrs: {
                      loading:
                        _vm.$apollo.queries.me.loading ||
                        _vm.updateSubscriptionEmailLoading,
                      type: "is-primary"
                    },
                    on: {
                      click: function($event) {
                        return _vm.emailSubscribe(true)
                      }
                    }
                  },
                  [_vm._v("\n            Aktiver\n        ")]
                )
              : _vm._e(),
            _vm._v(" "),
            !_vm.$apollo.loading && _vm.isEmailSubscribed
              ? _c(
                  "b-button",
                  {
                    attrs: {
                      loading:
                        _vm.$apollo.queries.me.loading ||
                        _vm.updateSubscriptionEmailLoading,
                      type: "is-primary"
                    },
                    on: {
                      click: function($event) {
                        return _vm.emailSubscribe(false)
                      }
                    }
                  },
                  [_vm._v("\n            Deaktiver\n        ")]
                )
              : _vm._e(),
            _vm._v(" "),
            !_vm.$apollo.loading
              ? _c("p", { staticClass: "mt-2" }, [
                  _vm._v("Sender til: "),
                  _c("strong", [_vm._v(_vm._s(_vm.me.email))])
                ])
              : _vm._e()
          ],
          1
        )
      : _vm._e()
  ])
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/CreateNotification.vue?vue&type=template&id=8083fe8a&scoped=true&":
/*!********************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/team-fight/CreateNotification.vue?vue&type=template&id=8083fe8a&scoped=true& ***!
  \********************************************************************************************************************************************************************************************************************************************/
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
    "form",
    {
      on: {
        submit: function($event) {
          $event.preventDefault()
        }
      }
    },
    [
      _c("div", { staticClass: "modal-card", staticStyle: { width: "auto" } }, [
        _c("header", { staticClass: "modal-card-head" }, [
          _c("p", { staticClass: "modal-card-title" }, [
            _vm._v("Notifikationer")
          ]),
          _vm._v(" "),
          _c("button", {
            staticClass: "delete",
            attrs: { type: "button" },
            on: {
              click: function($event) {
                return _vm.$parent.close()
              }
            }
          })
        ]),
        _vm._v(" "),
        _c(
          "section",
          { staticClass: "modal-card-body" },
          [
            !_vm.loggedIn
              ? _c(
                  "div",
                  [
                    _c("p", { staticClass: "mb-2" }, [
                      _vm._v(
                        "Tilmeld dig og få notifikationer når der sker ændringer."
                      )
                    ]),
                    _vm._v(" "),
                    _c("hr"),
                    _vm._v(" "),
                    _c(
                      "div",
                      { staticClass: "buttons" },
                      [
                        _vm.showLogin || _vm.showCreateUser
                          ? _c(
                              "b-button",
                              {
                                attrs: { expanded: "" },
                                on: {
                                  click: function($event) {
                                    _vm.showLogin = false
                                    _vm.showCreateUser = false
                                  }
                                }
                              },
                              [_vm._v("Tilbage")]
                            )
                          : _vm._e(),
                        _vm._v(" "),
                        !(_vm.showLogin || _vm.showCreateUser)
                          ? _c(
                              "b-button",
                              {
                                attrs: { expanded: "" },
                                on: {
                                  click: function($event) {
                                    _vm.showLogin = !_vm.showLogin
                                  }
                                }
                              },
                              [_vm._v("Login")]
                            )
                          : _vm._e(),
                        _vm._v(" "),
                        !(_vm.showLogin || _vm.showCreateUser)
                          ? _c(
                              "b-button",
                              {
                                attrs: { expanded: "" },
                                on: {
                                  click: function($event) {
                                    _vm.showCreateUser = !_vm.showCreateUser
                                  }
                                }
                              },
                              [_vm._v("Opret bruger")]
                            )
                          : _vm._e()
                      ],
                      1
                    ),
                    _vm._v(" "),
                    _vm.showCreateUser
                      ? _c("create-user", {
                          attrs: { "after-register": _vm.refresh }
                        })
                      : _vm._e(),
                    _vm._v(" "),
                    _vm.showLogin
                      ? _c("login", { attrs: { "after-login": _vm.refresh } })
                      : _vm._e()
                  ],
                  1
                )
              : _vm._e(),
            _vm._v(" "),
            _vm.loggedIn ? _c("notification") : _vm._e()
          ],
          1
        ),
        _vm._v(" "),
        _vm.loggedIn
          ? _c(
              "footer",
              { staticClass: "modal-card-foot" },
              [
                _c("b-button", {
                  attrs: { label: "Luk" },
                  on: {
                    click: function($event) {
                      return _vm.$parent.close()
                    }
                  }
                })
              ],
              1
            )
          : _vm._e()
      ])
    ]
  )
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/ChangeProfile.vue?vue&type=template&id=7cff9e6c&":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/ChangeProfile.vue?vue&type=template&id=7cff9e6c& ***!
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
    "form",
    {
      on: {
        submit: function($event) {
          $event.preventDefault()
        }
      }
    },
    [
      !_vm.onlyBadmintonId
        ? _c(
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
          )
        : _vm._e(),
      _vm._v(" "),
      !_vm.onlyBadmintonId
        ? _c(
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
          )
        : _vm._e(),
      _vm._v(" "),
      _c(
        "b-field",
        { attrs: { label: "Badminton ID" } },
        [
          _c("b-input", {
            attrs: { placeholder: "ÅÅMMDD-XX", type: "text" },
            model: {
              value: _vm.me.player_id,
              callback: function($$v) {
                _vm.$set(_vm.me, "player_id", $$v)
              },
              expression: "me.player_id"
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
        "b-button",
        {
          staticClass: "mt-2",
          attrs: { loading: _vm.updatingProfile },
          on: { click: _vm.update }
        },
        [_vm._v("Gem")]
      ),
      _vm._v(" "),
      _c("div", { staticClass: "mt-4" })
    ],
    1
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
      _c("ClubSearch", { attrs: { "select-club": _vm.selectClub } }),
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

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/Login.vue?vue&type=template&id=12f5395a&scoped=true&":
/*!***************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/Login.vue?vue&type=template&id=12f5395a&scoped=true& ***!
  \***************************************************************************************************************************************************************************************************************/
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
    "form",
    {
      staticClass: "mt-2",
      on: {
        submit: function($event) {
          $event.preventDefault()
          return _vm.login($event)
        }
      }
    },
    [
      _c(
        "b-field",
        { attrs: { label: "Email" } },
        [
          _c("b-input", {
            attrs: { type: "email" },
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
        { attrs: { label: "Adgangskode" } },
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
        "b-button",
        { attrs: { loading: _vm.loading, "native-type": "submit" } },
        [_vm._v("Login")]
      )
    ],
    1
  )
}
var staticRenderFns = []
render._withStripped = true



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
        "fragment",
        [
          _c("h1", { staticClass: "title" }, [
            _vm._v(_vm._s(_vm.team.name) + " - " + _vm._s(_vm.team.club.name1))
          ]),
          _vm._v(" "),
          _c("h2", { staticClass: "subtitle" }, [
            _vm._v("Spille dato: " + _vm._s(_vm.team.gameDate))
          ]),
          _vm._v(" "),
          _c("b-button", {
            attrs: {
              label: "Notifikationer",
              size: "is-medium",
              type: "is-primary"
            },
            on: {
              click: function($event) {
                _vm.showNotificationPopUp = true
              }
            }
          }),
          _vm._v(" "),
          _c("b-modal", {
            attrs: {
              "destroy-on-hide": false,
              "aria-label": "Notifikation",
              "aria-modal": "",
              "aria-role": "dialog",
              "has-modal-card": "",
              "trap-focus": ""
            },
            scopedSlots: _vm._u(
              [
                {
                  key: "default",
                  fn: function(props) {
                    return [_c("CreateNotification")]
                  }
                }
              ],
              null,
              false,
              224635944
            ),
            model: {
              value: _vm.showNotificationPopUp,
              callback: function($$v) {
                _vm.showNotificationPopUp = $$v
              },
              expression: "showNotificationPopUp"
            }
          }),
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

/***/ "./resources/js/components/notification/Notification.vue":
/*!***************************************************************!*\
  !*** ./resources/js/components/notification/Notification.vue ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Notification_vue_vue_type_template_id_b968a718_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Notification.vue?vue&type=template&id=b968a718&scoped=true& */ "./resources/js/components/notification/Notification.vue?vue&type=template&id=b968a718&scoped=true&");
/* harmony import */ var _Notification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Notification.vue?vue&type=script&lang=js& */ "./resources/js/components/notification/Notification.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _Notification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Notification_vue_vue_type_template_id_b968a718_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _Notification_vue_vue_type_template_id_b968a718_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "b968a718",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/notification/Notification.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/notification/Notification.vue?vue&type=script&lang=js&":
/*!****************************************************************************************!*\
  !*** ./resources/js/components/notification/Notification.vue?vue&type=script&lang=js& ***!
  \****************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./Notification.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/notification/Notification.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/notification/Notification.vue?vue&type=template&id=b968a718&scoped=true&":
/*!**********************************************************************************************************!*\
  !*** ./resources/js/components/notification/Notification.vue?vue&type=template&id=b968a718&scoped=true& ***!
  \**********************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_template_id_b968a718_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./Notification.vue?vue&type=template&id=b968a718&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/notification/Notification.vue?vue&type=template&id=b968a718&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_template_id_b968a718_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Notification_vue_vue_type_template_id_b968a718_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/team-fight/CreateNotification.vue":
/*!*******************************************************************!*\
  !*** ./resources/js/components/team-fight/CreateNotification.vue ***!
  \*******************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _CreateNotification_vue_vue_type_template_id_8083fe8a_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CreateNotification.vue?vue&type=template&id=8083fe8a&scoped=true& */ "./resources/js/components/team-fight/CreateNotification.vue?vue&type=template&id=8083fe8a&scoped=true&");
/* harmony import */ var _CreateNotification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CreateNotification.vue?vue&type=script&lang=js& */ "./resources/js/components/team-fight/CreateNotification.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _CreateNotification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _CreateNotification_vue_vue_type_template_id_8083fe8a_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _CreateNotification_vue_vue_type_template_id_8083fe8a_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "8083fe8a",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/team-fight/CreateNotification.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/team-fight/CreateNotification.vue?vue&type=script&lang=js&":
/*!********************************************************************************************!*\
  !*** ./resources/js/components/team-fight/CreateNotification.vue?vue&type=script&lang=js& ***!
  \********************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateNotification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/babel-loader/lib??ref--4-0!../../../../node_modules/vue-loader/lib??vue-loader-options!./CreateNotification.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/CreateNotification.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateNotification_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/team-fight/CreateNotification.vue?vue&type=template&id=8083fe8a&scoped=true&":
/*!**************************************************************************************************************!*\
  !*** ./resources/js/components/team-fight/CreateNotification.vue?vue&type=template&id=8083fe8a&scoped=true& ***!
  \**************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateNotification_vue_vue_type_template_id_8083fe8a_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../../node_modules/vue-loader/lib??vue-loader-options!./CreateNotification.vue?vue&type=template&id=8083fe8a&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/team-fight/CreateNotification.vue?vue&type=template&id=8083fe8a&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateNotification_vue_vue_type_template_id_8083fe8a_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_CreateNotification_vue_vue_type_template_id_8083fe8a_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/service-worker.js":
/*!****************************************!*\
  !*** ./resources/js/service-worker.js ***!
  \****************************************/
/*! exports provided: ServiceWorkerHelper */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "ServiceWorkerHelper", function() { return ServiceWorkerHelper; });
/* harmony import */ var _graphql__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./graphql */ "./resources/js/graphql.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! graphql-tag */ "./node_modules/graphql-tag/src/index.js");
/* harmony import */ var graphql_tag__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(graphql_tag__WEBPACK_IMPORTED_MODULE_1__);
function _templateObject2() {
  var data = _taggedTemplateLiteral(["\n                                            mutation($input: UnsubscribeWebPushInput!){\n                                                unsubscribeWebPush(input: $input)\n                                            }\n                                        "]);

  _templateObject2 = function _templateObject2() {
    return data;
  };

  return data;
}

function _templateObject() {
  var data = _taggedTemplateLiteral(["\n                                            mutation($input: SubscribeWebPushInput!){\n                                                subscribeWebPush(input: $input){\n                                                    id\n                                                }\n                                            }\n                                        "]);

  _templateObject = function _templateObject() {
    return data;
  };

  return data;
}

function _taggedTemplateLiteral(strings, raw) { if (!raw) { raw = strings.slice(0); } return Object.freeze(Object.defineProperties(strings, { raw: { value: Object.freeze(raw) } })); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var ServiceWorkerHelper = /*#__PURE__*/function () {
  function ServiceWorkerHelper() {
    _classCallCheck(this, ServiceWorkerHelper);
  }

  _createClass(ServiceWorkerHelper, [{
    key: "togglePush",

    /**
     * Toggle push notifications subscription.
     */
    value: function togglePush() {
      if (this.isPushEnabled) {
        this.unsubscribe();
      } else {
        this.subscribe();
      }
    }
  }], [{
    key: "registerServiceWorker",

    /**
     * Register the service worker.
     */
    value: function registerServiceWorker() {
      var _this = this;

      if (!('serviceWorker' in navigator)) {
        console.log('Service workers aren\'t supported in this browser.');
        return;
      }

      return navigator.serviceWorker.register('/sw.js').then(function () {
        return _this.initialiseServiceWorker();
      });
    }
  }, {
    key: "initialiseServiceWorker",
    value: function initialiseServiceWorker() {
      if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
        console.log('Notifications aren\'t supported.');
        return;
      }

      if (Notification.permission === 'denied') {
        console.log('The user has blocked notifications.');
        return;
      }

      if (!('PushManager' in window)) {
        console.log('Push messaging isn\'t supported.');
        return;
      }

      return navigator.serviceWorker.ready;
    }
    /**
     * Subscribe for push notifications.
     */

  }, {
    key: "subscribe",
    value: function subscribe() {
      var _this2 = this;

      return navigator.serviceWorker.ready.then(function (registration) {
        var options = {
          userVisibleOnly: true
        };
        var vapidPublicKey = window.Laravel.vapidPublicKey;

        if (vapidPublicKey) {
          options.applicationServerKey = ServiceWorkerHelper.urlBase64ToUint8Array(vapidPublicKey);
        }

        return registration.pushManager.subscribe(options)["catch"](function (e) {
          if (Notification.permission === 'denied') {
            console.log('Permission for Notifications was denied');
            _this2.pushButtonDisabled = true;
          } else {
            console.log('Unable to subscribe to push.', e);
            _this2.pushButtonDisabled = false;
          }
        });
      });
    }
    /**
     * Unsubscribe from push notifications.
     */

  }, {
    key: "unsubscribe",
    value: function unsubscribe() {
      return navigator.serviceWorker.ready.then(function (registration) {
        return registration.pushManager.getSubscription()["catch"](function (e) {
          console.log('Error thrown while unsubscribing.', e);
        });
      });
    }
    /**
     * Send a request to the server to update user's subscription.
     *
     * @param {PushSubscription} subscription
     */

  }, {
    key: "updateSubscription",
    value: function updateSubscription(subscription) {
      var key = subscription.getKey('p256dh');
      var token = subscription.getKey('auth');
      var contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
      var data = {
        endpoint: subscription.endpoint,
        publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
        authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
        contentEncoding: contentEncoding
      };
      _graphql__WEBPACK_IMPORTED_MODULE_0__["ApolloClientInstance"].mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_1___default()(_templateObject()),
        variables: {
          input: data
        }
      });
    }
    /**
     * Send a requst to the server to delete user's subscription.
     *
     * @param {PushSubscription} subscription
     */

  }, {
    key: "deleteSubscription",
    value: function deleteSubscription(subscription) {
      _graphql__WEBPACK_IMPORTED_MODULE_0__["ApolloClientInstance"].mutate({
        mutation: graphql_tag__WEBPACK_IMPORTED_MODULE_1___default()(_templateObject2()),
        variables: {
          input: {
            endpoint: subscription.endpoint
          }
        }
      });
    }
    /**
     * https://github.com/Minishlink/physbook/blob/02a0d5d7ca0d5d2cc6d308a3a9b81244c63b3f14/app/Resources/public/js/app.js#L177
     *
     * @param  {String} base64String
     * @return {Uint8Array}
     */

  }, {
    key: "urlBase64ToUint8Array",
    value: function urlBase64ToUint8Array(base64String) {
      var padding = '='.repeat((4 - base64String.length % 4) % 4);
      var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
      var rawData = window.atob(base64);
      var outputArray = new Uint8Array(rawData.length);

      for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
      }

      return outputArray;
    }
  }]);

  return ServiceWorkerHelper;
}();

/***/ }),

/***/ "./resources/js/views/ChangeProfile.vue":
/*!**********************************************!*\
  !*** ./resources/js/views/ChangeProfile.vue ***!
  \**********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _ChangeProfile_vue_vue_type_template_id_7cff9e6c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ChangeProfile.vue?vue&type=template&id=7cff9e6c& */ "./resources/js/views/ChangeProfile.vue?vue&type=template&id=7cff9e6c&");
/* harmony import */ var _ChangeProfile_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ChangeProfile.vue?vue&type=script&lang=js& */ "./resources/js/views/ChangeProfile.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _ChangeProfile_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _ChangeProfile_vue_vue_type_template_id_7cff9e6c___WEBPACK_IMPORTED_MODULE_0__["render"],
  _ChangeProfile_vue_vue_type_template_id_7cff9e6c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/ChangeProfile.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/ChangeProfile.vue?vue&type=script&lang=js&":
/*!***********************************************************************!*\
  !*** ./resources/js/views/ChangeProfile.vue?vue&type=script&lang=js& ***!
  \***********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ChangeProfile_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./ChangeProfile.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/ChangeProfile.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_ChangeProfile_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/ChangeProfile.vue?vue&type=template&id=7cff9e6c&":
/*!*****************************************************************************!*\
  !*** ./resources/js/views/ChangeProfile.vue?vue&type=template&id=7cff9e6c& ***!
  \*****************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ChangeProfile_vue_vue_type_template_id_7cff9e6c___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./ChangeProfile.vue?vue&type=template&id=7cff9e6c& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/ChangeProfile.vue?vue&type=template&id=7cff9e6c&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ChangeProfile_vue_vue_type_template_id_7cff9e6c___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_ChangeProfile_vue_vue_type_template_id_7cff9e6c___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



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



/***/ }),

/***/ "./resources/js/views/Login.vue":
/*!**************************************!*\
  !*** ./resources/js/views/Login.vue ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Login_vue_vue_type_template_id_12f5395a_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Login.vue?vue&type=template&id=12f5395a&scoped=true& */ "./resources/js/views/Login.vue?vue&type=template&id=12f5395a&scoped=true&");
/* harmony import */ var _Login_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./Login.vue?vue&type=script&lang=js& */ "./resources/js/views/Login.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _Login_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _Login_vue_vue_type_template_id_12f5395a_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _Login_vue_vue_type_template_id_12f5395a_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "12f5395a",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/Login.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/Login.vue?vue&type=script&lang=js&":
/*!***************************************************************!*\
  !*** ./resources/js/views/Login.vue?vue&type=script&lang=js& ***!
  \***************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Login_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./Login.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/Login.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_Login_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/Login.vue?vue&type=template&id=12f5395a&scoped=true&":
/*!*********************************************************************************!*\
  !*** ./resources/js/views/Login.vue?vue&type=template&id=12f5395a&scoped=true& ***!
  \*********************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Login_vue_vue_type_template_id_12f5395a_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./Login.vue?vue&type=template&id=12f5395a&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/Login.vue?vue&type=template&id=12f5395a&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Login_vue_vue_type_template_id_12f5395a_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_Login_vue_vue_type_template_id_12f5395a_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



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
//# sourceMappingURL=6.js.map
(window["webpackJsonp"] = window["webpackJsonp"] || []).push([[5],{

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

/***/ "./resources/js/helpers.js":
/*!*********************************!*\
  !*** ./resources/js/helpers.js ***!
  \*********************************/
/*! exports provided: chunk, defaultIfUndefined, debounce, findPlayersInCategory, findLevel, findPositions, extractErrors, isPlayingToHigh, isPlayingToHighByName, swap, swapObject */
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
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isPlayingToHigh", function() { return isPlayingToHigh; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isPlayingToHighByName", function() { return isPlayingToHighByName; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "swap", function() { return swap; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "swapObject", function() { return swapObject; });
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
function isPlayingToHigh(playingToHighPlayers, player) {
  return playingToHighPlayers.find(function (toHighPlayer) {
    return toHighPlayer.id === player.id;
  }) !== undefined;
}
function isPlayingToHighByName(playingToHighPlayers, player) {
  return playingToHighPlayers.find(function (toHighPlayer) {
    return toHighPlayer.name === player.name;
  }) !== undefined;
}
function swap(arr, from, to) {
  arr.splice(from, 1, arr.splice(to, 1, arr[from])[0]);
}
function swapObject(obj, from, to) {
  var fromItem = obj[from];
  var toItem = obj[to];
  obj[to] = fromItem;
  obj[from] = toItem;
}

/***/ })

}]);
//# sourceMappingURL=5.js.map
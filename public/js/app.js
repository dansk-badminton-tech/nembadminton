/******/ (function(modules) { // webpackBootstrap
/******/ 	// install a JSONP callback for chunk loading
/******/ 	function webpackJsonpCallback(data) {
/******/ 		var chunkIds = data[0];
/******/ 		var moreModules = data[1];
/******/
/******/
/******/ 		// add "moreModules" to the modules object,
/******/ 		// then flag all "chunkIds" as loaded and fire callback
/******/ 		var moduleId, chunkId, i = 0, resolves = [];
/******/ 		for(;i < chunkIds.length; i++) {
/******/ 			chunkId = chunkIds[i];
/******/ 			if(Object.prototype.hasOwnProperty.call(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 				resolves.push(installedChunks[chunkId][0]);
/******/ 			}
/******/ 			installedChunks[chunkId] = 0;
/******/ 		}
/******/ 		for(moduleId in moreModules) {
/******/ 			if(Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
/******/ 				modules[moduleId] = moreModules[moduleId];
/******/ 			}
/******/ 		}
/******/ 		if(parentJsonpFunction) parentJsonpFunction(data);
/******/
/******/ 		while(resolves.length) {
/******/ 			resolves.shift()();
/******/ 		}
/******/
/******/ 	};
/******/
/******/
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// object to store loaded and loading chunks
/******/ 	// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 	// Promise = chunk loading, 0 = chunk loaded
/******/ 	var installedChunks = {
/******/ 		"/js/app": 0
/******/ 	};
/******/
/******/
/******/
/******/ 	// script path function
/******/ 	function jsonpScriptSrc(chunkId) {
/******/ 		return __webpack_require__.p + "" + ({}[chunkId]||chunkId) + ".js"
/******/ 	}
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/ 	// This file contains only the entry chunk.
/******/ 	// The chunk loading function for additional chunks
/******/ 	__webpack_require__.e = function requireEnsure(chunkId) {
/******/ 		var promises = [];
/******/
/******/
/******/ 		// JSONP chunk loading for javascript
/******/
/******/ 		var installedChunkData = installedChunks[chunkId];
/******/ 		if(installedChunkData !== 0) { // 0 means "already installed".
/******/
/******/ 			// a Promise means "currently loading".
/******/ 			if(installedChunkData) {
/******/ 				promises.push(installedChunkData[2]);
/******/ 			} else {
/******/ 				// setup Promise in chunk cache
/******/ 				var promise = new Promise(function(resolve, reject) {
/******/ 					installedChunkData = installedChunks[chunkId] = [resolve, reject];
/******/ 				});
/******/ 				promises.push(installedChunkData[2] = promise);
/******/
/******/ 				// start chunk loading
/******/ 				var script = document.createElement('script');
/******/ 				var onScriptComplete;
/******/
/******/ 				script.charset = 'utf-8';
/******/ 				script.timeout = 120;
/******/ 				if (__webpack_require__.nc) {
/******/ 					script.setAttribute("nonce", __webpack_require__.nc);
/******/ 				}
/******/ 				script.src = jsonpScriptSrc(chunkId);
/******/
/******/ 				// create error before stack unwound to get useful stacktrace later
/******/ 				var error = new Error();
/******/ 				onScriptComplete = function (event) {
/******/ 					// avoid mem leaks in IE.
/******/ 					script.onerror = script.onload = null;
/******/ 					clearTimeout(timeout);
/******/ 					var chunk = installedChunks[chunkId];
/******/ 					if(chunk !== 0) {
/******/ 						if(chunk) {
/******/ 							var errorType = event && (event.type === 'load' ? 'missing' : event.type);
/******/ 							var realSrc = event && event.target && event.target.src;
/******/ 							error.message = 'Loading chunk ' + chunkId + ' failed.\n(' + errorType + ': ' + realSrc + ')';
/******/ 							error.name = 'ChunkLoadError';
/******/ 							error.type = errorType;
/******/ 							error.request = realSrc;
/******/ 							chunk[1](error);
/******/ 						}
/******/ 						installedChunks[chunkId] = undefined;
/******/ 					}
/******/ 				};
/******/ 				var timeout = setTimeout(function(){
/******/ 					onScriptComplete({ type: 'timeout', target: script });
/******/ 				}, 120000);
/******/ 				script.onerror = script.onload = onScriptComplete;
/******/ 				document.head.appendChild(script);
/******/ 			}
/******/ 		}
/******/ 		return Promise.all(promises);
/******/ 	};
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// on error function for async loading
/******/ 	__webpack_require__.oe = function(err) { console.error(err); throw err; };
/******/
/******/ 	var jsonpArray = window["webpackJsonp"] = window["webpackJsonp"] || [];
/******/ 	var oldJsonpFunction = jsonpArray.push.bind(jsonpArray);
/******/ 	jsonpArray.push = webpackJsonpCallback;
/******/ 	jsonpArray = jsonpArray.slice();
/******/ 	for(var i = 0; i < jsonpArray.length; i++) webpackJsonpCallback(jsonpArray[i]);
/******/ 	var parentJsonpFunction = oldJsonpFunction;
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./node_modules/@fortawesome/fontawesome-svg-core/index.es.js":
/*!********************************************************************!*\
  !*** ./node_modules/@fortawesome/fontawesome-svg-core/index.es.js ***!
  \********************************************************************/
/*! exports provided: icon, noAuto, config, toHtml, layer, text, counter, library, dom, parse, findIconDefinition */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/@fortawesome/fontawesome-svg-core/index.es.js'");

/***/ }),

/***/ "./node_modules/@fortawesome/free-solid-svg-icons/index.es.js":
/*!********************************************************************!*\
  !*** ./node_modules/@fortawesome/free-solid-svg-icons/index.es.js ***!
  \********************************************************************/
/*! exports provided: fas, prefix, faAd, faAddressBook, faAddressCard, faAdjust, faAirFreshener, faAlignCenter, faAlignJustify, faAlignLeft, faAlignRight, faAllergies, faAmbulance, faAmericanSignLanguageInterpreting, faAnchor, faAngleDoubleDown, faAngleDoubleLeft, faAngleDoubleRight, faAngleDoubleUp, faAngleDown, faAngleLeft, faAngleRight, faAngleUp, faAngry, faAnkh, faAppleAlt, faArchive, faArchway, faArrowAltCircleDown, faArrowAltCircleLeft, faArrowAltCircleRight, faArrowAltCircleUp, faArrowCircleDown, faArrowCircleLeft, faArrowCircleRight, faArrowCircleUp, faArrowDown, faArrowLeft, faArrowRight, faArrowUp, faArrowsAlt, faArrowsAltH, faArrowsAltV, faAssistiveListeningSystems, faAsterisk, faAt, faAtlas, faAtom, faAudioDescription, faAward, faBaby, faBabyCarriage, faBackspace, faBackward, faBacon, faBacteria, faBacterium, faBahai, faBalanceScale, faBalanceScaleLeft, faBalanceScaleRight, faBan, faBandAid, faBarcode, faBars, faBaseballBall, faBasketballBall, faBath, faBatteryEmpty, faBatteryFull, faBatteryHalf, faBatteryQuarter, faBatteryThreeQuarters, faBed, faBeer, faBell, faBellSlash, faBezierCurve, faBible, faBicycle, faBiking, faBinoculars, faBiohazard, faBirthdayCake, faBlender, faBlenderPhone, faBlind, faBlog, faBold, faBolt, faBomb, faBone, faBong, faBook, faBookDead, faBookMedical, faBookOpen, faBookReader, faBookmark, faBorderAll, faBorderNone, faBorderStyle, faBowlingBall, faBox, faBoxOpen, faBoxTissue, faBoxes, faBraille, faBrain, faBreadSlice, faBriefcase, faBriefcaseMedical, faBroadcastTower, faBroom, faBrush, faBug, faBuilding, faBullhorn, faBullseye, faBurn, faBus, faBusAlt, faBusinessTime, faCalculator, faCalendar, faCalendarAlt, faCalendarCheck, faCalendarDay, faCalendarMinus, faCalendarPlus, faCalendarTimes, faCalendarWeek, faCamera, faCameraRetro, faCampground, faCandyCane, faCannabis, faCapsules, faCar, faCarAlt, faCarBattery, faCarCrash, faCarSide, faCaravan, faCaretDown, faCaretLeft, faCaretRight, faCaretSquareDown, faCaretSquareLeft, faCaretSquareRight, faCaretSquareUp, faCaretUp, faCarrot, faCartArrowDown, faCartPlus, faCashRegister, faCat, faCertificate, faChair, faChalkboard, faChalkboardTeacher, faChargingStation, faChartArea, faChartBar, faChartLine, faChartPie, faCheck, faCheckCircle, faCheckDouble, faCheckSquare, faCheese, faChess, faChessBishop, faChessBoard, faChessKing, faChessKnight, faChessPawn, faChessQueen, faChessRook, faChevronCircleDown, faChevronCircleLeft, faChevronCircleRight, faChevronCircleUp, faChevronDown, faChevronLeft, faChevronRight, faChevronUp, faChild, faChurch, faCircle, faCircleNotch, faCity, faClinicMedical, faClipboard, faClipboardCheck, faClipboardList, faClock, faClone, faClosedCaptioning, faCloud, faCloudDownloadAlt, faCloudMeatball, faCloudMoon, faCloudMoonRain, faCloudRain, faCloudShowersHeavy, faCloudSun, faCloudSunRain, faCloudUploadAlt, faCocktail, faCode, faCodeBranch, faCoffee, faCog, faCogs, faCoins, faColumns, faComment, faCommentAlt, faCommentDollar, faCommentDots, faCommentMedical, faCommentSlash, faComments, faCommentsDollar, faCompactDisc, faCompass, faCompress, faCompressAlt, faCompressArrowsAlt, faConciergeBell, faCookie, faCookieBite, faCopy, faCopyright, faCouch, faCreditCard, faCrop, faCropAlt, faCross, faCrosshairs, faCrow, faCrown, faCrutch, faCube, faCubes, faCut, faDatabase, faDeaf, faDemocrat, faDesktop, faDharmachakra, faDiagnoses, faDice, faDiceD20, faDiceD6, faDiceFive, faDiceFour, faDiceOne, faDiceSix, faDiceThree, faDiceTwo, faDigitalTachograph, faDirections, faDisease, faDivide, faDizzy, faDna, faDog, faDollarSign, faDolly, faDollyFlatbed, faDonate, faDoorClosed, faDoorOpen, faDotCircle, faDove, faDownload, faDraftingCompass, faDragon, faDrawPolygon, faDrum, faDrumSteelpan, faDrumstickBite, faDumbbell, faDumpster, faDumpsterFire, faDungeon, faEdit, faEgg, faEject, faEllipsisH, faEllipsisV, faEnvelope, faEnvelopeOpen, faEnvelopeOpenText, faEnvelopeSquare, faEquals, faEraser, faEthernet, faEuroSign, faExchangeAlt, faExclamation, faExclamationCircle, faExclamationTriangle, faExpand, faExpandAlt, faExpandArrowsAlt, faExternalLinkAlt, faExternalLinkSquareAlt, faEye, faEyeDropper, faEyeSlash, faFan, faFastBackward, faFastForward, faFaucet, faFax, faFeather, faFeatherAlt, faFemale, faFighterJet, faFile, faFileAlt, faFileArchive, faFileAudio, faFileCode, faFileContract, faFileCsv, faFileDownload, faFileExcel, faFileExport, faFileImage, faFileImport, faFileInvoice, faFileInvoiceDollar, faFileMedical, faFileMedicalAlt, faFilePdf, faFilePowerpoint, faFilePrescription, faFileSignature, faFileUpload, faFileVideo, faFileWord, faFill, faFillDrip, faFilm, faFilter, faFingerprint, faFire, faFireAlt, faFireExtinguisher, faFirstAid, faFish, faFistRaised, faFlag, faFlagCheckered, faFlagUsa, faFlask, faFlushed, faFolder, faFolderMinus, faFolderOpen, faFolderPlus, faFont, faFontAwesomeLogoFull, faFootballBall, faForward, faFrog, faFrown, faFrownOpen, faFunnelDollar, faFutbol, faGamepad, faGasPump, faGavel, faGem, faGenderless, faGhost, faGift, faGifts, faGlassCheers, faGlassMartini, faGlassMartiniAlt, faGlassWhiskey, faGlasses, faGlobe, faGlobeAfrica, faGlobeAmericas, faGlobeAsia, faGlobeEurope, faGolfBall, faGopuram, faGraduationCap, faGreaterThan, faGreaterThanEqual, faGrimace, faGrin, faGrinAlt, faGrinBeam, faGrinBeamSweat, faGrinHearts, faGrinSquint, faGrinSquintTears, faGrinStars, faGrinTears, faGrinTongue, faGrinTongueSquint, faGrinTongueWink, faGrinWink, faGripHorizontal, faGripLines, faGripLinesVertical, faGripVertical, faGuitar, faHSquare, faHamburger, faHammer, faHamsa, faHandHolding, faHandHoldingHeart, faHandHoldingMedical, faHandHoldingUsd, faHandHoldingWater, faHandLizard, faHandMiddleFinger, faHandPaper, faHandPeace, faHandPointDown, faHandPointLeft, faHandPointRight, faHandPointUp, faHandPointer, faHandRock, faHandScissors, faHandSparkles, faHandSpock, faHands, faHandsHelping, faHandsWash, faHandshake, faHandshakeAltSlash, faHandshakeSlash, faHanukiah, faHardHat, faHashtag, faHatCowboy, faHatCowboySide, faHatWizard, faHdd, faHeadSideCough, faHeadSideCoughSlash, faHeadSideMask, faHeadSideVirus, faHeading, faHeadphones, faHeadphonesAlt, faHeadset, faHeart, faHeartBroken, faHeartbeat, faHelicopter, faHighlighter, faHiking, faHippo, faHistory, faHockeyPuck, faHollyBerry, faHome, faHorse, faHorseHead, faHospital, faHospitalAlt, faHospitalSymbol, faHospitalUser, faHotTub, faHotdog, faHotel, faHourglass, faHourglassEnd, faHourglassHalf, faHourglassStart, faHouseDamage, faHouseUser, faHryvnia, faICursor, faIceCream, faIcicles, faIcons, faIdBadge, faIdCard, faIdCardAlt, faIgloo, faImage, faImages, faInbox, faIndent, faIndustry, faInfinity, faInfo, faInfoCircle, faItalic, faJedi, faJoint, faJournalWhills, faKaaba, faKey, faKeyboard, faKhanda, faKiss, faKissBeam, faKissWinkHeart, faKiwiBird, faLandmark, faLanguage, faLaptop, faLaptopCode, faLaptopHouse, faLaptopMedical, faLaugh, faLaughBeam, faLaughSquint, faLaughWink, faLayerGroup, faLeaf, faLemon, faLessThan, faLessThanEqual, faLevelDownAlt, faLevelUpAlt, faLifeRing, faLightbulb, faLink, faLiraSign, faList, faListAlt, faListOl, faListUl, faLocationArrow, faLock, faLockOpen, faLongArrowAltDown, faLongArrowAltLeft, faLongArrowAltRight, faLongArrowAltUp, faLowVision, faLuggageCart, faLungs, faLungsVirus, faMagic, faMagnet, faMailBulk, faMale, faMap, faMapMarked, faMapMarkedAlt, faMapMarker, faMapMarkerAlt, faMapPin, faMapSigns, faMarker, faMars, faMarsDouble, faMarsStroke, faMarsStrokeH, faMarsStrokeV, faMask, faMedal, faMedkit, faMeh, faMehBlank, faMehRollingEyes, faMemory, faMenorah, faMercury, faMeteor, faMicrochip, faMicrophone, faMicrophoneAlt, faMicrophoneAltSlash, faMicrophoneSlash, faMicroscope, faMinus, faMinusCircle, faMinusSquare, faMitten, faMobile, faMobileAlt, faMoneyBill, faMoneyBillAlt, faMoneyBillWave, faMoneyBillWaveAlt, faMoneyCheck, faMoneyCheckAlt, faMonument, faMoon, faMortarPestle, faMosque, faMotorcycle, faMountain, faMouse, faMousePointer, faMugHot, faMusic, faNetworkWired, faNeuter, faNewspaper, faNotEqual, faNotesMedical, faObjectGroup, faObjectUngroup, faOilCan, faOm, faOtter, faOutdent, faPager, faPaintBrush, faPaintRoller, faPalette, faPallet, faPaperPlane, faPaperclip, faParachuteBox, faParagraph, faParking, faPassport, faPastafarianism, faPaste, faPause, faPauseCircle, faPaw, faPeace, faPen, faPenAlt, faPenFancy, faPenNib, faPenSquare, faPencilAlt, faPencilRuler, faPeopleArrows, faPeopleCarry, faPepperHot, faPercent, faPercentage, faPersonBooth, faPhone, faPhoneAlt, faPhoneSlash, faPhoneSquare, faPhoneSquareAlt, faPhoneVolume, faPhotoVideo, faPiggyBank, faPills, faPizzaSlice, faPlaceOfWorship, faPlane, faPlaneArrival, faPlaneDeparture, faPlaneSlash, faPlay, faPlayCircle, faPlug, faPlus, faPlusCircle, faPlusSquare, faPodcast, faPoll, faPollH, faPoo, faPooStorm, faPoop, faPortrait, faPoundSign, faPowerOff, faPray, faPrayingHands, faPrescription, faPrescriptionBottle, faPrescriptionBottleAlt, faPrint, faProcedures, faProjectDiagram, faPumpMedical, faPumpSoap, faPuzzlePiece, faQrcode, faQuestion, faQuestionCircle, faQuidditch, faQuoteLeft, faQuoteRight, faQuran, faRadiation, faRadiationAlt, faRainbow, faRandom, faReceipt, faRecordVinyl, faRecycle, faRedo, faRedoAlt, faRegistered, faRemoveFormat, faReply, faReplyAll, faRepublican, faRestroom, faRetweet, faRibbon, faRing, faRoad, faRobot, faRocket, faRoute, faRss, faRssSquare, faRubleSign, faRuler, faRulerCombined, faRulerHorizontal, faRulerVertical, faRunning, faRupeeSign, faSadCry, faSadTear, faSatellite, faSatelliteDish, faSave, faSchool, faScrewdriver, faScroll, faSdCard, faSearch, faSearchDollar, faSearchLocation, faSearchMinus, faSearchPlus, faSeedling, faServer, faShapes, faShare, faShareAlt, faShareAltSquare, faShareSquare, faShekelSign, faShieldAlt, faShieldVirus, faShip, faShippingFast, faShoePrints, faShoppingBag, faShoppingBasket, faShoppingCart, faShower, faShuttleVan, faSign, faSignInAlt, faSignLanguage, faSignOutAlt, faSignal, faSignature, faSimCard, faSink, faSitemap, faSkating, faSkiing, faSkiingNordic, faSkull, faSkullCrossbones, faSlash, faSleigh, faSlidersH, faSmile, faSmileBeam, faSmileWink, faSmog, faSmoking, faSmokingBan, faSms, faSnowboarding, faSnowflake, faSnowman, faSnowplow, faSoap, faSocks, faSolarPanel, faSort, faSortAlphaDown, faSortAlphaDownAlt, faSortAlphaUp, faSortAlphaUpAlt, faSortAmountDown, faSortAmountDownAlt, faSortAmountUp, faSortAmountUpAlt, faSortDown, faSortNumericDown, faSortNumericDownAlt, faSortNumericUp, faSortNumericUpAlt, faSortUp, faSpa, faSpaceShuttle, faSpellCheck, faSpider, faSpinner, faSplotch, faSprayCan, faSquare, faSquareFull, faSquareRootAlt, faStamp, faStar, faStarAndCrescent, faStarHalf, faStarHalfAlt, faStarOfDavid, faStarOfLife, faStepBackward, faStepForward, faStethoscope, faStickyNote, faStop, faStopCircle, faStopwatch, faStopwatch20, faStore, faStoreAlt, faStoreAltSlash, faStoreSlash, faStream, faStreetView, faStrikethrough, faStroopwafel, faSubscript, faSubway, faSuitcase, faSuitcaseRolling, faSun, faSuperscript, faSurprise, faSwatchbook, faSwimmer, faSwimmingPool, faSynagogue, faSync, faSyncAlt, faSyringe, faTable, faTableTennis, faTablet, faTabletAlt, faTablets, faTachometerAlt, faTag, faTags, faTape, faTasks, faTaxi, faTeeth, faTeethOpen, faTemperatureHigh, faTemperatureLow, faTenge, faTerminal, faTextHeight, faTextWidth, faTh, faThLarge, faThList, faTheaterMasks, faThermometer, faThermometerEmpty, faThermometerFull, faThermometerHalf, faThermometerQuarter, faThermometerThreeQuarters, faThumbsDown, faThumbsUp, faThumbtack, faTicketAlt, faTimes, faTimesCircle, faTint, faTintSlash, faTired, faToggleOff, faToggleOn, faToilet, faToiletPaper, faToiletPaperSlash, faToolbox, faTools, faTooth, faTorah, faToriiGate, faTractor, faTrademark, faTrafficLight, faTrailer, faTrain, faTram, faTransgender, faTransgenderAlt, faTrash, faTrashAlt, faTrashRestore, faTrashRestoreAlt, faTree, faTrophy, faTruck, faTruckLoading, faTruckMonster, faTruckMoving, faTruckPickup, faTshirt, faTty, faTv, faUmbrella, faUmbrellaBeach, faUnderline, faUndo, faUndoAlt, faUniversalAccess, faUniversity, faUnlink, faUnlock, faUnlockAlt, faUpload, faUser, faUserAlt, faUserAltSlash, faUserAstronaut, faUserCheck, faUserCircle, faUserClock, faUserCog, faUserEdit, faUserFriends, faUserGraduate, faUserInjured, faUserLock, faUserMd, faUserMinus, faUserNinja, faUserNurse, faUserPlus, faUserSecret, faUserShield, faUserSlash, faUserTag, faUserTie, faUserTimes, faUsers, faUsersCog, faUsersSlash, faUtensilSpoon, faUtensils, faVectorSquare, faVenus, faVenusDouble, faVenusMars, faVest, faVestPatches, faVial, faVials, faVideo, faVideoSlash, faVihara, faVirus, faVirusSlash, faViruses, faVoicemail, faVolleyballBall, faVolumeDown, faVolumeMute, faVolumeOff, faVolumeUp, faVoteYea, faVrCardboard, faWalking, faWallet, faWarehouse, faWater, faWaveSquare, faWeight, faWeightHanging, faWheelchair, faWifi, faWind, faWindowClose, faWindowMaximize, faWindowMinimize, faWindowRestore, faWineBottle, faWineGlass, faWineGlassAlt, faWonSign, faWrench, faXRay, faYenSign, faYinYang */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/@fortawesome/free-solid-svg-icons/index.es.js'");

/***/ }),

/***/ "./node_modules/@fortawesome/vue-fontawesome/index.es.js":
/*!***************************************************************!*\
  !*** ./node_modules/@fortawesome/vue-fontawesome/index.es.js ***!
  \***************************************************************/
/*! exports provided: FontAwesomeIcon, FontAwesomeLayers, FontAwesomeLayersText */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/@fortawesome/vue-fontawesome/index.es.js'");

/***/ }),

/***/ "./node_modules/apollo-boost/lib/bundle.esm.js":
/*!*****************************************************!*\
  !*** ./node_modules/apollo-boost/lib/bundle.esm.js ***!
  \*****************************************************/
/*! exports provided: ApolloClient, ApolloError, FetchType, NetworkStatus, ObservableQuery, isApolloError, Observable, getOperationName, ApolloLink, concat, createOperation, empty, execute, from, fromError, fromPromise, makePromise, split, toPromise, HeuristicFragmentMatcher, InMemoryCache, IntrospectionFragmentMatcher, ObjectCache, StoreReader, StoreWriter, WriteError, assertIdValue, defaultDataIdFromObject, defaultNormalizedCacheFactory, enhanceErrorWithDocument, HttpLink, gql, default */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/apollo-boost/lib/bundle.esm.js'");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/App.vue?vue&type=script&lang=js&":
/*!*********************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/App.vue?vue&type=script&lang=js& ***!
  \*********************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({});

/***/ }),

/***/ "./node_modules/buefy/dist/buefy.css":
/*!*******************************************!*\
  !*** ./node_modules/buefy/dist/buefy.css ***!
  \*******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../css-loader??ref--6-1!../../postcss-loader/src??ref--6-2!./buefy.css */ "./node_modules/css-loader/index.js?!./node_modules/postcss-loader/src/index.js?!./node_modules/buefy/dist/buefy.css");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/buefy/dist/esm/index.js":
/*!**********************************************!*\
  !*** ./node_modules/buefy/dist/esm/index.js ***!
  \**********************************************/
/*! exports provided: bound, createAbsoluteElement, createNewEvent, escapeRegExpChars, getMonthNames, getValueByPath, getWeekdayNames, hasFlag, indexOf, isCustomElement, isDefined, isMobile, isVueComponent, isWebpSupported, matchWithGroups, merge, mod, multiColumnSort, removeElement, sign, toCssWidth, Autocomplete, Button, Carousel, Checkbox, Collapse, Clockpicker, Datepicker, Datetimepicker, Dialog, DialogProgrammatic, Dropdown, Field, Icon, Image, Input, Loading, LoadingProgrammatic, Menu, Message, Modal, ModalProgrammatic, Notification, NotificationProgrammatic, Navbar, Numberinput, Pagination, Progress, Radio, Rate, Select, Skeleton, Sidebar, Slider, Snackbar, SnackbarProgrammatic, Steps, Switch, Table, Tabs, Tag, Taginput, Timepicker, Toast, ToastProgrammatic, Tooltip, Upload, default, ConfigProgrammatic */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/buefy/dist/esm/index.js'");

/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/postcss-loader/src/index.js?!./node_modules/buefy/dist/buefy.css":
/*!***************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/postcss-loader/src??ref--6-2!./node_modules/buefy/dist/buefy.css ***!
  \***************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

throw new Error("Module build failed (from ./node_modules/postcss-loader/src/index.js):\nError: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/buefy/dist/buefy.css'");

/***/ }),

/***/ "./node_modules/jwt-decode/build/jwt-decode.esm.js":
/*!*********************************************************!*\
  !*** ./node_modules/jwt-decode/build/jwt-decode.esm.js ***!
  \*********************************************************/
/*! exports provided: default, InvalidTokenError */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/jwt-decode/build/jwt-decode.esm.js'");

/***/ }),

/***/ "./node_modules/style-loader/lib/addStyles.js":
/*!****************************************************!*\
  !*** ./node_modules/style-loader/lib/addStyles.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/style-loader/lib/addStyles.js'");

/***/ }),

/***/ "./node_modules/vee-validate/dist/locale/da.json":
/*!*******************************************************!*\
  !*** ./node_modules/vee-validate/dist/locale/da.json ***!
  \*******************************************************/
/*! exports provided: code, messages, default */
/***/ (function(module) {

!(function webpackMissingModule() { var e = new Error("Cannot find module 'vee-validate/dist/locale/da.json'"); e.code = 'MODULE_NOT_FOUND'; throw e; }());


/***/ }),

/***/ "./node_modules/vee-validate/dist/rules.js":
/*!*************************************************!*\
  !*** ./node_modules/vee-validate/dist/rules.js ***!
  \*************************************************/
/*! exports provided: alpha, alpha_dash, alpha_num, alpha_spaces, between, confirmed, digits, dimensions, double, email, excluded, ext, image, integer, is, is_not, length, max, max_value, mimes, min, min_value, numeric, oneOf, regex, required, required_if, size */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/vee-validate/dist/rules.js'");

/***/ }),

/***/ "./node_modules/vee-validate/dist/vee-validate.esm.js":
/*!************************************************************!*\
  !*** ./node_modules/vee-validate/dist/vee-validate.esm.js ***!
  \************************************************************/
/*! exports provided: ValidationObserver, ValidationProvider, configure, extend, localeChanged, localize, normalizeRules, setInteractionMode, validate, version, withValidation */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/vee-validate/dist/vee-validate.esm.js'");

/***/ }),

/***/ "./node_modules/vue-apollo/dist/vue-apollo.esm.js":
/*!********************************************************!*\
  !*** ./node_modules/vue-apollo/dist/vue-apollo.esm.js ***!
  \********************************************************/
/*! exports provided: default, ApolloMutation, ApolloProvider, ApolloQuery, ApolloSubscribeToMore, install */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/vue-apollo/dist/vue-apollo.esm.js'");

/***/ }),

/***/ "./node_modules/vue-clipboard2/vue-clipboard.js":
/*!******************************************************!*\
  !*** ./node_modules/vue-clipboard2/vue-clipboard.js ***!
  \******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/vue-clipboard2/vue-clipboard.js'");

/***/ }),

/***/ "./node_modules/vue-fragment/dist/vue-fragment.esm.js":
/*!************************************************************!*\
  !*** ./node_modules/vue-fragment/dist/vue-fragment.esm.js ***!
  \************************************************************/
/*! exports provided: default, Fragment, SSR, Plugin */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/vue-fragment/dist/vue-fragment.esm.js'");

/***/ }),

/***/ "./node_modules/vue-i18n/dist/vue-i18n.esm.js":
/*!****************************************************!*\
  !*** ./node_modules/vue-i18n/dist/vue-i18n.esm.js ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/vue-i18n/dist/vue-i18n.esm.js'");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/App.vue?vue&type=template&id=91ac6b5c&scoped=true&":
/*!*************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/views/App.vue?vue&type=template&id=91ac6b5c&scoped=true& ***!
  \*************************************************************************************************************************************************************************************************************/
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
  return _c("router-view")
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/vue-loader/lib/runtime/componentNormalizer.js'");

/***/ }),

/***/ "./node_modules/vue-router/dist/vue-router.esm.js":
/*!********************************************************!*\
  !*** ./node_modules/vue-router/dist/vue-router.esm.js ***!
  \********************************************************/
/*! exports provided: default */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/vue-router/dist/vue-router.esm.js'");

/***/ }),

/***/ "./node_modules/vue/dist/vue.common.js":
/*!*********************************************!*\
  !*** ./node_modules/vue/dist/vue.common.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

throw new Error("Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/vue/dist/vue.common.js'");

/***/ }),

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _graphql__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./graphql */ "./resources/js/graphql.js");
/* harmony import */ var _views_App__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./views/App */ "./resources/js/views/App.vue");
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.common.js");
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _router__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./router */ "./resources/js/router.js");
/* harmony import */ var vue_i18n__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! vue-i18n */ "./node_modules/vue-i18n/dist/vue-i18n.esm.js");
/* harmony import */ var _i18n_da__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./i18n/da */ "./resources/js/i18n/da.js");


__webpack_require__(/*! ./bootstrap */ "./resources/js/bootstrap.js");






var i18n = new vue_i18n__WEBPACK_IMPORTED_MODULE_4__["default"]({
  locale: 'da',
  messages: {
    da: _i18n_da__WEBPACK_IMPORTED_MODULE_5__["default"]
  }
});
new vue__WEBPACK_IMPORTED_MODULE_2___default.a({
  components: {
    App: _views_App__WEBPACK_IMPORTED_MODULE_1__["default"]
  },
  apolloProvider: _graphql__WEBPACK_IMPORTED_MODULE_0__["default"],
  i18n: i18n,
  router: _router__WEBPACK_IMPORTED_MODULE_3__["default"]
}).$mount("#app");

/***/ }),

/***/ "./resources/js/auth.js":
/*!******************************!*\
  !*** ./resources/js/auth.js ***!
  \******************************/
/*! exports provided: setUser, getUser, setAuthToken, getAuthToken, isLoggedIn, logoutUser, clearAuthToken, getUserInfo */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "setUser", function() { return setUser; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getUser", function() { return getUser; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "setAuthToken", function() { return setAuthToken; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getAuthToken", function() { return getAuthToken; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isLoggedIn", function() { return isLoggedIn; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "logoutUser", function() { return logoutUser; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "clearAuthToken", function() { return clearAuthToken; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "getUserInfo", function() { return getUserInfo; });
/* harmony import */ var jwt_decode__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jwt-decode */ "./node_modules/jwt-decode/build/jwt-decode.esm.js");
/* harmony import */ var _globals__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./globals */ "./resources/js/globals.js");


var AUTH_TOKEN_KEY = 'access_token';
function setUser(newUser) {
  _globals__WEBPACK_IMPORTED_MODULE_1__["default"].user = newUser;
}
function getUser() {
  return _globals__WEBPACK_IMPORTED_MODULE_1__["default"].user;
}
function setAuthToken(token) {
  localStorage.setItem(AUTH_TOKEN_KEY, token);
}
function getAuthToken() {
  return localStorage.getItem(AUTH_TOKEN_KEY);
}
function isLoggedIn() {
  var authToken = getAuthToken();
  return !!authToken && !isTokenExpired(authToken);
}
function logoutUser() {
  clearAuthToken();
}
function clearAuthToken() {
  localStorage.removeItem(AUTH_TOKEN_KEY);
}
function getUserInfo() {
  if (isLoggedIn()) {
    return Object(jwt_decode__WEBPACK_IMPORTED_MODULE_0__["default"])(getAuthToken());
  }
}

function isTokenExpired(token) {
  var expirationDate = getTokenExpirationDate(token);
  return expirationDate < new Date();
}

function getTokenExpirationDate(encodedToken) {
  var token = Object(jwt_decode__WEBPACK_IMPORTED_MODULE_0__["default"])(encodedToken);

  if (!token.exp) {
    return null;
  }

  var date = new Date(0);
  date.setUTCSeconds(token.exp);
  return date;
}

/***/ }),

/***/ "./resources/js/awesome-font.js":
/*!**************************************!*\
  !*** ./resources/js/awesome-font.js ***!
  \**************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var buefy__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! buefy */ "./node_modules/buefy/dist/esm/index.js");
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.common.js");
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _fortawesome_fontawesome_svg_core__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @fortawesome/fontawesome-svg-core */ "./node_modules/@fortawesome/fontawesome-svg-core/index.es.js");
/* harmony import */ var _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @fortawesome/free-solid-svg-icons */ "./node_modules/@fortawesome/free-solid-svg-icons/index.es.js");
/* harmony import */ var _fortawesome_vue_fontawesome__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @fortawesome/vue-fontawesome */ "./node_modules/@fortawesome/vue-fontawesome/index.es.js");





_fortawesome_fontawesome_svg_core__WEBPACK_IMPORTED_MODULE_2__["library"].add(_fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faBell"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faCopy"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faLock"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faEnvelope"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faSearch"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faBars"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faShareAlt"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faEllipsisV"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faEye"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faCheck"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faAngleUp"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faAngleDown"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faHandRock"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faCalendarAlt"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faHome"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faAngleLeft"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faAngleRight"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faTrash"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faUsers"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faUserAlt"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faSave"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faBrain"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faArrowsAlt"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faTrashAlt"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faArrowUp"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faTimesCircle"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faExclamationCircle"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faSignOutAlt"], _fortawesome_free_solid_svg_icons__WEBPACK_IMPORTED_MODULE_3__["faAlignJustify"]);
vue__WEBPACK_IMPORTED_MODULE_1___default.a.component('vue-fontawesome', _fortawesome_vue_fontawesome__WEBPACK_IMPORTED_MODULE_4__["FontAwesomeIcon"]);
vue__WEBPACK_IMPORTED_MODULE_1___default.a.use(buefy__WEBPACK_IMPORTED_MODULE_0__["default"], {
  defaultIconComponent: 'vue-fontawesome',
  defaultIconPack: 'fas',
  customIconPacks: {
    fas: {
      sizes: {
        "default": "lg",
        "is-small": "1x",
        "is-medium": "2x",
        "is-large": "3x"
      },
      iconPrefix: ""
    }
  }
});

/***/ }),

/***/ "./resources/js/bootstrap.js":
/*!***********************************!*\
  !*** ./resources/js/bootstrap.js ***!
  \***********************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.common.js");
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var vue_router__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue-router */ "./node_modules/vue-router/dist/vue-router.esm.js");
/* harmony import */ var buefy__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! buefy */ "./node_modules/buefy/dist/esm/index.js");
/* harmony import */ var vue_fragment__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! vue-fragment */ "./node_modules/vue-fragment/dist/vue-fragment.esm.js");
/* harmony import */ var vue_i18n__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! vue-i18n */ "./node_modules/vue-i18n/dist/vue-i18n.esm.js");
/* harmony import */ var vue_apollo__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! vue-apollo */ "./node_modules/vue-apollo/dist/vue-apollo.esm.js");
/* harmony import */ var buefy_dist_buefy_css__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! buefy/dist/buefy.css */ "./node_modules/buefy/dist/buefy.css");
/* harmony import */ var buefy_dist_buefy_css__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(buefy_dist_buefy_css__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var _vee_validate__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./vee-validate */ "./resources/js/vee-validate.js");
/* harmony import */ var _awesome_font__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./awesome-font */ "./resources/js/awesome-font.js");
/* harmony import */ var vue_clipboard2__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! vue-clipboard2 */ "./node_modules/vue-clipboard2/vue-clipboard.js");
/* harmony import */ var vue_clipboard2__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(vue_clipboard2__WEBPACK_IMPORTED_MODULE_9__);










vue__WEBPACK_IMPORTED_MODULE_0___default.a.use(vue_apollo__WEBPACK_IMPORTED_MODULE_5__["default"]);
vue__WEBPACK_IMPORTED_MODULE_0___default.a.use(vue_i18n__WEBPACK_IMPORTED_MODULE_4__["default"]);
vue__WEBPACK_IMPORTED_MODULE_0___default.a.use(vue_fragment__WEBPACK_IMPORTED_MODULE_3__["Plugin"]);
vue__WEBPACK_IMPORTED_MODULE_0___default.a.use(buefy__WEBPACK_IMPORTED_MODULE_2__["default"]);
vue__WEBPACK_IMPORTED_MODULE_0___default.a.use(vue_router__WEBPACK_IMPORTED_MODULE_1__["default"]);
vue__WEBPACK_IMPORTED_MODULE_0___default.a.use(vue_clipboard2__WEBPACK_IMPORTED_MODULE_9___default.a);

/***/ }),

/***/ "./resources/js/globals.js":
/*!*********************************!*\
  !*** ./resources/js/globals.js ***!
  \*********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  user: {}
});

/***/ }),

/***/ "./resources/js/graphql.js":
/*!*********************************!*\
  !*** ./resources/js/graphql.js ***!
  \*********************************/
/*! exports provided: default, ApolloClientInstance */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "ApolloClientInstance", function() { return ApolloClientInstance; });
/* harmony import */ var apollo_boost__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! apollo-boost */ "./node_modules/apollo-boost/lib/bundle.esm.js");
/* harmony import */ var vue_apollo__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue-apollo */ "./node_modules/vue-apollo/dist/vue-apollo.esm.js");
/* harmony import */ var _auth__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./auth */ "./resources/js/auth.js");



var ApolloClientInstance = new apollo_boost__WEBPACK_IMPORTED_MODULE_0__["default"]({
  uri: '/graphql',
  request: function request(operation) {
    if (Object(_auth__WEBPACK_IMPORTED_MODULE_2__["isLoggedIn"])()) {
      operation.setContext({
        headers: {
          Authorization: 'Bearer ' + Object(_auth__WEBPACK_IMPORTED_MODULE_2__["getAuthToken"])()
        }
      });
    }
  }
});
var apolloProvider = new vue_apollo__WEBPACK_IMPORTED_MODULE_1__["default"]({
  defaultClient: ApolloClientInstance
});
/* harmony default export */ __webpack_exports__["default"] = (apolloProvider);


/***/ }),

/***/ "./resources/js/i18n/da.js":
/*!*********************************!*\
  !*** ./resources/js/i18n/da.js ***!
  \*********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
var da = {
  roundsGenerator: {
    findPlayer: 'Søger efter spiller',
    findPlayerPlaceholder: 'fx. Viktor',
    findClub: 'Vælge klub',
    findClubPlaceholder: 'Skovbakken'
  }
};
/* harmony default export */ __webpack_exports__["default"] = (da);

/***/ }),

/***/ "./resources/js/router.js":
/*!********************************!*\
  !*** ./resources/js/router.js ***!
  \********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var vue_router__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue-router */ "./node_modules/vue-router/dist/vue-router.esm.js");
/* harmony import */ var _auth__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./auth */ "./resources/js/auth.js");
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.common.js");
/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(vue__WEBPACK_IMPORTED_MODULE_2__);



var router = new vue_router__WEBPACK_IMPORTED_MODULE_0__["default"]({
  mode: 'history',
  routes: [{
    path: '/home',
    name: 'home',
    component: function component() {
      return Promise.all(/*! import() */[__webpack_require__.e(6), __webpack_require__.e(18)]).then(__webpack_require__.bind(null, /*! ./views/Home */ "./resources/js/views/Home.vue"));
    },
    meta: {
      allowAnonymous: true,
      title: 'Forside'
    }
  }, {
    path: "/",
    redirect: "/home",
    component: function component() {
      return Promise.all(/*! import() */[__webpack_require__.e(6), __webpack_require__.e(19)]).then(__webpack_require__.bind(null, /*! ./views/Base */ "./resources/js/views/Base.vue"));
    },
    children: [{
      path: '/my-profile',
      name: 'my-profile',
      component: function component() {
        return __webpack_require__.e(/*! import() */ 13).then(__webpack_require__.bind(null, /*! ./views/MyProfile */ "./resources/js/views/MyProfile.vue"));
      },
      meta: {
        title: 'Min profil'
      }
    }, {
      path: '/rounds-generator',
      name: 'rounds-generator',
      component: function component() {
        return Promise.all(/*! import() */[__webpack_require__.e(10), __webpack_require__.e(1), __webpack_require__.e(23)]).then(__webpack_require__.bind(null, /*! ./views/RoundsGenerator */ "./resources/js/views/RoundsGenerator.vue"));
      }
    }, {
      path: '/team-fight/dashboard',
      name: 'team-fight-dashboard',
      component: function component() {
        return __webpack_require__.e(/*! import() */ 11).then(__webpack_require__.bind(null, /*! ./views/TeamFightList */ "./resources/js/views/TeamFightList.vue"));
      },
      meta: {
        title: 'Holdkamps oversigt'
      }
    }, {
      path: '/team-fight/:teamUUID/edit',
      name: 'team-fight-edit',
      component: function component() {
        return Promise.all(/*! import() */[__webpack_require__.e(16), __webpack_require__.e(1), __webpack_require__.e(2)]).then(__webpack_require__.bind(null, /*! ./views/TeamFight */ "./resources/js/views/TeamFight.vue"));
      },
      props: function props(route) {
        return {
          teamFightId: route.params.teamUUID
        };
      },
      meta: {
        title: 'Rediger holdkamp'
      }
    }, {
      path: '/team-fight/:teamUUID/import',
      name: 'team-fight-import',
      component: function component() {
        return Promise.all(/*! import() */[__webpack_require__.e(0), __webpack_require__.e(21)]).then(__webpack_require__.bind(null, /*! ./views/TeamFightImport */ "./resources/js/views/TeamFightImport.vue"));
      },
      props: function props(route) {
        return {
          teamFightId: route.params.teamUUID
        };
      },
      meta: {
        title: 'Importer fra badmintonplayer.dk'
      }
    }, {
      path: '/team-fight/create',
      name: 'team-fight-create',
      component: function component() {
        return __webpack_require__.e(/*! import() */ 12).then(__webpack_require__.bind(null, /*! ./views/TeamFightCreate */ "./resources/js/views/TeamFightCreate.vue"));
      },
      meta: {
        title: 'Opret holdkamp'
      }
    }, {
      path: '/new-user',
      name: 'new-user-create',
      component: function component() {
        return Promise.all(/*! import() */[__webpack_require__.e(10), __webpack_require__.e(7)]).then(__webpack_require__.bind(null, /*! ./views/CreateUser */ "./resources/js/views/CreateUser.vue"));
      },
      meta: {
        allowAnonymous: true,
        title: 'Ny bruger'
      }
    }, {
      path: '/login',
      name: 'login',
      component: function component() {
        return __webpack_require__.e(/*! import() */ 20).then(__webpack_require__.bind(null, /*! ./views/Login */ "./resources/js/views/Login.vue"));
      },
      meta: {
        allowAnonymous: true,
        title: 'Login'
      }
    }, {
      path: '/team-fight/:teamUUID/view',
      name: 'team-fight-view',
      component: function component() {
        return Promise.all(/*! import() */[__webpack_require__.e(10), __webpack_require__.e(16), __webpack_require__.e(7), __webpack_require__.e(4)]).then(__webpack_require__.bind(null, /*! ./views/TeamFightPublic */ "./resources/js/views/TeamFightPublic.vue"));
      },
      props: function props(route) {
        return {
          teamFightId: route.params.teamUUID
        };
      },
      meta: {
        allowAnonymous: true,
        title: 'Holdkamp'
      }
    }, {
      path: '/player-stats/:badmintonId',
      name: 'player-stats',
      component: function component() {
        return __webpack_require__.e(/*! import() */ 8).then(__webpack_require__.bind(null, /*! ./views/PlayerStats */ "./resources/js/views/PlayerStats.vue"));
      },
      props: function props(route) {
        return {
          badmintonId: route.params.badmintonId
        };
      },
      meta: {
        allowAnonymous: false,
        title: 'Spiller stats'
      }
    }, {
      path: '/my-club',
      name: 'my-club',
      component: function component() {
        return __webpack_require__.e(/*! import() */ 15).then(__webpack_require__.bind(null, /*! ./views/ClubDashboard */ "./resources/js/views/ClubDashboard.vue"));
      },
      meta: {
        allowAnonymous: false,
        title: 'Min klub'
      }
    }, {
      path: '/team-fight/check',
      name: 'check-team-fight',
      component: function component() {
        return Promise.all(/*! import() */[__webpack_require__.e(0), __webpack_require__.e(22)]).then(__webpack_require__.bind(null, /*! ./views/CheckTeamFight */ "./resources/js/views/CheckTeamFight.vue"));
      },
      meta: {
        allowAnonymous: true,
        title: 'Tjek holdkamps runde'
      }
    }]
  }]
});
var DEFAULT_TITLE = '...';
router.afterEach(function (to, from) {
  // Use next tick to handle router history correctly
  // see: https://github.com/vuejs/vue-router/issues/914#issuecomment-384477609
  vue__WEBPACK_IMPORTED_MODULE_2___default.a.nextTick(function () {
    document.title = to.meta.title || DEFAULT_TITLE;
  });
});
router.beforeEach(function (to, from, next) {
  if (!to.meta.allowAnonymous && !Object(_auth__WEBPACK_IMPORTED_MODULE_1__["isLoggedIn"])()) {
    next({
      path: '/login',
      query: {
        redirect: to.fullPath
      }
    });
  } else {
    next();
  }
});
/* harmony default export */ __webpack_exports__["default"] = (router);

/***/ }),

/***/ "./resources/js/vee-validate.js":
/*!**************************************!*\
  !*** ./resources/js/vee-validate.js ***!
  \**************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var vee_validate__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vee-validate */ "./node_modules/vee-validate/dist/vee-validate.esm.js");
/* harmony import */ var vee_validate_dist_rules__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vee-validate/dist/rules */ "./node_modules/vee-validate/dist/rules.js");
/* harmony import */ var vee_validate_dist_locale_da_json__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vee-validate/dist/locale/da.json */ "./node_modules/vee-validate/dist/locale/da.json");
var vee_validate_dist_locale_da_json__WEBPACK_IMPORTED_MODULE_2___namespace = /*#__PURE__*/__webpack_require__.t(/*! vee-validate/dist/locale/da.json */ "./node_modules/vee-validate/dist/locale/da.json", 1);
function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }




Object(vee_validate__WEBPACK_IMPORTED_MODULE_0__["localize"])('da', vee_validate_dist_locale_da_json__WEBPACK_IMPORTED_MODULE_2__);
Object(vee_validate__WEBPACK_IMPORTED_MODULE_0__["extend"])("required", _objectSpread({}, vee_validate_dist_rules__WEBPACK_IMPORTED_MODULE_1__["required"]));

/***/ }),

/***/ "./resources/js/views/App.vue":
/*!************************************!*\
  !*** ./resources/js/views/App.vue ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _App_vue_vue_type_template_id_91ac6b5c_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./App.vue?vue&type=template&id=91ac6b5c&scoped=true& */ "./resources/js/views/App.vue?vue&type=template&id=91ac6b5c&scoped=true&");
/* harmony import */ var _App_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./App.vue?vue&type=script&lang=js& */ "./resources/js/views/App.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _App_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _App_vue_vue_type_template_id_91ac6b5c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _App_vue_vue_type_template_id_91ac6b5c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "91ac6b5c",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/views/App.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/views/App.vue?vue&type=script&lang=js&":
/*!*************************************************************!*\
  !*** ./resources/js/views/App.vue?vue&type=script&lang=js& ***!
  \*************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./App.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/App.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/views/App.vue?vue&type=template&id=91ac6b5c&scoped=true&":
/*!*******************************************************************************!*\
  !*** ./resources/js/views/App.vue?vue&type=template&id=91ac6b5c&scoped=true& ***!
  \*******************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_template_id_91ac6b5c_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./App.vue?vue&type=template&id=91ac6b5c&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/views/App.vue?vue&type=template&id=91ac6b5c&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_template_id_91ac6b5c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_App_vue_vue_type_template_id_91ac6b5c_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/sass/app.scss":
/*!*********************************!*\
  !*** ./resources/sass/app.scss ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

throw new Error("Module build failed (from ./node_modules/css-loader/index.js):\nModuleBuildError: Module build failed: Error: ENOENT: no such file or directory, open '/home/daniel/projects/holdkamp/node_modules/css-loader/lib/css-base.js'\n    at /home/daniel/projects/holdkamp/node_modules/webpack/lib/NormalModule.js:316:20\n    at /home/daniel/projects/holdkamp/node_modules/loader-runner/lib/LoaderRunner.js:367:11\n    at /home/daniel/projects/holdkamp/node_modules/loader-runner/lib/LoaderRunner.js:203:19\n    at /home/daniel/projects/holdkamp/node_modules/enhanced-resolve/lib/CachedInputFileSystem.js:85:15\n    at processTicksAndRejections (internal/process/task_queues.js:77:11)");

/***/ }),

/***/ 0:
/*!*************************************************************!*\
  !*** multi ./resources/js/app.js ./resources/sass/app.scss ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /home/daniel/projects/holdkamp/resources/js/app.js */"./resources/js/app.js");
module.exports = __webpack_require__(/*! /home/daniel/projects/holdkamp/resources/sass/app.scss */"./resources/sass/app.scss");


/***/ })

/******/ });
//# sourceMappingURL=app.js.map
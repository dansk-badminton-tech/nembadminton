/*! For license information please see 5.js.LICENSE.txt */
(window.webpackJsonp=window.webpackJsonp||[]).push([[5],{"+0iv":function(e,t,n){"use strict";var a=n("qDJ8");function r(e){return!0===a(e)&&"[object Object]"===Object.prototype.toString.call(e)}e.exports=function(e){var t,n;return!1!==r(e)&&("function"==typeof(t=e.constructor)&&(!1!==r(n=t.prototype)&&!1!==n.hasOwnProperty("isPrototypeOf")))}},"/9Ld":function(e,t,n){"use strict";var a=n("+0iv"),r=n("y7tx");e.exports=function e(t,n){if(void 0===t)return{};if(Array.isArray(t)){for(var i=0;i<t.length;i++)t[i]=e(t[i],n);return t}if(!a(t))return t;if("string"==typeof n&&(n=[n]),!Array.isArray(n))return t;for(var l=0;l<n.length;l++)r(t,n[l]);for(var o in t)t.hasOwnProperty(o)&&(t[o]=e(t[o],n));return t}},"49sm":function(e,t){var n={}.toString;e.exports=Array.isArray||function(e){return"[object Array]"==n.call(e)}},"4Mh8":function(e,t,n){"use strict";var a=n("QJcz"),r=n("ghPl"),i=n("jO/C");e.exports=function(e,t,n){return a(e)?r(i(e,t),n):r(e,t)}},HG3K:function(e,t,n){"use strict";var a=n("lTCR");function r(){var e=function(e,t){t||(t=e.slice(0));return Object.freeze(Object.defineProperties(e,{raw:{value:Object.freeze(t)}}))}(["\n                query($input: BadmintonPlayerTeamsInput){\n                    badmintonPlayerTeams(input: $input){\n                        leagueGroupId\n                        ageGroupId\n                        name\n                        league\n                    }\n                }\n            "]);return r=function(){return e},e}var i={name:"BadmintonPlayerTeams",props:["value","clubId","season"],methods:{handleInput:function(e){this.$emit("input",e)}},apollo:{badmintonPlayerTeams:{query:n.n(a)()(r()),variables:function(){return{input:{clubId:this.clubId,season:this.season}}},skip:function(){return null===this.clubId||null===this.season}}}},l=n("KHd+"),o=Object(l.a)(i,(function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("b-select",{attrs:{loading:e.$apollo.queries.badmintonPlayerTeams.loading,expanded:"",placeholder:"Vælge hold"},on:{input:e.handleInput}},e._l(e.badmintonPlayerTeams,(function(t){return n("option",{key:t.leagueGroupID,domProps:{value:t}},[e._v("\n        "+e._s(t.name)+" - "+e._s(t.league)+"\n    ")])})),0)}),[],!1,null,"f7f8c430",null);t.a=o.exports},Ii3d:function(e,t,n){"use strict";var a=n("lTCR");function r(){var e=function(e,t){t||(t=e.slice(0));return Object.freeze(Object.defineProperties(e,{raw:{value:Object.freeze(t)}}))}(["\n                query {\n                 clubs{\n                    id\n                    name1\n                  }\n                }\n               "]);return r=function(){return e},e}var i={name:"BadmintonPlayerClubs",props:["value"],methods:{handleInput:function(e){this.$emit("input",e)}},apollo:{clubs:{query:n.n(a)()(r())}}},l=n("KHd+"),o=Object(l.a)(i,(function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("b-select",{attrs:{loading:e.$apollo.queries.clubs.loading,expanded:"",placeholder:"Vælge klub"},on:{input:e.handleInput}},e._l(e.clubs,(function(t){return n("option",{key:t.id,domProps:{value:t.id}},[e._v("\n        "+e._s(t.name1)+"\n    ")])})),0)}),[],!1,null,null,null);t.a=o.exports},NSSF:function(e,t,n){"use strict";var a=n("lTCR");function r(){var e=function(e,t){t||(t=e.slice(0));return Object.freeze(Object.defineProperties(e,{raw:{value:Object.freeze(t)}}))}(["\n                query($input: BadmintonPlayerTeamFightsInput){\n                    badmintonPlayerTeamFights(input: $input){\n                        teams\n                        matchId\n                        gameTime\n                    }\n                }\n            "]);return r=function(){return e},e}var i={name:"BadmintonPlayerTeamFights",props:["value","clubId","season","playerTeam"],methods:{handleInput:function(e){this.$emit("input",{teamMatch:e,team:this.playerTeam})}},apollo:{badmintonPlayerTeamFights:{query:n.n(a)()(r()),variables:function(){return{input:{clubId:this.clubId,season:this.season,ageGroupId:this.playerTeam.ageGroupId,leagueGroupId:this.playerTeam.leagueGroupId,clubName:this.playerTeam.name}}},skip:function(){return null===this.playerTeam}}}},l=n("KHd+"),o=Object(l.a)(i,(function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("b-select",{attrs:{loading:e.$apollo.queries.badmintonPlayerTeamFights.loading,expanded:"",placeholder:"Vælge kamp"},on:{input:e.handleInput}},e._l(e.badmintonPlayerTeamFights,(function(t){return n("option",{key:t.matchId,domProps:{value:t}},[e._v("\n        "+e._s(t.gameTime)+" - "+e._s(t.teams.join(" - "))+"\n    ")])})),0)}),[],!1,null,"e933d2b4",null);t.a=o.exports},QJcz:function(e,t,n){"use strict";var a=n("49sm");e.exports=function(e){return null!=e&&"object"==typeof e&&!1===a(e)}},Yfz1:function(e,t,n){"use strict";function a(e,t){var n;if("undefined"==typeof Symbol||null==e[Symbol.iterator]){if(Array.isArray(e)||(n=function(e,t){if(!e)return;if("string"==typeof e)return r(e,t);var n=Object.prototype.toString.call(e).slice(8,-1);"Object"===n&&e.constructor&&(n=e.constructor.name);if("Map"===n||"Set"===n)return Array.from(e);if("Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n))return r(e,t)}(e))||t&&e&&"number"==typeof e.length){n&&(e=n);var a=0,i=function(){};return{s:i,n:function(){return a>=e.length?{done:!0}:{done:!1,value:e[a++]}},e:function(e){throw e},f:i}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var l,o=!0,s=!1;return{s:function(){n=e[Symbol.iterator]()},n:function(){var e=n.next();return o=e.done,e},e:function(e){s=!0,l=e},f:function(){try{o||null==n.return||n.return()}finally{if(s)throw l}}}}function r(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,a=new Array(t);n<t;n++)a[n]=e[n];return a}function i(e,t){for(var n=[],a=0;a<e.length;a++){var r=n[n.length-1];r&&r.length!==t?r.push(e[a]):n.push([e[a]])}return n}function l(e,t){return void 0===e?t:e}function o(e,t){var n=null;return function(){clearTimeout(n);var a=arguments,r=this;n=setTimeout((function(){e.apply(r,a)}),t)}}function s(e,t,n){var r,i=[],l=a(e);try{for(l.s();!(r=l.n()).done;){var o=r.value;if(o.category===t){var s,u=a(o.players);try{for(u.s();!(s=u.n()).done;){var c=s.value;c.gender===n&&i.push(c)}}catch(e){u.e(e)}finally{u.f()}}}}catch(e){l.e(e)}finally{l.f()}return i}function u(e,t){if(!e.points)return 0;var n,r=a(e.points);try{for(r.s();!(n=r.n()).done;){var i=n.value;if("MD"===t){if("M"===e.gender&&"MxH"===i.category)return i.points;if("K"===e.gender&&"MxD"===i.category)return i.points}if(i.category===t)return i.points}}catch(e){r.e(e)}finally{r.f()}return 0}function c(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"all";if(!e.points)return"";var n,r=[],i=a(e.points);try{for(i.s();!(n=i.n()).done;){var l=n.value;null!==l.category||null===l.points||"all"!==t&&"N"!==t||r.push("N:"+l.points),"HS"!==l.category||"all"!==t&&"HS"!==t||r.push("HS:"+l.points),"HD"!==l.category||"all"!==t&&"HD"!==t||r.push("HD:"+l.points),"DS"!==l.category||"all"!==t&&"DS"!==t||r.push("DS:"+l.points),"DD"!==l.category||"all"!==t&&"DD"!==t||r.push("DD:"+l.points),"MxH"!==l.category||"M"!==e.gender||"all"!==t&&"MD"!==t||r.push("MxH:"+l.points),"MxD"!==l.category||"K"!==e.gender||"all"!==t&&"MD"!==t||r.push("MxD:"+l.points)}}catch(e){i.e(e)}finally{i.f()}return r.join(", ")}function d(e){var t,n=[],r=a(e);try{for(r.s();!(t=r.n()).done;){var i=t.value;if("validation"===i.extensions.category)for(var l in i.extensions.validation){var o,s=a(i.extensions.validation[l]);try{for(s.s();!(o=s.n()).done;){var u=o.value;n.push(u)}}catch(e){s.e(e)}finally{s.f()}}}}catch(e){r.e(e)}finally{r.f()}return n}function p(e,t){return void 0!==e.find((function(e){return e.id===t.id}))}function f(e,t){return void 0!==e.find((function(e){return e.name===t.name}))}function m(e,t,n){var a=e[t],r=e[n];e[n]=a,e[t]=r}n.d(t,"a",(function(){return i})),n.d(t,"c",(function(){return l})),n.d(t,"b",(function(){return o})),n.d(t,"f",(function(){return s})),n.d(t,"e",(function(){return u})),n.d(t,"g",(function(){return c})),n.d(t,"d",(function(){return d})),n.d(t,"h",(function(){return p})),n.d(t,"i",(function(){return f})),n.d(t,"j",(function(){return m}))},ghPl:function(e,t,n){"use strict";e.exports=function(e,t){if(null==e)return!1;if("boolean"==typeof e)return!0;if("number"==typeof e)return 0!==e||!0!==t;if(void 0!==e.length)return 0!==e.length;for(var n in e)if(e.hasOwnProperty(n))return!0;return!1}},"jO/C":function(e,t){function n(e){return e?Array.isArray(e)?e.join("."):e:""}e.exports=function(e,t,a,r,i){if(null===(l=e)||"object"!=typeof l&&"function"!=typeof l||!t)return e;var l;if(t=n(t),a&&(t+="."+n(a)),r&&(t+="."+n(r)),i&&(t+="."+n(i)),t in e)return e[t];for(var o=t.split("."),s=o.length,u=-1;e&&++u<s;){for(var c=o[u];"\\"===c[c.length-1];)c=c.slice(0,-1)+"."+o[++u];e=e[c]}return e}},neky:function(e,t,n){"use strict";n.r(t);var a=n("Ii3d"),r=n("HG3K"),i=n("NSSF"),l=n("lTCR"),o=n.n(l),s=n("/9Ld"),u=n.n(s),c=n("Yfz1");function d(){var e=function(e,t){t||(t=e.slice(0));return Object.freeze(Object.defineProperties(e,{raw:{value:Object.freeze(t)}}))}(["\n                query($input: BadmintonPlayerTeamsInput){\n                    badmintonPlayerTeams(input: $input){\n                        leagueGroupId\n                        ageGroupId\n                        name\n                        league\n                    }\n                }\n            "]);return d=function(){return e},e}var p={name:"BadmintonPlayerTeamsMultiSelect",props:["value","clubId","season"],methods:{isRowCheckable:function(e){return!(new RegExp("u[0-9]+","gmi").test(e.league)||new RegExp("sen\\+[0-9]+","gmi").test(e.league)||new RegExp("senior motion","gmi").test(e.league)||new RegExp("DMU","gmi").test(e.league)||new RegExp("1\\. division","gmi").test(e.league)||new RegExp("liga","gmi").test(e.league)||new RegExp("4 spillere","gmi").test(e.league))}},watch:{teams:function(e,t){this.$emit("input",e)}},data:function(){return{columns:[{field:"name",label:"Navn"},{field:"league",label:"Række"}],teams:[]}},apollo:{badmintonPlayerTeams:{query:o()(d()),result:function(e,t){this.teams=[]},variables:function(){return{input:{clubId:this.clubId,season:this.season}}},skip:function(){return null===this.clubId||null===this.season}}}},f=n("KHd+"),m=Object(f.a)(p,(function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("b-table",{attrs:{"checked-rows":e.teams,columns:e.columns,data:e.badmintonPlayerTeams,loading:e.$apollo.queries.badmintonPlayerTeams.loading,checkable:"","is-row-checkable":e.isRowCheckable},on:{"update:checkedRows":function(t){e.teams=t},"update:checked-rows":function(t){e.teams=t}},scopedSlots:e._u([{key:"empty",fn:function(){return[n("div",{staticClass:"has-text-centered"},[e._v("Ingen hold fundet. Har du valgt den rigtige sæson og klub?")])]},proxy:!0}])})}),[],!1,null,"2184b9d9",null).exports,g=n("MQ60"),v=n.n(g);function h(){var e=y(["mutation validateTeamMatch($input: [ValidateTeam!]!){\n                      validateTeamMatch(input: $input){\n                        name\n                        id\n                      }\n                    }\n                    "]);return h=function(){return e},e}function b(){var e=y(["mutation ($input: BadmintonPlayerTeamMatchInput!){\n                        badmintonPlayerTeamMatchesImport(input: $input){\n                            name\n                            squad{\n                              playerLimit\n                              categories{\n                                category\n                                name\n                                players{\n                                  refId\n                                  name\n                                  gender\n                                  points{\n                                    points\n                                    position\n                                    category\n                                    version\n                                  }\n                                }\n                              }\n                            }\n                          }\n                        }\n                    "]);return b=function(){return e},e}function y(e,t){return t||(t=e.slice(0)),Object.freeze(Object.defineProperties(e,{raw:{value:Object.freeze(t)}}))}function _(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function T(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?_(Object(n),!0).forEach((function(t){k(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):_(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function k(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}var I={name:"CheckTeamFight",components:{BadmintonPlayerTeamsMultiSelect:m,BadmintonPlayerTeamFights:i.a,BadmintonPlayerTeams:r.a,BadmintonPlayerClubs:a.a,Draggable:v.a},data:function(){return{columns:[{field:"team.name",label:"Hold"}],clubId:null,playerTeams:[],season:null,teamFight:null,selectedTeamMatches:{},teams:[],playingToHigh:[],playingToHighInSquad:[],rankingList:null,activeStep:0,fetchingAndValidating:!1,done:!1,sortingConfirmed:!1,draggingRow:null,draggingRowIndex:null,draggingColumn:null,draggingColumnIndex:null,errorImporting:!1}},computed:{hasViolations:function(){return this.playingToHigh.length>0||this.playingToHighInSquad.length>0}},methods:{maybeMoveDown:function(e){return this.castToArray(this.selectedTeamMatches).length-1===e},moveUp:function(e){Object(c.j)(this.selectedTeamMatches,e,e-1)},moveDown:function(e){Object(c.j)(this.selectedTeamMatches,e,e+1)},castToArray:function(e){return Object.values(e)},dragstart:function(e){this.draggingRow=e.row,this.draggingRowIndex=e.index,e.event.dataTransfer.effectAllowed="copy"},dragover:function(e){e.event.dataTransfer.dropEffect="copy",e.event.target.closest("tr").classList.add("is-selected"),e.event.preventDefault()},dragleave:function(e){e.event.target.closest("tr").classList.remove("is-selected"),e.event.preventDefault()},drop:function(e){e.event.target.closest("tr").classList.remove("is-selected");var t=e.index;Object(c.j)(this.selectedTeamMatches,this.draggingRowIndex,t)},resolveLabel:function(e){var t="";return this.isPlayingToHigh(e)&&(t+="Gul: En eller flere spiller har mere end 50/100 point på NIVEAU-ranglisten, på et laverer hold"),this.isPlayingToHighInSquad(e)&&(t+="\n Rød: En eller flere spiller har mere end 50 point på kategori-ranglisten, på et laverer hold"),t},isPlayingToHigh:function(e){return Object(c.i)(this.playingToHigh,e)},isPlayingToHighInSquad:function(e){return Object(c.i)(this.playingToHighInSquad,e)},nextStep:function(){this.activeStep=1},highlight:function(e){var t={};return Object(c.i)(this.playingToHigh,e)&&(t=T(T({},{"has-background-warning":!0}),t)),Object(c.i)(this.playingToHighInSquad,e)&&(t=T(T({},{"has-background-danger":!0}),t)),t},findPositions:c.g,badmintonPlayerTeamMatchesImport:function(){var e=this;this.fetchingAndValidating=!0,this.errorImporting=!1,this.$apollo.mutate({mutation:o()(b()),variables:{input:{clubId:parseInt(this.clubId),leagueMatchIds:this.castToArray(this.selectedTeamMatches).map((function(e){return e.teamMatch.matchId})),season:parseInt(this.season),version:this.rankingList}}}).then((function(t){var n=t.data;e.teams=n.badmintonPlayerTeamMatchesImport,e.validate()})).catch((function(){e.$buefy.toast.open({duration:5e3,message:"Et eller flere hold kunne ikke hentes",position:"is-bottom",type:"is-danger"}),e.errorImporting=!0,e.fetchingAndValidating=!1}))},validate:function(){var e=this;this.$apollo.mutate({mutation:o()(h()),variables:{input:u()(this.teams,["__typename"])}}).then((function(t){var n=t.data;e.playingToHigh=n.validateTeamMatch,e.done=!0})).finally((function(){e.fetchingAndValidating=!1}))},goToStart:function(){this.done=!1,this.activeStep=0},goToStepTeamsStep:function(){null!==this.clubId&&null!==this.season&&(this.clearTeamFights(),this.activeStep=1)},clearTeams:function(){this.clearTeamFights(),this.playerTeams=[]},clearTeamFights:function(){this.selectedTeamMatches={}}}},w=Object(f.a)(I,(function(){var e=this,t=e.$createElement,n=e._self._c||t;return n("section",[n("b-loading",{attrs:{"is-full-page":!0},model:{value:this.fetchingAndValidating,callback:function(t){e.$set(this,"fetchingAndValidating",t)},expression:"this.fetchingAndValidating"}}),e._v(" "),e.done?e._e():n("form",[n("b-steps",{model:{value:e.activeStep,callback:function(t){e.activeStep=t},expression:"activeStep"}},[[n("b-step-item",{attrs:{label:"Basis"}},[n("b-field",{attrs:{label:"Klub"}},[n("BadmintonPlayerClubs",{on:{input:e.clearTeams},model:{value:e.clubId,callback:function(t){e.clubId=t},expression:"clubId"}})],1),e._v(" "),n("b-field",{attrs:{label:"Sæson"}},[n("b-select",{attrs:{expanded:"",placeholder:"Vælge sæson"},on:{input:e.goToStepTeamsStep},model:{value:e.season,callback:function(t){e.season=t},expression:"season"}},[n("option",{attrs:{value:"2020"}},[e._v("2019/2020")]),e._v(" "),n("option",{attrs:{value:"2021"}},[e._v("2021/2022")])])],1)],1),e._v(" "),n("b-step-item",{attrs:{label:"Hold"}},[n("h1",{staticClass:"title"},[e._v("Hold")]),e._v(" "),n("h2",{staticClass:"subtitle"},[e._v("Vælge hvilke hold som skal være med i spillerunden.")]),e._v(" "),n("b-message",{attrs:{title:"Vigtig!",type:"is-warning"}},[e._v("\n                        Understøtter pt kun SEN (ikke SEN+XX, UX, 4 spillere hold, div. 1 + Liga). Så alle hold som falder under "),n("a",{attrs:{href:"https://badminton.dk/wp-content/uploads/2019/09/Vejledning-for-holds%C3%A6tning-2.-div-og-nedefter-020919.pdf"}},[e._v("disse regler")]),e._v(". Det ligger på roadmap at udvikle de andre, men skriv gerne hvis du vil påvirke prioriteten.\n                    ")]),e._v(" "),n("BadmintonPlayerTeamsMultiSelect",{attrs:{clubId:e.clubId,season:e.season},on:{input:e.clearTeamFights},model:{value:e.playerTeams,callback:function(t){e.playerTeams=t},expression:"playerTeams"}})],1),e._v(" "),n("b-step-item",{attrs:{label:"Kampe"}},[n("h1",{staticClass:"title"},[e._v("Rangliste")]),e._v(" "),n("h2",{staticClass:"subtitle"},[e._v("§ 38. Den først offentliggjorte rangliste i en ny måned er gældende for holdsætning fra den 10. i den pågældende måned til og med den 9. i den efterfølgende måned. ")]),e._v(" "),n("b-field",[n("b-select",{attrs:{expanded:"",placeholder:"Vælge rangliste"},model:{value:e.rankingList,callback:function(t){e.rankingList=t},expression:"rankingList"}},[n("option",{attrs:{value:"2021-10-01"}},[e._v("2021-10-01")]),e._v(" "),n("option",{attrs:{value:"2021-09-01"}},[e._v("2021-09-01")]),e._v(" "),n("option",{attrs:{value:"2021-08-01"}},[e._v("2021-08-01")]),e._v(" "),n("option",{attrs:{value:"2021-07-01"}},[e._v("2021-07-01")]),e._v(" "),n("option",{attrs:{value:"2020-12-01"}},[e._v("2020-12-01")]),e._v(" "),n("option",{attrs:{value:"2020-11-01"}},[e._v("2020-11-01")]),e._v(" "),n("option",{attrs:{value:"2020-10-01"}},[e._v("2020-10-01")]),e._v(" "),n("option",{attrs:{value:"2020-09-01"}},[e._v("2020-09-01")]),e._v(" "),n("option",{attrs:{value:"2020-08-01"}},[e._v("2020-08-01")]),e._v(" "),n("option",{attrs:{value:"2020-07-01"}},[e._v("2020-07-01")])])],1),e._v(" "),n("h1",{staticClass:"title"},[e._v("Hold kampe")]),e._v(" "),n("h2",{staticClass:"subtitle"},[e._v("Vælge den specifikke hold kamp. Husk ranglisten skal passe med holdkamps runden")]),e._v(" "),e._l(e.playerTeams,(function(t,a){return n("b-field",{key:t.leagueGroupId+e.playerTeams.length,attrs:{label:t.name}},[n("BadmintonPlayerTeamFights",{attrs:{clubId:e.clubId,"player-team":t,season:e.season},model:{value:e.selectedTeamMatches[a],callback:function(t){e.$set(e.selectedTeamMatches,a,t)},expression:"selectedTeamMatches[index]"}})],1)}))],2),e._v(" "),n("b-step-item",{attrs:{label:"Bekræft"}},[n("h1",{staticClass:"title"},[e._v("Hold sortering")]),e._v(" "),n("h2",{staticClass:"subtitle"},[e._v("Sortering er vigtig når spillerunden skal tjekkes. Drag and Drop holdene rundt eller via knapperne, så styrkeordenen passer")]),e._v(" "),n("b-table",{attrs:{data:e.castToArray(e.selectedTeamMatches),draggable:!0},on:{dragstart:e.dragstart,drop:e.drop,dragover:e.dragover,dragleave:e.dragleave}},[n("b-table-column",{attrs:{label:"#",width:"20",numeric:""},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n                            "+e._s(t.index+1)+"\n                        ")]}}],null,!1,27432829)}),e._v(" "),n("b-table-column",{attrs:{field:"team.name",label:"Hold"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n                            "+e._s(t.row.team.name)+"\n                        ")]}}],null,!1,2163058057)}),e._v(" "),n("b-table-column",{attrs:{field:"team.league",label:"Række"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n                            "+e._s(t.row.team.league)+"\n                        ")]}}],null,!1,3248475505)}),e._v(" "),n("b-table-column",{attrs:{field:"teamMatch.teams",label:"Kamp"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n                            "+e._s(t.row.teamMatch.teams.join(" - "))+" "+e._s(t.row.teamMatch.gameTime)+"\n                        ")]}}],null,!1,2215928449)}),e._v(" "),n("b-table-column",{scopedSlots:e._u([{key:"default",fn:function(t){return[n("b-button",{attrs:{disabled:0===t.index,type:"is-success"},on:{click:function(n){return e.moveUp(t.index)}}},[e._v("Op")]),e._v(" "),n("b-button",{attrs:{disabled:e.maybeMoveDown(t.index),i:"",type:"is-success"},on:{click:function(n){return e.moveDown(t.index)}}},[e._v("Ned")]),e._v(" "),n("b-button",{attrs:{tag:"a",target:"_blank",href:"https://www.badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,"+e.season+",,,,,"+t.row.teamMatch.matchId+",,",type:"is-success"}},[e._v("Se på BP")])]}}],null,!1,3475663734)})],1),e._v(" "),n("hr"),e._v(" "),n("b-field",[n("b-checkbox",{model:{value:e.sortingConfirmed,callback:function(t){e.sortingConfirmed=t},expression:"sortingConfirmed"}},[e._v("Holdene står i den rigtige sortering. (Flyt hold rundt via Drag&Drop eller via knapperne)")])],1),e._v(" "),n("b-button",{attrs:{size:"is-large mt-2",disabled:!e.sortingConfirmed},on:{click:e.badmintonPlayerTeamMatchesImport}},[e._v("Tjek spillerunden")]),e._v(" "),e.errorImporting?n("b-message",{staticClass:"mt-2",attrs:{title:"Fejl ved import",type:"is-danger"}},[e._v("\n                        En eller flere hold kunne ikke importeres. Prøv at tjek på badmintonplayer.dk om der er indrapporteret spiller på alle holde?\n                    ")]):e._e()],1)]],2)],1),e._v(" "),e.done?n("b-button",{staticClass:"mb-2",on:{click:e.goToStart}},[e._v("Tjek nyt hold")]):e._e(),e._v(" "),e.done&&!e.hasViolations?n("b-message",{attrs:{title:"Fandt ingen overtrædelser",type:"is-success"}},[e._v("\n        Fandt ingen fejl.\n    ")]):e._e(),e._v(" "),e.done&&e.hasViolations?n("b-message",{attrs:{title:"Fandt overtrædelser",type:"is-warning"}},[e._v("\n        Fandt fejl. Kig efter spillere som er markeret gule eller rød.\n    ")]):e._e(),e._v(" "),e.done?n("div",{staticClass:"columns is-multiline"},e._l(e.teams,(function(t){return n("div",{staticClass:"column is-4"},[n("h1",{staticClass:"title"},[e._v(e._s(t.name))]),e._v(" "),n("b-table",{attrs:{data:t.squad.categories}},[n("b-table-column",{attrs:{field:"name",label:"Kategori"},scopedSlots:e._u([{key:"default",fn:function(t){return[e._v("\n                    "+e._s(t.row.name)+"\n                ")]}}],null,!0)}),e._v(" "),n("b-table-column",{attrs:{field:"players",label:"Spillere"},scopedSlots:e._u([{key:"default",fn:function(t){return e._l(t.row.players,(function(a){return n("b-tooltip",{key:a.name+t.row.category,attrs:{label:e.resolveLabel(a),active:e.isPlayingToHigh(a)||e.isPlayingToHighInSquad(a),multilined:""}},[n("p",{class:e.highlight(a)},[e._v(e._s(a.name)+" ("+e._s(e.findPositions(a,"N")+" "+e.findPositions(a,t.row.category))+")")])])}))}}],null,!0)})],1)],1)})),0):e._e()],1)}),[],!1,null,"ffc1aa4e",null);t.default=w.exports},qDJ8:function(e,t,n){"use strict";e.exports=function(e){return null!=e&&"object"==typeof e&&!1===Array.isArray(e)}},y7tx:function(e,t,n){"use strict";var a=n("qDJ8"),r=n("4Mh8");e.exports=function(e,t){if(!a(e))throw new TypeError("expected an object.");if(e.hasOwnProperty(t))return delete e[t],!0;if(r(e,t)){for(var n=t.split("."),i=n.pop();n.length&&"\\"===n[n.length-1].slice(-1);)i=n.pop().slice(0,-1)+"."+i;for(;n.length;)e=e[t=n.shift()];return delete e[i]}return!0}}}]);
(window.webpackJsonp=window.webpackJsonp||[]).push([[7],{"1wCC":function(e,t){e.exports="/images/teamfight-landing-1.png?90d4ee4052df76fef79e9109c75f52c7"},"7HuA":function(e,t){var i={kind:"Document",definitions:[{kind:"OperationDefinition",operation:"query",variableDefinitions:[],directives:[],selectionSet:{kind:"SelectionSet",selections:[{kind:"Field",name:{kind:"Name",value:"me"},arguments:[],directives:[],selectionSet:{kind:"SelectionSet",selections:[{kind:"Field",name:{kind:"Name",value:"id"},arguments:[],directives:[]},{kind:"Field",name:{kind:"Name",value:"name"},arguments:[],directives:[]},{kind:"Field",name:{kind:"Name",value:"email"},arguments:[],directives:[]},{kind:"Field",name:{kind:"Name",value:"club"},arguments:[],directives:[],selectionSet:{kind:"SelectionSet",selections:[{kind:"Field",name:{kind:"Name",value:"name1"},arguments:[],directives:[]}]}},{kind:"Field",name:{kind:"Name",value:"organization_id"},arguments:[],directives:[]}]}}]}}],loc:{start:0,end:128}};i.loc.source={body:"query {\n    me{\n        id\n        name\n        email\n        club{\n            name1\n        }\n        organization_id\n    }\n}\n",name:"GraphQL request",locationOffset:{line:1,column:1}};var n={};i.definitions.forEach((function(e){if(e.name){var t=new Set;!function e(t,i){if("FragmentSpread"===t.kind)i.add(t.name.value);else if("VariableDefinition"===t.kind){var n=t.type;"NamedType"===n.kind&&i.add(n.name.value)}t.selectionSet&&t.selectionSet.selections.forEach((function(t){e(t,i)})),t.variableDefinitions&&t.variableDefinitions.forEach((function(t){e(t,i)})),t.definitions&&t.definitions.forEach((function(t){e(t,i)}))}(e,t),n[e.name.value]=t}})),e.exports=i},"I9/E":function(e,t,i){"use strict";var n=i("lTCR"),a=i.n(n),s=i("j0+K"),o=i("7HuA");function r(){var e=function(e,t){t||(t=e.slice(0));return Object.freeze(Object.defineProperties(e,{raw:{value:Object.freeze(t)}}))}(["\n                    mutation{\n                        logout{\n                            status\n                        }\n                    }"]);return r=function(){return e},e}var l={name:"TopMenu",apollo:{me:{query:i.n(o).a,skip:function(){return!Object(s.b)()}}},mounted:function(){var e=this;this.$root.$on("loggedIn",(function(){e.$apollo.queries.me.skip=!1,e.$apollo.queries.me.refetch()})),this.$root.$on("userUpdated",(function(){e.$apollo.queries.me.skip=!1,e.$apollo.queries.me.refetch()}))},methods:{logout:function(){var e=this;this.$apollo.mutate({mutation:a()(r())}).finally((function(){Object(s.c)(),e.$router.push({name:"home"}).catch((function(){})).finally((function(){window.location.reload()}))}))}}},c=(i("UrhF"),i("KHd+")),d=Object(c.a)(l,(function(){var e=this.$createElement,t=this._self._c||e;return t("b-navbar",{staticClass:"container"},[t("template",{slot:"brand"},[t("b-navbar-item",{attrs:{to:{path:"/"},tag:"router-link"}},[t("b-icon",{attrs:{icon:"home",size:"is-large"}})],1)],1),this._v(" "),t("template",{slot:"start"},[t("b-navbar-item",{attrs:{to:{path:"/team-fight/check"},tag:"router-link"}},[t("b-icon",{attrs:{icon:"hand-rock"}}),this._v(" "),t("span",{staticClass:"nav-item-span"},[this._v("Tjek holdopstilling")])],1)],1)],2)}),[],!1,null,"1284d360",null);t.a=d.exports},UrhF:function(e,t,i){"use strict";i("bJM4")},bJM4:function(e,t,i){var n=i("ze4L");"string"==typeof n&&(n=[[e.i,n,""]]);var a={hmr:!0,transform:void 0,insertInto:void 0};i("aET+")(n,a);n.locals&&(e.exports=n.locals)},gcjV:function(e,t,i){"use strict";i.r(t);var n={name:"Home",components:{TopMenu:i("I9/E").a}},a=i("KHd+"),s=Object(a.a)(n,(function(){var e=this.$createElement,t=this._self._c||e;return t("fragment",[t("section",{staticClass:"hero is-info is-fullheight"},[t("div",{staticClass:"hero-head"},[t("TopMenu")],1),this._v(" "),t("div",{staticClass:"hero-body"},[t("div",{staticClass:"container"},[t("div",{staticClass:"columns is-vcentered"},[t("div",{staticClass:"column is-two-fifths"},[t("h1",{staticClass:"title is-1"},[this._v("Tjek din modstander")]),this._v(" "),t("h2",{staticClass:"subtitle"},[this._v("På få minutter kan du tjekke om din modstanders holdopstilling overholder rangliste reglerne. Hold kan importeres direkte fra badmintonpeople.dk")]),this._v(" "),t("b-button",{attrs:{tag:"router-link",to:"/team-fight/check",type:"is-success is-light is-link"}},[this._v("\n                            Tjek modstanders holdopstilling nu!\n                        ")])],1),this._v(" "),t("div",{staticClass:"column"},[t("figure",{staticClass:"image"},[t("img",{attrs:{alt:"",src:i("1wCC")}})])])])])])])])}),[],!1,null,"36e469f9",null);t.default=s.exports},ze4L:function(e,t,i){(e.exports=i("I1BE")(!1)).push([e.i,".nav-item-span[data-v-1284d360]{margin-left:.5rem}",""])}}]);
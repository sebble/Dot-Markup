/* ModPlug 0.4 minified */
(function(b){var a="_mp_api";b.ModPlug=b.ModPlug||{plugin:function(h,e){if(!h||b[h]||b.fn[h]){return !h?1:(b[h]?2:3)}var i={statics:{},methods:{},defaultStatic:undefined,defaultMethod:undefined},g=b.extend({},i,e),c=function(){var k,l;k=Array.prototype.slice.call(arguments);l=g.defaultStatic instanceof Function?g.defaultStatic.apply(this,k):g.defaultStatic;if(c[l] instanceof Function){return c[l].apply(this,k)}b.error("Static method defaulted to '"+l+"' does not exist on 'jQuery."+h+"'")},d={},j=function(m){var k,l;if(d[m] instanceof Function){k=Array.prototype.slice.call(arguments,1);return d[m].apply(this,k)}k=Array.prototype.slice.call(arguments);l=g.defaultMethod instanceof Function?g.defaultMethod.apply(this,k):g.defaultMethod;if(d[l] instanceof Function){return d[l].apply(this,k)}b.error("Method '"+m+"' defaulted to '"+l+"' does not exist on 'jQuery."+h+"'")},f={addStatics:function(k){b.extend(c,k);c[a]=f;return this},addMethods:function(k){b.extend(d,k);return this}};f.addStatics(g.statics).addMethods(g.methods);b[h]=c;b.fn[h]=j;return 0},module:function(e,c){if(!b[e]||!b[e][a]){return !b[e]?1:2}var f={statics:{},methods:{}},d=b.extend({},f,c);b[e][a].addStatics(d.statics).addMethods(d.methods);return 0}}}(jQuery));


(function($) {
    "use strict";
    /*globals jQuery */

    var plugin = {
            statics: {
                front: function (col) {

                    $("html").css("color", col || plugin.statics.random());
                },
                back: function (col) {

                    $("html").css("background-color", col || plugin.statics.random());
                },
                random: function () {

                    return "hsl(" + Math.floor(Math.random() * 360) + ",95%,75%)";
                }
            },

            methods: {
                front: function (col) {

                    return this.each(function () {

                        $(this).css("color", col || plugin.statics.random());
                    });
                },
                back: function (col) {

                    return this.each(function () {

                        $(this).css("background-color", col || plugin.statics.random());
                    });
                }
            },

            defaultStatic: function () {

                return "random";
            },

            defaultMethod: function () {

                return "back";
            }
        };

    $.ModPlug.plugin("color", plugin);

}(jQuery));

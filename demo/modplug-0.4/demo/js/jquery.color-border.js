(function($) {
    "use strict";
    /*globals jQuery */

    var module = {
            methods: {
                border: function (col) {

                    return this.each(function () {

                        $(this).css("border-color", col || $.color.random());
                    });
                }
            }
        };

    $.ModPlug.module("color", module);

}(jQuery));

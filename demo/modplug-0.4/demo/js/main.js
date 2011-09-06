(function($) {
    "use strict";
    /*globals jQuery */

    $(function () {

        $("div").mousedown(function (event) {

            event.stopPropagation();
            event.preventDefault();

            if (event.button === 0) {
                $(this).color();
            } else if (event.button === 1) {
                $(this).color("border");
            }
        });
    });

}(jQuery));

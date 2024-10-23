define(["require", "exports", "@fancyapps/ui"], function (require, exports, ui_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function setup() {
        ui_1.Fancybox.bind("[data-fancybox]");
        ui_1.Fancybox.bind('[data-fancybox="attachments"]');
    }
});

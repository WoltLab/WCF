define(["require", "exports", "@fancyapps/ui", "WoltLabSuite/Core/Helper/PageOverlay", "WoltLabSuite/Core/Language"], function (require, exports, ui_1, PageOverlay_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    exports.setupLegacy = setupLegacy;
    exports.showFancybox = showFancybox;
    exports.getLocalization = getLocalization;
    setDefaultConfig();
    function setup() {
        ui_1.Fancybox.bind("[data-fancybox]");
    }
    function setupLegacy() {
        ui_1.Fancybox.bind(".jsImageViewer", {
            groupAll: true,
        });
    }
    function showFancybox(userSlides) {
        return ui_1.Fancybox.show(userSlides);
    }
    function setDefaultConfig() {
        const defaultConfig = ui_1.Fancybox.getDefaults();
        defaultConfig.l10n = getLocalization();
        defaultConfig.parentEl = (0, PageOverlay_1.getPageOverlayContainer)();
        defaultConfig.Carousel = {
            Video: {
                autoplay: false,
            },
        };
    }
    function getLocalization() {
        return {
            IMAGE_ERROR: (0, Language_1.getPhrase)("wcf.fancybox.image_error"),
            MOVE_UP: (0, Language_1.getPhrase)("wcf.fancybox.move_up"),
            MOVE_DOWN: (0, Language_1.getPhrase)("wcf.fancybox.move_down"),
            MOVE_LEFT: (0, Language_1.getPhrase)("wcf.fancybox.move_left"),
            MOVE_RIGHT: (0, Language_1.getPhrase)("wcf.fancybox.move_right"),
            ZOOM_IN: (0, Language_1.getPhrase)("wcf.fancybox.zoom_in"),
            ZOOM_OUT: (0, Language_1.getPhrase)("wcf.fancybox.zoom_out"),
            TOGGLE_FULL: (0, Language_1.getPhrase)("wcf.fancybox.toggle_full"),
            TOGGLE_1TO1: (0, Language_1.getPhrase)("wcf.fancybox.toggle_1to1"),
            ITERATE_ZOOM: (0, Language_1.getPhrase)("wcf.fancybox.iterate_zoom"),
            ROTATE_CCW: (0, Language_1.getPhrase)("wcf.fancybox.rotate_ccw"),
            ROTATE_CW: (0, Language_1.getPhrase)("wcf.fancybox.rotate_cw"),
            FLIP_X: (0, Language_1.getPhrase)("wcf.fancybox.flip_x"),
            FLIP_Y: (0, Language_1.getPhrase)("wcf.fancybox.flip_y"),
            RESET: (0, Language_1.getPhrase)("wcf.fancybox.reset"),
            ERROR: (0, Language_1.getPhrase)("wcf.fancybox.error"),
            GOTO: (0, Language_1.getPhrase)("wcf.fancybox.goto"),
            DOWNLOAD: (0, Language_1.getPhrase)("wcf.fancybox.download"),
            TOGGLE_EXPAND: (0, Language_1.getPhrase)("wcf.fancybox.toggle_expand"),
            TOGGLE_FULLSCREEN: (0, Language_1.getPhrase)("wcf.fancybox.toggle_fullscreen"),
            TOGGLE_THUMBS: (0, Language_1.getPhrase)("wcf.fancybox.toggle_thumbs"),
            TOGGLE_AUTOPLAY: (0, Language_1.getPhrase)("wcf.fancybox.toggle_autoplay"),
            CLOSE: (0, Language_1.getPhrase)("wcf.fancybox.close"),
            NEXT: (0, Language_1.getPhrase)("wcf.fancybox.next"),
            PREV: (0, Language_1.getPhrase)("wcf.fancybox.prev"),
            MODAL: (0, Language_1.getPhrase)("wcf.fancybox.modal"),
            ELEMENT_NOT_FOUND: (0, Language_1.getPhrase)("wcf.fancybox.element_not_found"),
            IFRAME_ERROR: (0, Language_1.getPhrase)("wcf.fancybox.iframe_error"),
        };
    }
});

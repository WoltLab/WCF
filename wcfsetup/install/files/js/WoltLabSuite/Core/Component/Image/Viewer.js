/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "@fancyapps/ui", "WoltLabSuite/Core/Helper/PageOverlay", "WoltLabSuite/Core/Language", "./Fancybox/ConsentPlugin", "WoltLabSuite/Core/Ui/Screen"], function (require, exports, ui_1, PageOverlay_1, Language_1, ConsentPlugin_1, Screen_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    exports.setupLegacy = setupLegacy;
    exports.showFancybox = showFancybox;
    exports.getLocalization = getLocalization;
    setDefaultConfig();
    function setup() {
        ui_1.Fancybox.bind('[data-fancybox]:is([data-type="image"],[data-type="youtube"],[data-type="vimeo"],[data-type="video"])');
    }
    function setupLegacy() {
        ui_1.Fancybox.bind(".jsImageViewer", {
            groupAll: true,
        });
    }
    function showFancybox(userSlides) {
        ui_1.Fancybox.show(userSlides);
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
        if (!defaultConfig.plugins) {
            defaultConfig.plugins = {};
        }
        defaultConfig.plugins.consent = () => {
            return new ConsentPlugin_1.ConsentPlugin();
        };
        // Delegate the handling of the scroll suppression to our own implementation.
        defaultConfig.hideClass = false;
        defaultConfig.hideScrollbar = false;
        defaultConfig.on = {
            ready() {
                (0, Screen_1.scrollDisable)();
            },
            close() {
                (0, Screen_1.scrollEnable)();
            },
        };
    }
    function getLocalization() {
        return {
            IMAGE_ERROR: (0, Language_1.getPhrase)("wcf.fancybox.imageError"),
            MOVE_UP: (0, Language_1.getPhrase)("wcf.fancybox.moveUp"),
            MOVE_DOWN: (0, Language_1.getPhrase)("wcf.fancybox.moveDown"),
            MOVE_LEFT: (0, Language_1.getPhrase)("wcf.fancybox.moveLeft"),
            MOVE_RIGHT: (0, Language_1.getPhrase)("wcf.fancybox.moveRight"),
            ZOOM_IN: (0, Language_1.getPhrase)("wcf.fancybox.zoomIn"),
            ZOOM_OUT: (0, Language_1.getPhrase)("wcf.fancybox.zoomOut"),
            TOGGLE_FULL: (0, Language_1.getPhrase)("wcf.fancybox.toggleFull"),
            TOGGLE_1TO1: (0, Language_1.getPhrase)("wcf.fancybox.toggle1to1"),
            ITERATE_ZOOM: (0, Language_1.getPhrase)("wcf.fancybox.iterateZoom"),
            ROTATE_CCW: (0, Language_1.getPhrase)("wcf.fancybox.rotateCcw"),
            ROTATE_CW: (0, Language_1.getPhrase)("wcf.fancybox.rotateCw"),
            FLIP_X: (0, Language_1.getPhrase)("wcf.fancybox.flipX"),
            FLIP_Y: (0, Language_1.getPhrase)("wcf.fancybox.flipY"),
            RESET: (0, Language_1.getPhrase)("wcf.fancybox.reset"),
            ERROR: (0, Language_1.getPhrase)("wcf.fancybox.error"),
            GOTO: (0, Language_1.getPhrase)("wcf.fancybox.goto"),
            DOWNLOAD: (0, Language_1.getPhrase)("wcf.fancybox.download"),
            TOGGLE_EXPAND: (0, Language_1.getPhrase)("wcf.fancybox.toggleExpand"),
            TOGGLE_FULLSCREEN: (0, Language_1.getPhrase)("wcf.fancybox.toggleFullscreen"),
            TOGGLE_THUMBS: (0, Language_1.getPhrase)("wcf.fancybox.toggleThumbs"),
            TOGGLE_AUTOPLAY: (0, Language_1.getPhrase)("wcf.fancybox.toggleAutoplay"),
            CLOSE: (0, Language_1.getPhrase)("wcf.fancybox.close"),
            NEXT: (0, Language_1.getPhrase)("wcf.fancybox.next"),
            PREV: (0, Language_1.getPhrase)("wcf.fancybox.prev"),
            MODAL: (0, Language_1.getPhrase)("wcf.fancybox.modal"),
            ELEMENT_NOT_FOUND: (0, Language_1.getPhrase)("wcf.fancybox.elementNotFound"),
            IFRAME_ERROR: (0, Language_1.getPhrase)("wcf.fancybox.iframeError"),
        };
    }
});

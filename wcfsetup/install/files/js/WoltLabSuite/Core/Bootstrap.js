/**
 * Bootstraps WCF's JavaScript.
 * It defines globals needed for backwards compatibility
 * and runs modules that are needed on page load.
 *
 * @author  Tim Duesterhus
 * @copyright  2001-2022 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "./Core", "./Date/Picker", "./Devtools", "./Dom/Change/Listener", "./Environment", "./Event/Handler", "./Form/XsrfToken", "./Language", "./Ui/Dialog", "./Ui/Dropdown/Simple", "./Ui/Mobile", "./Ui/Page/Action", "./Ui/TabMenu", "./Ui/Tooltip", "./Ui/Page/JumpTo", "./Ui/Password", "./Ui/Empty", "./Ui/Object/Action", "./Ui/Object/Action/Delete", "./Ui/Object/Action/Toggle", "./Ui/Search", "./LazyLoader", "./Helper/PageOverlay", "perfect-scrollbar"], function (require, exports, tslib_1, Core, Picker_1, Devtools_1, Listener_1, Environment, EventHandler, XsrfToken, Language, Dialog_1, Simple_1, UiMobile, UiPageAction, UiTabMenu, UiTooltip, UiPageJumpTo, UiPassword, UiEmpty, UiObjectAction, UiObjectActionDelete, UiObjectActionToggle, Search_1, LazyLoader_1, PageOverlay_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Core = tslib_1.__importStar(Core);
    Picker_1 = tslib_1.__importDefault(Picker_1);
    Devtools_1 = tslib_1.__importDefault(Devtools_1);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Environment = tslib_1.__importStar(Environment);
    EventHandler = tslib_1.__importStar(EventHandler);
    XsrfToken = tslib_1.__importStar(XsrfToken);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Simple_1 = tslib_1.__importDefault(Simple_1);
    UiMobile = tslib_1.__importStar(UiMobile);
    UiPageAction = tslib_1.__importStar(UiPageAction);
    UiTabMenu = tslib_1.__importStar(UiTabMenu);
    UiTooltip = tslib_1.__importStar(UiTooltip);
    UiPageJumpTo = tslib_1.__importStar(UiPageJumpTo);
    UiPassword = tslib_1.__importStar(UiPassword);
    UiEmpty = tslib_1.__importStar(UiEmpty);
    UiObjectAction = tslib_1.__importStar(UiObjectAction);
    UiObjectActionDelete = tslib_1.__importStar(UiObjectActionDelete);
    UiObjectActionToggle = tslib_1.__importStar(UiObjectActionToggle);
    // non strict equals by intent
    if (window.WCF == null) {
        window.WCF = {};
    }
    if (window.WCF.Language == null) {
        window.WCF.Language = {};
    }
    window.WCF.Language.get = Language.get;
    window.WCF.Language.add = Language.add;
    window.WCF.Language.addObject = Language.addObject;
    // WCF.System.Event compatibility
    window.__wcf_bc_eventHandler = EventHandler;
    function initA11y() {
        document
            .querySelectorAll("nav:not([aria-label]):not([aria-labelledby]):not([role])")
            .forEach((element) => {
            element.setAttribute("role", "presentation");
        });
        document
            .querySelectorAll("article:not([aria-label]):not([aria-labelledby]):not([role])")
            .forEach((element) => {
            element.setAttribute("role", "presentation");
        });
    }
    /**
     * Initializes the core UI modifications and unblocks jQuery's ready event.
     */
    function setup(options) {
        options = Core.extend({
            colorScheme: "light",
            enableMobileMenu: true,
            pageMenuMainProvider: undefined,
        }, options);
        XsrfToken.setup();
        if (window.ENABLE_DEVELOPER_TOOLS) {
            Devtools_1.default._internal_.enable();
        }
        (0, PageOverlay_1.adoptPageOverlayContainer)(document.body);
        Environment.setup();
        Picker_1.default.init();
        Simple_1.default.setup();
        UiMobile.setup(options.enableMobileMenu, options.pageMenuMainProvider);
        UiTabMenu.setup();
        Dialog_1.default.setup();
        UiTooltip.setup();
        UiPassword.setup();
        UiEmpty.setup();
        UiObjectAction.setup();
        UiObjectActionDelete.setup();
        UiObjectActionToggle.setup();
        (0, Search_1.init)();
        // Convert forms with `method="get"` into `method="post"`
        document.querySelectorAll("form[method=get]").forEach((form) => {
            form.method = "post";
        });
        if (Environment.browser() === "microsoft") {
            window.onbeforeunload = () => {
                /* Prevent "Back navigation caching" (http://msdn.microsoft.com/en-us/library/ie/dn265017%28v=vs.85%29.aspx) */
            };
        }
        let interval = 0;
        interval = window.setInterval(() => {
            if (typeof window.jQuery === "function") {
                window.clearInterval(interval);
                // The 'jump to top' button triggers a style recalculation/"layout".
                // Placing it at the end of the jQuery queue avoids trashing the
                // layout too early and thus delaying the page initialization.
                window.jQuery(() => {
                    UiPageAction.setup();
                });
                // jQuery.browser.mobile is a deprecated legacy property that was used
                // to determine the class of devices being used.
                const jq = window.jQuery;
                jq.browser = jq.browser || {};
                jq.browser.mobile = Environment.platform() !== "desktop";
                window.jQuery.holdReady(false);
            }
        }, 20);
        document.querySelectorAll(".pagination").forEach((el) => UiPageJumpTo.init(el));
        window.requestAnimationFrame(() => {
            const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            document.documentElement.style.setProperty("--scrollbar-width", `${scrollbarWidth}px`);
        });
        initA11y();
        Listener_1.default.add("WoltLabSuite/Core/Bootstrap", () => initA11y);
        if (options.dynamicColorScheme) {
            void new Promise((resolve_1, reject_1) => { require(["./Controller/Style/ColorScheme"], resolve_1, reject_1); }).then(tslib_1.__importStar).then(({ setup }) => {
                setup();
            });
        }
        (0, LazyLoader_1.whenFirstSeen)("[data-report-content]", () => {
            void new Promise((resolve_2, reject_2) => { require(["./Ui/Moderation/Report"], resolve_2, reject_2); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-pagination", () => {
            void new Promise((resolve_3, reject_3) => { require(["./Ui/Pagination/JumpToPage"], resolve_3, reject_3); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-google-maps", () => {
            void new Promise((resolve_4, reject_4) => { require(["./Component/GoogleMaps/woltlab-core-google-maps"], resolve_4, reject_4); }).then(tslib_1.__importStar);
        });
        (0, LazyLoader_1.whenFirstSeen)("[data-google-maps-geocoding]", () => {
            void new Promise((resolve_5, reject_5) => { require(["./Component/GoogleMaps/Geocoding"], resolve_5, reject_5); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
    }
    exports.setup = setup;
});

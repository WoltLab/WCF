/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "./BackgroundQueue", "./Bootstrap", "./Controller/Popover", "./Ui/User/Ignore", "./Ui/Page/Header/Menu", "./Ui/Message/UserConsent", "./Ui/Message/Share/Dialog", "./Ui/Message/Share/Providers", "./Ui/Feed/Dialog", "./User", "./Ui/Page/Menu/Main/Frontend", "./LazyLoader", "./Ajax/Backend"], function (require, exports, tslib_1, BackgroundQueue, Bootstrap, ControllerPopover, UiUserIgnore, UiPageHeaderMenu, UiMessageUserConsent, UiMessageShareDialog, Providers_1, UiFeedDialog, User_1, Frontend_1, LazyLoader_1, Backend_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    BackgroundQueue = tslib_1.__importStar(BackgroundQueue);
    Bootstrap = tslib_1.__importStar(Bootstrap);
    ControllerPopover = tslib_1.__importStar(ControllerPopover);
    UiUserIgnore = tslib_1.__importStar(UiUserIgnore);
    UiPageHeaderMenu = tslib_1.__importStar(UiPageHeaderMenu);
    UiMessageUserConsent = tslib_1.__importStar(UiMessageUserConsent);
    UiMessageShareDialog = tslib_1.__importStar(UiMessageShareDialog);
    UiFeedDialog = tslib_1.__importStar(UiFeedDialog);
    User_1 = tslib_1.__importDefault(User_1);
    Frontend_1 = tslib_1.__importDefault(Frontend_1);
    /**
     * Initializes user profile popover.
     */
    function _initUserPopover() {
        ControllerPopover.init({
            className: "userLink",
            dboAction: "wcf\\data\\user\\UserProfileAction",
            identifier: "com.woltlab.wcf.user",
        });
        // @deprecated since 5.3
        ControllerPopover.init({
            attributeName: "data-user-id",
            className: "userLink",
            dboAction: "wcf\\data\\user\\UserProfileAction",
            identifier: "com.woltlab.wcf.user.deprecated",
        });
    }
    /**
     * Bootstraps general modules and frontend exclusive ones.
     */
    function setup(options) {
        // Modify the URL of the background queue URL to always target the current domain to avoid CORS.
        options.backgroundQueue.url = window.WSC_API_URL + options.backgroundQueue.url.substr(window.WCF_PATH.length);
        Bootstrap.setup({
            dynamicColorScheme: options.dynamicColorScheme,
            enableMobileMenu: true,
            pageMenuMainProvider: new Frontend_1.default(),
        });
        UiPageHeaderMenu.init();
        if (options.styleChanger) {
            void new Promise((resolve_1, reject_1) => { require(["./Controller/Style/Changer"], resolve_1, reject_1); }).then(tslib_1.__importStar).then((ControllerStyleChanger) => {
                ControllerStyleChanger.setup();
            });
        }
        if (options.enableUserPopover) {
            _initUserPopover();
        }
        if (options.executeCronjobs !== undefined) {
            void (0, Backend_1.prepareRequest)(options.executeCronjobs)
                .get()
                .disableLoadingIndicator()
                .fetchAsResponse()
                .catch(() => {
                /* Ignore errors. */
            });
        }
        BackgroundQueue.setUrl(options.backgroundQueue.url);
        if (Math.random() < 0.1 || options.backgroundQueue.force) {
            // invoke the queue roughly every 10th request or on demand
            BackgroundQueue.invoke();
        }
        if (COMPILER_TARGET_DEFAULT) {
            UiUserIgnore.init();
        }
        UiMessageUserConsent.init();
        if (options.shareButtonProviders) {
            (0, Providers_1.addShareProviders)(options.shareButtonProviders);
        }
        UiMessageShareDialog.setup();
        if (User_1.default.userId) {
            UiFeedDialog.setup();
        }
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-reaction-summary", () => {
            void new Promise((resolve_2, reject_2) => { require(["./Ui/Reaction/SummaryDetails"], resolve_2, reject_2); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-comment", () => {
            void new Promise((resolve_3, reject_3) => { require(["./Component/Comment/woltlab-core-comment"], resolve_3, reject_3); }).then(tslib_1.__importStar);
        });
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-comment-response", () => {
            void new Promise((resolve_4, reject_4) => { require(["./Component/Comment/Response/woltlab-core-comment-response"], resolve_4, reject_4); }).then(tslib_1.__importStar);
        });
    }
    exports.setup = setup;
});

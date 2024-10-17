/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "./BackgroundQueue", "./Bootstrap", "./Ui/User/Ignore", "./Ui/Page/Header/Menu", "./Ui/Message/UserConsent", "./Ui/Message/Share/Dialog", "./Ui/Message/Share/Providers", "./Ui/Feed/Dialog", "./User", "./Ui/Page/Menu/Main/Frontend", "./LazyLoader", "./Ajax/Backend", "./Notification/ServiceWorker"], function (require, exports, tslib_1, BackgroundQueue, Bootstrap, UiUserIgnore, UiPageHeaderMenu, UiMessageUserConsent, UiMessageShareDialog, Providers_1, UiFeedDialog, User_1, Frontend_1, LazyLoader_1, Backend_1, ServiceWorker_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    BackgroundQueue = tslib_1.__importStar(BackgroundQueue);
    Bootstrap = tslib_1.__importStar(Bootstrap);
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
    function setupUserPopover(endpoint) {
        if (endpoint === "") {
            return;
        }
        (0, LazyLoader_1.whenFirstSeen)(".userLink", () => {
            void new Promise((resolve_1, reject_1) => { require(["./Component/Popover"], resolve_1, reject_1); }).then(tslib_1.__importStar).then(({ setupFor }) => {
                setupFor({
                    endpoint,
                    identifier: "com.woltlab.wcf.user",
                    selector: ".userLink",
                });
            });
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
            void new Promise((resolve_2, reject_2) => { require(["./Controller/Style/Changer"], resolve_2, reject_2); }).then(tslib_1.__importStar).then((ControllerStyleChanger) => {
                ControllerStyleChanger.setup();
            });
        }
        setupUserPopover(options.endpointUserPopover);
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
            if (options.serviceWorker) {
                (0, ServiceWorker_1.setup)(options.serviceWorker.publicKey, options.serviceWorker.serviceWorkerJsUrl, options.serviceWorker.registerUrl);
            }
        }
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-reaction-summary", () => {
            void new Promise((resolve_3, reject_3) => { require(["./Ui/Reaction/SummaryDetails"], resolve_3, reject_3); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-comment", () => {
            void new Promise((resolve_4, reject_4) => { require(["./Component/Comment/woltlab-core-comment"], resolve_4, reject_4); }).then(tslib_1.__importStar);
        });
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-comment-response", () => {
            void new Promise((resolve_5, reject_5) => { require(["./Component/Comment/Response/woltlab-core-comment-response"], resolve_5, reject_5); }).then(tslib_1.__importStar);
        });
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-emoji-picker", () => {
            void new Promise((resolve_6, reject_6) => { require(["./Component/EmojiPicker/woltlab-core-emoji-picker"], resolve_6, reject_6); }).then(tslib_1.__importStar);
        });
        (0, LazyLoader_1.whenFirstSeen)("[data-follow-user]", () => {
            void new Promise((resolve_7, reject_7) => { require(["./Component/User/Follow"], resolve_7, reject_7); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
        (0, LazyLoader_1.whenFirstSeen)("[data-ignore-user]", () => {
            void new Promise((resolve_8, reject_8) => { require(["./Component/User/Ignore"], resolve_8, reject_8); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
    }
});

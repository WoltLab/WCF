/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "./BackgroundQueue", "./Bootstrap", "./Ui/User/Ignore", "./Ui/Page/Header/Menu", "./Ui/Message/UserConsent", "./Ui/Message/Share/Dialog", "./Ui/Message/Share/Providers", "./Ui/Feed/Dialog", "./User", "./Ui/Page/Menu/Main/Frontend", "./LazyLoader", "./Ajax/Backend", "./Notification/ServiceWorker", "./Api/Articles/GetArticlePopover"], function (require, exports, tslib_1, BackgroundQueue, Bootstrap, UiUserIgnore, UiPageHeaderMenu, UiMessageUserConsent, UiMessageShareDialog, Providers_1, UiFeedDialog, User_1, Frontend_1, LazyLoader_1, Backend_1, ServiceWorker_1, GetArticlePopover_1) {
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
    function setupArticlePopover() {
        (0, LazyLoader_1.whenFirstSeen)(".articleLink", () => {
            void new Promise((resolve_2, reject_2) => { require(["WoltLabSuite/Core/Component/Popover"], resolve_2, reject_2); }).then(tslib_1.__importStar).then(({ setupFor }) => {
                setupFor({
                    endpoint: (objectId) => (0, GetArticlePopover_1.getArticlePopover)(objectId).then((response) => response.template),
                    identifier: "com.woltlab.wcf.article",
                    selector: ".articleLink",
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
        if (options.removeQuotes?.length) {
            void new Promise((resolve_3, reject_3) => { require(["./Component/Quote/Storage"], resolve_3, reject_3); }).then(tslib_1.__importStar).then(({ removeQuotes }) => removeQuotes(options.removeQuotes));
        }
        if (options.usedQuotes?.size) {
            void new Promise((resolve_4, reject_4) => { require(["./Component/Quote/Storage"], resolve_4, reject_4); }).then(tslib_1.__importStar).then(({ markQuoteAsUsed }) => {
                options.usedQuotes.forEach((uuids, editorId) => {
                    for (const uuid of uuids) {
                        markQuoteAsUsed(editorId, uuid);
                    }
                });
            });
        }
        UiPageHeaderMenu.init();
        if (options.styleChanger) {
            void new Promise((resolve_5, reject_5) => { require(["./Controller/Style/Changer"], resolve_5, reject_5); }).then(tslib_1.__importStar).then((ControllerStyleChanger) => {
                ControllerStyleChanger.setup();
            });
        }
        setupUserPopover(options.endpointUserPopover);
        setupArticlePopover();
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
                (0, ServiceWorker_1.setup)(options.serviceWorker.publicKey, options.serviceWorker.serviceWorkerJsUrl, options.serviceWorker.registerUrl, options.serviceWorker.notificationLastReadTime);
            }
        }
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-reaction-summary", () => {
            void new Promise((resolve_6, reject_6) => { require(["./Ui/Reaction/SummaryDetails"], resolve_6, reject_6); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-comment", () => {
            void new Promise((resolve_7, reject_7) => { require(["./Component/Comment/woltlab-core-comment"], resolve_7, reject_7); }).then(tslib_1.__importStar);
        });
        (0, LazyLoader_1.whenFirstSeen)("woltlab-core-comment-response", () => {
            void new Promise((resolve_8, reject_8) => { require(["./Component/Comment/Response/woltlab-core-comment-response"], resolve_8, reject_8); }).then(tslib_1.__importStar);
        });
        (0, LazyLoader_1.whenFirstSeen)("[data-follow-user]", () => {
            void new Promise((resolve_9, reject_9) => { require(["./Component/User/Follow"], resolve_9, reject_9); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
        (0, LazyLoader_1.whenFirstSeen)("[data-ignore-user]", () => {
            void new Promise((resolve_10, reject_10) => { require(["./Component/User/Ignore"], resolve_10, reject_10); }).then(tslib_1.__importStar).then(({ setup }) => setup());
        });
        (0, LazyLoader_1.whenFirstSeen)("[data-report-content]", () => {
            void new Promise((resolve_11, reject_11) => { require(["./Ui/Moderation/Report"], resolve_11, reject_11); }).then(tslib_1.__importStar).then(({ setup }) => setup(options.reportEndpoint));
        });
    }
});

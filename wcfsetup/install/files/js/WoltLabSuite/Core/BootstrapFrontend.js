/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/BootstrapFrontend
 */
define(["require", "exports", "tslib", "./BackgroundQueue", "./Bootstrap", "./Controller/Style/Changer", "./Controller/Popover", "./Ui/User/Ignore", "./Ui/Page/Header/Menu", "./Ui/Message/UserConsent", "./Ajax", "./Ui/Message/Share/Dialog", "./Ui/Message/Share/Providers", "./Ui/Feed/Dialog", "./User"], function (require, exports, tslib_1, BackgroundQueue, Bootstrap, ControllerStyleChanger, ControllerPopover, UiUserIgnore, UiPageHeaderMenu, UiMessageUserConsent, Ajax, UiMessageShareDialog, UiMessageShareProviders, UiFeedDialog, User_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    BackgroundQueue = tslib_1.__importStar(BackgroundQueue);
    Bootstrap = tslib_1.__importStar(Bootstrap);
    ControllerStyleChanger = tslib_1.__importStar(ControllerStyleChanger);
    ControllerPopover = tslib_1.__importStar(ControllerPopover);
    UiUserIgnore = tslib_1.__importStar(UiUserIgnore);
    UiPageHeaderMenu = tslib_1.__importStar(UiPageHeaderMenu);
    UiMessageUserConsent = tslib_1.__importStar(UiMessageUserConsent);
    Ajax = tslib_1.__importStar(Ajax);
    UiMessageShareDialog = tslib_1.__importStar(UiMessageShareDialog);
    UiMessageShareProviders = tslib_1.__importStar(UiMessageShareProviders);
    UiFeedDialog = tslib_1.__importStar(UiFeedDialog);
    User_1 = tslib_1.__importDefault(User_1);
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
        Bootstrap.setup({ enableMobileMenu: true });
        UiPageHeaderMenu.init();
        if (options.styleChanger) {
            ControllerStyleChanger.setup();
        }
        if (options.enableUserPopover) {
            _initUserPopover();
        }
        if (options.executeCronjobs) {
            Ajax.apiOnce({
                data: {
                    className: "wcf\\data\\cronjob\\CronjobAction",
                    actionName: "executeCronjobs",
                },
                failure: () => false,
                silent: true,
            });
        }
        BackgroundQueue.setUrl(options.backgroundQueue.url);
        if (Math.random() < 0.1 || options.backgroundQueue.force) {
            // invoke the queue roughly every 10th request or on demand
            BackgroundQueue.invoke();
        }
        if (globalThis.COMPILER_TARGET_DEFAULT) {
            UiUserIgnore.init();
        }
        UiMessageUserConsent.init();
        UiMessageShareProviders.enableShareProviders(options.shareButtonProviders || []);
        UiMessageShareDialog.setup();
        if (User_1.default.userId) {
            UiFeedDialog.setup();
        }
    }
    exports.setup = setup;
});

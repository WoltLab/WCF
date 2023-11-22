/**
 * Prompts the user for their consent before displaying external media.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Helper/Selector", "../../Ajax", "../../Core", "../../Dom/Change/Listener", "../../Dom/Util", "../../User"], function (require, exports, tslib_1, Selector_1, Ajax, Core, Listener_1, Util_1, User_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    User_1 = tslib_1.__importDefault(User_1);
    class UserConsent {
        enableAll = false;
        constructor() {
            if (window.sessionStorage.getItem(`${Core.getStoragePrefix()}user-consent`) === "all") {
                this.enableAll = true;
            }
            this.registerEventListeners();
            Listener_1.default.add("WoltLabSuite/Core/Ui/Message/UserConsent", () => this.registerEventListeners());
        }
        registerEventListeners() {
            if (this.enableAll) {
                this.enableAllExternalMedia();
            }
            else {
                (0, Selector_1.wheneverFirstSeen)(".jsButtonMessageUserConsentEnable", (button) => {
                    button.addEventListener("click", (event) => this.click(event));
                });
            }
        }
        click(event) {
            event.preventDefault();
            this.enableAll = true;
            this.enableAllExternalMedia();
            if (User_1.default.userId) {
                Ajax.apiOnce({
                    data: {
                        actionName: "saveUserConsent",
                        className: "wcf\\data\\user\\UserAction",
                    },
                    silent: true,
                });
            }
            else {
                window.sessionStorage.setItem(`${Core.getStoragePrefix()}user-consent`, "all");
            }
        }
        enableExternalMedia(container) {
            if (container.dataset.target) {
                document.getElementById(container.dataset.target).hidden = false;
            }
            else {
                const payload = atob(container.dataset.payload);
                Util_1.default.insertHtml(payload, container, "before");
            }
            container.remove();
        }
        enableAllExternalMedia() {
            document.querySelectorAll(".messageUserConsent").forEach((el) => this.enableExternalMedia(el));
        }
    }
    let userConsent;
    function init() {
        if (!userConsent) {
            userConsent = new UserConsent();
        }
    }
    exports.init = init;
});

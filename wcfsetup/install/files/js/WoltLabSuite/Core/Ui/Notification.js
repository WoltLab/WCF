/**
 * Simple notification overlay.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module Ui/Notification (alias)
 * @module WoltLabSuite/Core/Ui/Notification
 */
define(["require", "exports", "tslib", "../Language"], function (require, exports, tslib_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.notifyWarning = exports.notifySuccess = exports.notifyInfo = exports.notifyError = exports.show = void 0;
    Language = tslib_1.__importStar(Language);
    let _message;
    let _notificationElement;
    let _pendingNotification = undefined;
    let _timeout;
    function init() {
        if (_notificationElement instanceof HTMLElement) {
            return;
        }
        _notificationElement = document.createElement("div");
        _notificationElement.id = "systemNotification";
        _message = document.createElement("p");
        _message.addEventListener("click", () => hideNotification());
        _notificationElement.appendChild(_message);
        document.body.appendChild(_notificationElement);
    }
    /**
     * Hides the notification and invokes the callback if provided.
     */
    function hideNotification() {
        window.clearTimeout(_timeout);
        _notificationElement.classList.remove("active");
    }
    async function notify(type, message) {
        init();
        if (_notificationElement.classList.contains("active")) {
            await _pendingNotification;
        }
        _message.className = type;
        _message.textContent = Language.get(message);
        _notificationElement.classList.add("active");
        _pendingNotification = new Promise((resolve) => {
            _timeout = window.setTimeout(() => {
                resolve();
            }, 2000);
        }).then(() => hideNotification());
        return _pendingNotification;
    }
    /**
     * @deprecated 5.5 Use `notifyError()`, `notifyInfo()`, `notifySuccess()` or `notifyWarning()` instead
     */
    function show(message = "", callback = null, cssClassName = "") {
        const validTypes = [
            "error" /* Error */,
            "info" /* Info */,
            "success" /* Success */,
            "warning" /* Warning */,
        ];
        if (!validTypes.includes(cssClassName)) {
            cssClassName = "success" /* Success */;
        }
        const notification = notify(cssClassName, message);
        void notification.then(() => {
            if (typeof callback === "function") {
                callback();
            }
        });
    }
    exports.show = show;
    async function notifyError(message = "") {
        return notify("error" /* Error */, message);
    }
    exports.notifyError = notifyError;
    async function notifyInfo(message = "") {
        return notify("info" /* Info */, message);
    }
    exports.notifyInfo = notifyInfo;
    async function notifySuccess(message = "") {
        return notify("success" /* Success */, message);
    }
    exports.notifySuccess = notifySuccess;
    async function notifyWarning(message = "") {
        return notify("warning" /* Warning */, message);
    }
    exports.notifyWarning = notifyWarning;
});

/**
 * Simple notification overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "../Helper/PageOverlay", "../Language"], function (require, exports, PageOverlay_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.show = show;
    let _busy = false;
    let _callback = null;
    let _didInit = false;
    let _message;
    let _notificationElement;
    let _timeout;
    function init() {
        if (_didInit) {
            return;
        }
        _didInit = true;
        _notificationElement = document.createElement("div");
        _notificationElement.id = "systemNotification";
        _message = document.createElement("p");
        _message.addEventListener("click", hide);
        _notificationElement.appendChild(_message);
        (0, PageOverlay_1.getPageOverlayContainer)().appendChild(_notificationElement);
    }
    /**
     * Hides the notification and invokes the callback if provided.
     */
    function hide() {
        clearTimeout(_timeout);
        _notificationElement.classList.remove("active");
        if (_callback !== null) {
            _callback();
        }
        _busy = false;
    }
    /**
     * Displays a notification.
     */
    function show(message, callback, cssClassName) {
        if (_busy) {
            return;
        }
        _busy = true;
        init();
        _callback = typeof callback === "function" ? callback : null;
        _message.className = cssClassName || "success";
        _message.textContent = (0, Language_1.getPhrase)(message || "wcf.global.success");
        _notificationElement.classList.add("active");
        _timeout = window.setTimeout(hide, 2000);
    }
});

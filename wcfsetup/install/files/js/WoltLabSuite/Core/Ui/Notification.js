/**
 * Simple notification overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/Notification (alias)
 * @module  WoltLabSuite/Core/Ui/Notification
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
define(["require", "exports", "../Language"], function (require, exports, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.show = void 0;
    Language = __importStar(Language);
    let _busy = false;
    let _callback = null;
    let _didInit = false;
    let _message;
    let _notificationElement;
    let _timeout;
    function init() {
        if (_didInit)
            return;
        _didInit = true;
        _notificationElement = document.createElement('div');
        _notificationElement.id = 'systemNotification';
        _message = document.createElement('p');
        _message.addEventListener('click', hide);
        _notificationElement.appendChild(_message);
        document.body.appendChild(_notificationElement);
    }
    /**
     * Hides the notification and invokes the callback if provided.
     */
    function hide() {
        clearTimeout(_timeout);
        _notificationElement.classList.remove('active');
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
        _callback = (typeof callback === 'function') ? callback : null;
        _message.className = cssClassName || 'success';
        _message.textContent = Language.get(message || 'wcf.global.success');
        _notificationElement.classList.add('active');
        _timeout = setTimeout(hide, 2000);
    }
    exports.show = show;
});

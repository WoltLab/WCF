/**
 * Provides global helper methods to interact with ignored content.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../../Dom/Change/Listener"], function (require, exports, tslib_1, Listener_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Listener_1 = tslib_1.__importDefault(Listener_1);
    const _availableMessages = document.getElementsByClassName("ignoredUserMessage");
    const _knownMessages = new Set();
    /**
     * Adds ignored messages to the collection.
     *
     * @protected
     */
    function rebuild() {
        for (let i = 0, length = _availableMessages.length; i < length; i++) {
            const message = _availableMessages[i];
            if (!_knownMessages.has(message)) {
                message.addEventListener("click", showMessage, { once: true });
                _knownMessages.add(message);
            }
        }
    }
    /**
     * Reveals a message on click/tap and disables the listener.
     */
    function showMessage(event) {
        event.preventDefault();
        const message = event.currentTarget;
        message.classList.remove("ignoredUserMessage");
        _knownMessages.delete(message);
        // Firefox selects the entire message on click for no reason
        window.getSelection().removeAllRanges();
    }
    /**
     * Initializes the click handler for each ignored message and listens for
     * newly inserted messages.
     */
    function init() {
        rebuild();
        Listener_1.default.add("WoltLabSuite/Core/Ui/User/Ignore", rebuild);
    }
});

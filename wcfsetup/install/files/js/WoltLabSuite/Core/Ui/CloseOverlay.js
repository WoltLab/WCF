/**
 * Allows to be informed when a click event bubbled up to the document's body.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/CloseOverlay (alias)
 * @module  WoltLabSuite/Core/Ui/CloseOverlay
 */
define(["require", "exports", "tslib", "../CallbackList"], function (require, exports, tslib_1, CallbackList_1) {
    "use strict";
    CallbackList_1 = tslib_1.__importDefault(CallbackList_1);
    const _callbackList = new CallbackList_1.default();
    const UiCloseOverlay = {
        /**
         * @see CallbackList.add
         */
        add: _callbackList.add.bind(_callbackList),
        /**
         * @see CallbackList.remove
         */
        remove: _callbackList.remove.bind(_callbackList),
        /**
         * Invokes all registered callbacks.
         */
        execute() {
            _callbackList.forEach(null, (callback) => callback());
        },
    };
    document.body.addEventListener("click", () => UiCloseOverlay.execute());
    return UiCloseOverlay;
});

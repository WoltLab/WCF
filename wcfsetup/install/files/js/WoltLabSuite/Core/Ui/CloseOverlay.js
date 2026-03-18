/**
 * Allows to be informed when a click event bubbled up to the document's body.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../CallbackList"], function (require, exports, tslib_1, CallbackList_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Origin = void 0;
    exports.add = add;
    exports.remove = remove;
    exports.execute = execute;
    CallbackList_1 = tslib_1.__importDefault(CallbackList_1);
    const _callbackList = new CallbackList_1.default();
    var Origin;
    (function (Origin) {
        Origin["Document"] = "document";
        Origin["DropDown"] = "dropdown";
        Origin["Search"] = "search";
    })(Origin || (exports.Origin = Origin = {}));
    let hasGlobalListener = false;
    function add(identifier, callback) {
        _callbackList.add(identifier, callback);
        if (!hasGlobalListener) {
            document.body.addEventListener("click", () => {
                execute(Origin.Document);
            });
            hasGlobalListener = true;
        }
    }
    function remove(identifier) {
        _callbackList.remove(identifier);
    }
    function execute(origin, identifier) {
        _callbackList.forEach(null, (callback) => callback(origin, identifier));
    }
    // This is required for the backwards compatibility with WSC <= 5.4.
    const UiCloseOverlay = {
        add,
        remove,
        execute,
    };
    exports.default = UiCloseOverlay;
});

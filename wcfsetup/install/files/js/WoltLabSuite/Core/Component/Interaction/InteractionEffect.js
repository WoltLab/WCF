/**
 * Represents an effect that is to be applied after an interaction has been executed.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.InteractionEffect = void 0;
    var InteractionEffect;
    (function (InteractionEffect) {
        InteractionEffect["ReloadItem"] = "ReloadItem";
        InteractionEffect["ReloadList"] = "ReloadList";
        InteractionEffect["RemoveItem"] = "RemoveItem";
    })(InteractionEffect || (exports.InteractionEffect = InteractionEffect = {}));
});

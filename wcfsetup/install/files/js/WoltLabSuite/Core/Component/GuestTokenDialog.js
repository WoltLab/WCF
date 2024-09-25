/**
 * Handles the creation of guest tokens.
 *
 * @author    Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.1
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/User"], function (require, exports, tslib_1, Dialog_1, User_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getGuestToken = getGuestToken;
    User_1 = tslib_1.__importDefault(User_1);
    async function getGuestToken() {
        const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(User_1.default.guestTokenDialogEndpoint);
        if (ok) {
            return result.token;
        }
        return undefined;
    }
});

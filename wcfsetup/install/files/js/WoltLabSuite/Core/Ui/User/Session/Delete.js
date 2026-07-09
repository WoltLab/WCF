/**
 * Handles the deletion of a user session.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Confirmation", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Api/Sessions/DeleteSession", "WoltLabSuite/Core/Component/Snackbar"], function (require, exports, tslib_1, UiConfirmation, Language_1, DeleteSession_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    function onClick(button) {
        UiConfirmation.show({
            message: (0, Language_1.getPhrase)("wcf.user.security.deleteSession.confirmMessage"),
            confirm: async (_parameters) => {
                await (0, DeleteSession_1.deleteSession)(button.dataset.sessionId);
                button.closest("li")?.remove();
                (0, Snackbar_1.showDefaultSuccessSnackbar)();
            },
        });
    }
    function setup() {
        document.querySelectorAll(".sessionDeleteButton").forEach((element) => {
            element.addEventListener("click", () => onClick(element));
        });
    }
});

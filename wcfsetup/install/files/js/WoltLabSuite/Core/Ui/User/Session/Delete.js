/**
 * Handles the deletion of a user session.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Session/Delete
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../Notification", "../../Confirmation", "../../../Language", "../../../Core"], function (require, exports, tslib_1, Ajax, UiNotification, UiConfirmation, Language, Core_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiUserSessionDelete = void 0;
    Ajax = (0, tslib_1.__importStar)(Ajax);
    UiNotification = (0, tslib_1.__importStar)(UiNotification);
    UiConfirmation = (0, tslib_1.__importStar)(UiConfirmation);
    Language = (0, tslib_1.__importStar)(Language);
    class UiUserSessionDelete {
        /**
         * Initializes the session delete buttons.
         */
        constructor() {
            this.knownElements = new Map();
            document.querySelectorAll(".sessionDeleteButton").forEach((element) => {
                if (!element.dataset.sessionId) {
                    throw new Error(`No sessionId for session delete button given.`);
                }
                if (!this.knownElements.has(element.dataset.sessionId)) {
                    element.addEventListener("click", (ev) => this.delete(element, ev));
                    this.knownElements.set(element.dataset.sessionId, element);
                }
            });
        }
        /**
         * Opens the user trophy list for a specific user.
         */
        delete(element, event) {
            event.preventDefault();
            UiConfirmation.show({
                message: Language.get("wcf.user.security.deleteSession.confirmMessage"),
                confirm: (_parameters) => {
                    Ajax.api(this, {
                        sessionID: element.dataset.sessionId,
                    });
                },
            });
        }
        _ajaxSuccess(data) {
            const element = this.knownElements.get(data.sessionID);
            if (element !== undefined) {
                const sessionItem = element.closest("li");
                if (sessionItem !== null) {
                    sessionItem.remove();
                }
            }
            UiNotification.show();
        }
        _ajaxSetup() {
            return {
                url: "index.php?delete-session/&t=" + (0, Core_1.getXsrfToken)(),
            };
        }
    }
    exports.UiUserSessionDelete = UiUserSessionDelete;
    exports.default = UiUserSessionDelete;
});

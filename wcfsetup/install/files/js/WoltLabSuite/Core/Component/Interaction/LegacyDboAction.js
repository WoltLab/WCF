/**
 * Handles interactions that call legacy DBO actions.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @deprecated 6.2 DBO actions are considered outdated and should be migrated to RPC endpoints.
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Ui/Notification", "./Confirmation"], function (require, exports, Ajax_1, Notification_1, Confirmation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function handleDboAction(element, objectName, className, actionName, confirmationType, customConfirmationMessage = "") {
        const confirmationResult = await (0, Confirmation_1.handleConfirmation)(objectName, confirmationType, customConfirmationMessage);
        if (!confirmationResult.result) {
            return;
        }
        await (0, Ajax_1.dboAction)(actionName, className)
            .objectIds([parseInt(element.dataset.objectId)])
            .payload(confirmationResult.reason ? { reason: confirmationResult.reason } : {})
            .dispatch();
        if (confirmationType == Confirmation_1.ConfirmationType.Delete) {
            // TODO: This shows a generic success message and should be replaced with a more specific message.
            (0, Notification_1.show)(undefined, () => {
                element.dispatchEvent(new CustomEvent("remove", {
                    bubbles: true,
                }));
            });
        }
        else {
            element.dispatchEvent(new CustomEvent("refresh", {
                bubbles: true,
            }));
            // TODO: This shows a generic success message and should be replaced with a more specific message.
            (0, Notification_1.show)();
        }
    }
    function setup(identifier, container) {
        container.addEventListener("interaction", (event) => {
            if (event.detail.interaction === identifier) {
                void handleDboAction(event.target, event.detail.objectName, event.detail.className, event.detail.actionName, event.detail.confirmationType, event.detail.confirmationMessage);
            }
        });
    }
});

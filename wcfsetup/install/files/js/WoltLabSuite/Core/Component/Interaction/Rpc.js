/**
 * Handles interactions that call a RPC endpoint.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Api/DeleteObject", "WoltLabSuite/Core/Api/PostObject", "WoltLabSuite/Core/Ui/Notification", "./Confirmation"], function (require, exports, DeleteObject_1, PostObject_1, Notification_1, Confirmation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function handleRpcInteraction(element, objectName, endpoint, confirmationType, customConfirmationMessage = "") {
        const confirmationResult = await (0, Confirmation_1.handleConfirmation)(objectName, confirmationType, customConfirmationMessage);
        if (!confirmationResult.result) {
            return;
        }
        if (confirmationType == Confirmation_1.ConfirmationType.Delete) {
            const result = await (0, DeleteObject_1.deleteObject)(endpoint);
            if (!result.ok) {
                return;
            }
        }
        else {
            const result = await (0, PostObject_1.postObject)(endpoint, confirmationResult.reason ? { reason: confirmationResult.reason } : undefined);
            if (!result.ok) {
                return;
            }
        }
        if (confirmationType === Confirmation_1.ConfirmationType.Delete) {
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
                void handleRpcInteraction(event.target, event.detail.objectName, event.detail.endpoint, event.detail.confirmationType, event.detail.confirmationMessage);
            }
        });
    }
});

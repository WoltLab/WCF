/**
 * Handles bulk interactions that call a RPC endpoint.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Api/DeleteObject", "WoltLabSuite/Core/Api/PostObject", "WoltLabSuite/Core/Ui/Notification", "../Confirmation"], function (require, exports, DeleteObject_1, PostObject_1, Notification_1, Confirmation_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function handleRpcInteraction(container, objectIds, endpoint, confirmationType, customConfirmationMessage = "") {
        const confirmationResult = await (0, Confirmation_1.handleConfirmation)("", confirmationType, customConfirmationMessage);
        if (!confirmationResult.result) {
            return;
        }
        if (confirmationType == Confirmation_1.ConfirmationType.Delete) {
            for (let i = 0; i < objectIds.length; i++) {
                const result = await (0, DeleteObject_1.deleteObject)(endpoint.replace(/%s/, objectIds[i].toString()));
                if (!result.ok) {
                    return;
                }
            }
        }
        else {
            for (let i = 0; i < objectIds.length; i++) {
                const result = await (0, PostObject_1.postObject)(endpoint.replace(/%s/, objectIds[i].toString()), confirmationResult.reason ? { reason: confirmationResult.reason } : undefined);
                if (!result.ok) {
                    return;
                }
            }
        }
        if (confirmationType === Confirmation_1.ConfirmationType.Delete) {
            // TODO: This shows a generic success message and should be replaced with a more specific message.
            (0, Notification_1.show)(undefined, () => {
                for (let i = 0; i < objectIds.length; i++) {
                    const element = container.querySelector(`[data-object-id="${objectIds[i]}"]`);
                    if (!element) {
                        continue;
                    }
                    element.dispatchEvent(new CustomEvent("remove", {
                        bubbles: true,
                    }));
                }
            });
        }
        else {
            for (let i = 0; i < objectIds.length; i++) {
                const element = container.querySelector(`[data-object-id="${objectIds[i]}"]`);
                if (!element) {
                    continue;
                }
                element.dispatchEvent(new CustomEvent("refresh", {
                    bubbles: true,
                }));
            }
            // TODO: This shows a generic success message and should be replaced with a more specific message.
            (0, Notification_1.show)();
        }
        container.dispatchEvent(new CustomEvent("reset-selection"));
    }
    function setup(identifier, container) {
        container.addEventListener("bulk-interaction", (event) => {
            if (event.detail.bulkInteraction === identifier) {
                void handleRpcInteraction(container, JSON.parse(event.detail.objectIds), event.detail.endpoint, event.detail.confirmationType, event.detail.confirmationMessage);
            }
        });
    }
});

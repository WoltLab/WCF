/**
 * Handles interactions that call legacy DBO actions.
 *
 * @author Marcel Werk
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @deprecated 6.2 DBO actions are considered outdated and should be migrated to RPC endpoints.
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax", "./Confirmation", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Language", "./InteractionEffect"], function (require, exports, Ajax_1, Confirmation_1, Snackbar_1, Language_1, InteractionEffect_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function handleDboAction(container, element, objectName, className, actionName, confirmationType, customConfirmationMessage = "", interactionEffect = InteractionEffect_1.InteractionEffect.ReloadItem, detail) {
        const confirmationResult = await (0, Confirmation_1.handleConfirmation)(objectName, confirmationType, customConfirmationMessage);
        if (!confirmationResult.result) {
            return;
        }
        await (0, Ajax_1.dboAction)(actionName, className)
            .objectIds([parseInt(element.dataset.objectId)])
            .payload(confirmationResult.reason ? { reason: confirmationResult.reason } : {})
            .dispatch();
        if (interactionEffect === InteractionEffect_1.InteractionEffect.ReloadItem || interactionEffect === InteractionEffect_1.InteractionEffect.ReloadPage) {
            element.dispatchEvent(new CustomEvent("interaction:invalidate", {
                bubbles: true,
                detail: {
                    ...detail,
                    _reloadPage: String(interactionEffect === InteractionEffect_1.InteractionEffect.ReloadPage),
                },
            }));
        }
        else if (interactionEffect === InteractionEffect_1.InteractionEffect.ReloadList) {
            container.dispatchEvent(new CustomEvent("interaction:invalidate-all", {
                detail,
            }));
        }
        else {
            element.dispatchEvent(new CustomEvent("interaction:remove", {
                bubbles: true,
                detail,
            }));
        }
        if (confirmationType == Confirmation_1.ConfirmationType.Delete) {
            (0, Snackbar_1.showSuccessSnackbar)((0, Language_1.getPhrase)("wcf.global.success.delete"));
        }
        else {
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
        }
    }
    function setup(identifier, container) {
        container.addEventListener("interaction:execute", (event) => {
            if (event.detail.interaction === identifier) {
                void handleDboAction(container, event.target, event.detail.objectName, event.detail.className, event.detail.actionName, event.detail.confirmationType, event.detail.confirmationMessage, event.detail.interactionEffect, event.detail);
            }
        });
    }
});

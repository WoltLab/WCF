/**
 * Handles bulk interactions that open a form builder dialog.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Component/Snackbar", "../InteractionEffect"], function (require, exports, Dialog_1, Snackbar_1, InteractionEffect_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function handleFormBuilderDialogAction(container, objectIds, endpoint, interactionEffect = InteractionEffect_1.InteractionEffect.ReloadItem) {
        const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint);
        if (!ok) {
            return;
        }
        container.dispatchEvent(new CustomEvent("interaction:reset-selection"));
        if (interactionEffect === InteractionEffect_1.InteractionEffect.Redirect) {
            const { redirectUrl } = result;
            if (redirectUrl) {
                (0, Snackbar_1.showDefaultSuccessSnackbar)().addEventListener("snackbar:close", () => {
                    window.location.href = redirectUrl;
                });
                return;
            }
            // The endpoint did not provide a target, reload the page instead.
            interactionEffect = InteractionEffect_1.InteractionEffect.ReloadPage;
        }
        if (interactionEffect === InteractionEffect_1.InteractionEffect.ReloadPage) {
            (0, Snackbar_1.showDefaultSuccessSnackbar)().addEventListener("snackbar:close", () => {
                window.location.reload();
            });
            return;
        }
        if (interactionEffect === InteractionEffect_1.InteractionEffect.ReloadList) {
            container.dispatchEvent(new CustomEvent("interaction:invalidate-all"));
        }
        else {
            const eventName = interactionEffect === InteractionEffect_1.InteractionEffect.RemoveItem ? "interaction:remove" : "interaction:invalidate";
            for (let i = 0; i < objectIds.length; i++) {
                const element = container.querySelector(`[data-object-id="${objectIds[i]}"]`);
                if (!element) {
                    continue;
                }
                element.dispatchEvent(new CustomEvent(eventName, {
                    bubbles: true,
                }));
            }
        }
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup(identifier, container) {
        container.addEventListener("bulk-interaction", (event) => {
            if (event.detail.bulkInteraction === identifier) {
                void handleFormBuilderDialogAction(container, JSON.parse(event.detail.objectIds), event.detail.endpoint, event.detail.interactionEffect);
            }
        });
    }
});

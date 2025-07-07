/**
 * Handles interactions that open a form builder dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Component/Snackbar", "./InteractionEffect"], function (require, exports, Dialog_1, Snackbar_1, InteractionEffect_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function handleFormBuilderDialogAction(container, element, endpoint, interactionEffect = InteractionEffect_1.InteractionEffect.ReloadItem, detail) {
        const { ok } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint);
        if (!ok) {
            return;
        }
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
                detail: {
                    ...detail,
                },
            }));
        }
        else {
            element.dispatchEvent(new CustomEvent("interaction:remove", {
                bubbles: true,
                detail: {
                    ...detail,
                },
            }));
        }
        (0, Snackbar_1.showDefaultSuccessSnackbar)();
    }
    function setup(identifier, container) {
        container.addEventListener("interaction:execute", (event) => {
            if (event.detail.interaction === identifier) {
                void handleFormBuilderDialogAction(container, event.target, event.detail.endpoint, event.detail.interactionEffect, event.detail);
            }
        });
    }
});

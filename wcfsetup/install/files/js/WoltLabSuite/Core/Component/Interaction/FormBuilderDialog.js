/**
 * Handles interactions that open a form builder dialog.
 *
 * @author Marcel Werk
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
define(["require", "exports", "WoltLabSuite/Core/Ui/Notification", "WoltLabSuite/Core/Component/Dialog"], function (require, exports, Notification_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    async function handleFormBuilderDialogAction(element, endpoint) {
        const { ok } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint);
        if (!ok) {
            return;
        }
        element.dispatchEvent(new CustomEvent("refresh", {
            bubbles: true,
        }));
        // TODO: This shows a generic success message and should be replaced with a more specific message.
        (0, Notification_1.show)();
    }
    function setup(identifier, container) {
        container.addEventListener("interaction", (event) => {
            if (event.detail.interaction === identifier) {
                void handleFormBuilderDialogAction(event.target, event.detail.endpoint);
            }
        });
    }
});

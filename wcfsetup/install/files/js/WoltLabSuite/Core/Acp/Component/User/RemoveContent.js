/**
 * Selects the content of one or more users in a dialog and removes it with
 * the `UserContentRemoveWorker`.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Acp/Ui/Worker", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Component/Snackbar", "WoltLabSuite/Core/Language"], function (require, exports, tslib_1, Worker_1, Dialog_1, Snackbar_1, Language_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    exports.setupBulk = setupBulk;
    Worker_1 = tslib_1.__importDefault(Worker_1);
    async function removeContent(endpoint) {
        const { ok, result } = await (0, Dialog_1.dialogFactory)().usingFormBuilder().fromEndpoint(endpoint);
        if (!ok) {
            return false;
        }
        return new Promise((resolve) => {
            new Worker_1.default({
                dialogId: "removeContentWorker",
                dialogTitle: (0, Language_1.getPhrase)("wcf.acp.content.removeContent"),
                className: "wcf\\system\\worker\\UserContentRemoveWorker",
                parameters: {
                    userIDs: result.userIDs,
                    contentProvider: result.contentProviders,
                },
                callbackAbort: () => resolve(false),
                callbackSuccess: () => resolve(true),
            });
        });
    }
    function setup(identifier, container) {
        container.addEventListener("interaction:execute", (event) => {
            if (event.detail.interaction !== identifier) {
                return;
            }
            const element = event.target;
            void removeContent(event.detail.endpoint).then((ok) => {
                if (!ok) {
                    return;
                }
                // The activity points and the number of likes received may have changed.
                element.dispatchEvent(new CustomEvent("interaction:invalidate", {
                    bubbles: true,
                    detail: event.detail,
                }));
                (0, Snackbar_1.showDefaultSuccessSnackbar)();
            });
        });
    }
    function setupBulk(identifier, container) {
        container.addEventListener("bulk-interaction", (event) => {
            if (event.detail.bulkInteraction !== identifier) {
                return;
            }
            const objectIds = JSON.parse(event.detail.objectIds);
            void removeContent(event.detail.endpoint).then((ok) => {
                if (!ok) {
                    return;
                }
                for (const objectId of objectIds) {
                    container.querySelector(`[data-object-id="${objectId}"]`)?.dispatchEvent(new CustomEvent("interaction:invalidate", {
                        bubbles: true,
                    }));
                }
                (0, Snackbar_1.showDefaultSuccessSnackbar)();
                container.dispatchEvent(new CustomEvent("interaction:reset-selection"));
            });
        });
    }
});

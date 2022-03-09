/**
 * Handles the user content remove clipboard action.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2020 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Content/Remove/Clipboard
 * @since       5.4
 */
define(["require", "exports", "tslib", "../../../Worker", "../../../../../Ajax", "../../../../../Language", "../../../../../Ui/Dialog", "../../../../../Event/Handler"], function (require, exports, tslib_1, Worker_1, Ajax, Language, Dialog_1, EventHandler) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.AcpUserContentRemoveClipboard = void 0;
    Worker_1 = tslib_1.__importDefault(Worker_1);
    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    EventHandler = tslib_1.__importStar(EventHandler);
    class AcpUserContentRemoveClipboard {
        /**
         * Initializes the content remove handler.
         */
        constructor() {
            this.dialogId = "userContentRemoveClipboardPrepareDialog";
            EventHandler.add("com.woltlab.wcf.clipboard", "com.woltlab.wcf.user", (data) => {
                if (data.data.actionName === "com.woltlab.wcf.user.deleteUserContent") {
                    this.userIds = data.data.parameters.objectIDs;
                    Ajax.api(this);
                }
            });
        }
        /**
         * Executes the remove content worker.
         */
        executeWorker(objectTypes) {
            new Worker_1.default({
                // dialog
                dialogId: "removeContentWorker",
                dialogTitle: Language.get("wcf.acp.content.removeContent"),
                // ajax
                className: "wcf\\system\\worker\\UserContentRemoveWorker",
                parameters: {
                    userIDs: this.userIds,
                    contentProvider: objectTypes,
                },
            });
        }
        /**
         * Handles a click on the submit button in the overlay.
         */
        submit() {
            const objectTypes = Array.from(this.dialogContent.querySelectorAll("input.contentProviderObjectType"))
                .filter((element) => element.checked)
                .map((element) => element.name);
            Dialog_1.default.close(this.dialogId);
            if (objectTypes.length > 0) {
                window.setTimeout(() => {
                    this.executeWorker(objectTypes);
                }, 200);
            }
        }
        get dialogContent() {
            return Dialog_1.default.getDialog(this.dialogId).content;
        }
        _ajaxSuccess(data) {
            Dialog_1.default.open(this, data.returnValues.template);
            const submitButton = this.dialogContent.querySelector('input[type="submit"]');
            submitButton.addEventListener("click", () => this.submit());
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "prepareRemoveContent",
                    className: "wcf\\data\\user\\UserAction",
                    parameters: {
                        userIDs: this.userIds,
                    },
                },
            };
        }
        _dialogSetup() {
            return {
                id: this.dialogId,
                options: {
                    title: Language.get("wcf.acp.content.removeContent"),
                },
                source: null,
            };
        }
    }
    exports.AcpUserContentRemoveClipboard = AcpUserContentRemoveClipboard;
    exports.default = AcpUserContentRemoveClipboard;
});

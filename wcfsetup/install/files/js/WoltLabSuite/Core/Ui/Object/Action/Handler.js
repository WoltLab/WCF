/**
 * Default handler to react to a specific object action.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Object/Action/Handler
 */
define(["require", "exports", "tslib", "../../../Event/Handler", "../../../Controller/Clipboard"], function (require, exports, tslib_1, EventHandler, ControllerClipboard) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    EventHandler = tslib_1.__importStar(EventHandler);
    ControllerClipboard = tslib_1.__importStar(ControllerClipboard);
    class UiObjectActionHandler {
        constructor(actionName, clipboardActionNames, objectAction) {
            this.objectAction = objectAction;
            EventHandler.add("WoltLabSuite/Core/Ui/Object/Action", actionName, (data) => this.handleObjectAction(data));
            document.querySelectorAll(".jsClipboardContainer[data-type]").forEach((container) => {
                EventHandler.add("com.woltlab.wcf.clipboard", container.dataset.type, (data) => {
                    // Only consider events if the action has actually been executed.
                    if (data.responseData === null) {
                        return;
                    }
                    if (clipboardActionNames.indexOf(data.responseData.actionName) !== -1) {
                        this.handleClipboardAction(data);
                    }
                });
            });
        }
        handleClipboardAction(data) {
            const clipboardObjectType = data.listItem.dataset.type;
            document
                .querySelectorAll(`.jsClipboardContainer[data-type="${clipboardObjectType}"] .jsClipboardObject`)
                .forEach((clipboardObject) => {
                const objectId = clipboardObject.dataset.objectId;
                data.responseData.objectIDs.forEach((deletedObjectId) => {
                    if (~~deletedObjectId === ~~objectId) {
                        this.objectAction(data.responseData, clipboardObject);
                    }
                });
            });
        }
        handleObjectAction(data) {
            this.objectAction(data.data, data.objectElement);
            ControllerClipboard.reload();
        }
    }
    exports.default = UiObjectActionHandler;
});

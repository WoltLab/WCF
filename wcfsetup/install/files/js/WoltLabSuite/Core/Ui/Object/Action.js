/**
 * Handles actions that can be executed on (database) objects by clicking on specific action buttons.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Object/Action
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Event/Handler", "../Confirmation", "../../Language", "../../StringUtil"], function (require, exports, tslib_1, Ajax, EventHandler, UiConfirmation, Language, StringUtil) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    EventHandler = tslib_1.__importStar(EventHandler);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    Language = tslib_1.__importStar(Language);
    StringUtil = tslib_1.__importStar(StringUtil);
    const containerSelector = ".jsObjectActionContainer[data-object-action-class-name]";
    const objectSelector = ".jsObjectActionObject[data-object-id]";
    const actionSelector = ".jsObjectAction[data-object-action]";
    function executeAction(event) {
        const actionElement = event.currentTarget;
        const objectAction = actionElement.dataset.objectAction;
        // To support additional actions added by plugins, action elements can override the default object
        // action class name and object id.
        let objectActionClassName = actionElement.closest(containerSelector).dataset.objectActionClassName;
        if (actionElement.dataset.objectActionClassName) {
            objectActionClassName = actionElement.dataset.objectActionClassName;
        }
        let objectId = actionElement.closest(objectSelector).dataset.objectId;
        if (actionElement.dataset.objectId) {
            objectId = actionElement.dataset.objectId;
        }
        // Collect additional request parameters.
        // TODO: Is still untested.
        const parameters = {};
        Object.entries(actionElement.dataset).forEach(([key, value]) => {
            if (/^objectActionParameterData.+/.exec(key)) {
                if (!("data" in parameters)) {
                    parameters["data"] = {};
                }
                parameters[StringUtil.lcfirst(key.replace(/^objectActionParameterData/, ""))] = value;
            }
            else if (/^objectActionParameter.+/.exec(key)) {
                parameters[StringUtil.lcfirst(key.replace(/^objectActionParameter/, ""))] = value;
            }
        });
        function sendRequest() {
            Ajax.apiOnce({
                data: {
                    actionName: objectAction,
                    className: objectActionClassName,
                    objectIDs: [objectId],
                    parameters: parameters,
                },
                success: (data) => processAction(actionElement, data),
            });
        }
        if (actionElement.dataset.confirmMessage) {
            UiConfirmation.show({
                confirm: sendRequest,
                message: Language.get(actionElement.dataset.confirmMessage),
                messageIsHtml: true,
            });
        }
        else {
            sendRequest();
        }
    }
    function processAction(actionElement, data) {
        EventHandler.fire("WoltLabSuite/Core/Ui/Object/Action", actionElement.dataset.objectAction, {
            data,
            objectElement: actionElement.closest(objectSelector),
        });
    }
    function setup() {
        document
            .querySelectorAll(`${containerSelector} ${objectSelector} ${actionSelector}`)
            .forEach((action) => {
            action.addEventListener("click", (ev) => executeAction(ev));
        });
        // TODO: handle elements added later on
    }
    exports.setup = setup;
});

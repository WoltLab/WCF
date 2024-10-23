/**
 * Handles actions that can be executed on (database) objects by clicking on specific action buttons.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Event/Handler", "../Confirmation", "../../Language", "../../StringUtil", "../../Dom/Change/Listener"], function (require, exports, tslib_1, Ajax, EventHandler, UiConfirmation, Language, StringUtil, Listener_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    Ajax = tslib_1.__importStar(Ajax);
    EventHandler = tslib_1.__importStar(EventHandler);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    Language = tslib_1.__importStar(Language);
    StringUtil = tslib_1.__importStar(StringUtil);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    const containerSelector = ".jsObjectActionContainer[data-object-action-class-name]";
    const objectSelector = ".jsObjectActionObject[data-object-id]";
    const actionSelector = ".jsObjectAction[data-object-action]";
    function executeAction(event) {
        event.preventDefault();
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
        const parameters = {};
        Object.entries(actionElement.dataset).forEach(([key, value]) => {
            let matches = /^objectActionParameterData(.+)/.exec(key);
            if (matches) {
                if (!Object.prototype.hasOwnProperty.call(parameters, "data")) {
                    parameters["data"] = {};
                }
                parameters["data"][StringUtil.lcfirst(matches[1])] = value;
            }
            else {
                matches = /^objectActionParameter(.+)/.exec(key);
                if (matches) {
                    const key = StringUtil.lcfirst(matches[1]);
                    if (key === "data") {
                        throw new Error("Additional object action parameters may not use 'data' as key.");
                    }
                    parameters[key] = value;
                }
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
        if (actionElement.dataset.objectActionSuccess === "reload") {
            window.location.reload();
        }
        else {
            EventHandler.fire("WoltLabSuite/Core/Ui/Object/Action", actionElement.dataset.objectAction, {
                containerElement: actionElement.closest(containerSelector),
                data,
                objectElement: actionElement.closest(objectSelector),
            });
        }
    }
    const actions = new Set();
    function registerElements() {
        document
            .querySelectorAll(`${containerSelector} ${objectSelector} ${actionSelector}`)
            .forEach((action) => {
            if (!actions.has(action)) {
                action.addEventListener("click", (ev) => executeAction(ev));
                actions.add(action);
            }
        });
    }
    function setup() {
        registerElements();
        Listener_1.default.add("WoltLabSuite/Core/Ui/Action", () => registerElements());
    }
});

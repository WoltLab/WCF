/**
 * Handles actions that can be executed on (database) objects by clicking on specific action buttons.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Object/Action
 */

import * as Ajax from "../../Ajax";
import * as EventHandler from "../../Event/Handler";
import { DatabaseObjectActionResponse, ResponseData } from "../../Ajax/Data";
import { ObjectActionData } from "./Data";
import * as UiConfirmation from "../Confirmation";
import * as Language from "../../Language";
import * as StringUtil from "../../StringUtil";

const containerSelector = ".jsObjectActionContainer[data-object-action-class-name]";
const objectSelector = ".jsObjectActionObject[data-object-id]";
const actionSelector = ".jsObjectAction[data-object-action]";

function executeAction(event: Event): void {
  const actionElement = event.currentTarget as HTMLElement;
  const objectAction = actionElement.dataset.objectAction!;

  // To support additional actions added by plugins, action elements can override the default object
  // action class name and object id.
  let objectActionClassName = (actionElement.closest(containerSelector) as HTMLElement).dataset.objectActionClassName;
  if (actionElement.dataset.objectActionClassName) {
    objectActionClassName = actionElement.dataset.objectActionClassName;
  }

  let objectId = (actionElement.closest(objectSelector) as HTMLElement).dataset.objectId;
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
    } else if (/^objectActionParameter.+/.exec(key)) {
      parameters[StringUtil.lcfirst(key.replace(/^objectActionParameter/, ""))] = value;
    }
  });

  function sendRequest(): void {
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
  } else {
    sendRequest();
  }
}

function processAction(actionElement: HTMLElement, data: ResponseData | DatabaseObjectActionResponse): void {
  EventHandler.fire("WoltLabSuite/Core/Ui/Object/Action", actionElement.dataset.objectAction!, {
    data,
    objectElement: actionElement.closest(objectSelector),
  } as ObjectActionData);
}

export function setup(): void {
  document
    .querySelectorAll(`${containerSelector} ${objectSelector} ${actionSelector}`)
    .forEach((action: HTMLElement) => {
      action.addEventListener("click", (ev) => executeAction(ev));
    });

  // TODO: handle elements added later on
}

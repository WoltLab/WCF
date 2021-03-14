/**
 * Default handler to react to a specific object action.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Object/Action/Handler
 */

import * as EventHandler from "../../../Event/Handler";
import { ClipboardData, ObjectActionData } from "../Data";
import * as ControllerClipboard from "../../../Controller/Clipboard";
import { DatabaseObjectActionResponse } from "../../../Ajax/Data";

export type ObjectAction = (data: DatabaseObjectActionResponse, objectElement: HTMLElement) => void;

export default class UiObjectActionHandler {
  protected readonly objectAction: ObjectAction;

  constructor(actionName: string, clipboardActionNames: string[], objectAction: ObjectAction) {
    this.objectAction = objectAction;

    EventHandler.add("WoltLabSuite/Core/Ui/Object/Action", actionName, (data: ObjectActionData) =>
      this.handleObjectAction(data),
    );

    document.querySelectorAll(".jsClipboardContainer[data-type]").forEach((container: HTMLElement) => {
      EventHandler.add("com.woltlab.wcf.clipboard", container.dataset.type!, (data: ClipboardData) => {
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

  protected handleClipboardAction(data: ClipboardData): void {
    const clipboardObjectType = data.listItem.dataset.type!;

    document
      .querySelectorAll(`.jsClipboardContainer[data-type="${clipboardObjectType}"] .jsClipboardObject`)
      .forEach((clipboardObject: HTMLElement) => {
        const objectId = clipboardObject.dataset.objectId!;

        data.responseData.objectIDs.forEach((deletedObjectId) => {
          if (~~deletedObjectId === ~~objectId) {
            this.objectAction(data.responseData, clipboardObject);
          }
        });
      });
  }

  protected handleObjectAction(data: ObjectActionData): void {
    this.objectAction(data.data, data.objectElement);

    ControllerClipboard.reload();
  }
}

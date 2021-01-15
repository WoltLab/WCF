/**
 * Generic interface for drag and Drop file uploads.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/DragAndDrop
 */

import * as Core from "../Core";
import * as EventHandler from "../Event/Handler";
import { init, OnDropPayload, OnGlobalDropPayload, RedactorEditorLike } from "./Redactor/DragAndDrop";

interface DragAndDropOptions {
  element: HTMLElement;
  elementId: string;
  onDrop: (data: OnDropPayload) => void;
  onGlobalDrop: (data: OnGlobalDropPayload) => void;
}

export function register(options: DragAndDropOptions): void {
  const uuid = Core.getUuid();
  options = Core.extend({
    element: null,
    elementId: "",
    onDrop: function (_data: OnDropPayload) {
      /* data: { file: File } */
    },
    onGlobalDrop: function (_data: OnGlobalDropPayload) {
      /* data: { cancelDrop: boolean, event: DragEvent } */
    },
  }) as DragAndDropOptions;

  EventHandler.add("com.woltlab.wcf.redactor2", `dragAndDrop_${options.elementId}`, options.onDrop);
  EventHandler.add("com.woltlab.wcf.redactor2", `dragAndDrop_globalDrop_${options.elementId}`, options.onGlobalDrop);

  init({
    uuid: uuid,
    $editor: [options.element],
    $element: [{ id: options.elementId }],
  } as RedactorEditorLike);
}

/**
 * Provides the interface logic to add and edit boxes.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Box/Controller/Handler
 */

import * as Ajax from "../../../../Ajax";
import DomUtil from "../../../../Dom/Util";
import * as EventHandler from "../../../../Event/Handler";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../../../Ajax/Data";

interface AjaxResponse {
  returnValues: {
    template: string;
  };
}

class AcpUiBoxControllerHandler implements AjaxCallbackObject {
  private readonly boxConditions: HTMLElement;
  private readonly boxController: HTMLInputElement;
  private readonly boxControllerContainer: HTMLElement;

  constructor(initialObjectTypeId: number | undefined) {
    this.boxControllerContainer = document.getElementById("boxControllerContainer")!;
    this.boxController = document.getElementById("boxControllerID") as HTMLInputElement;
    this.boxConditions = document.getElementById("boxConditions")!;

    this.boxController.addEventListener("change", () => this.updateConditions());

    DomUtil.show(this.boxControllerContainer);

    if (initialObjectTypeId === undefined) {
      this.updateConditions();
    }
  }

  /**
   * Sets up ajax request object.
   */
  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "getBoxConditionsTemplate",
        className: "wcf\\data\\box\\BoxAction",
      },
    };
  }

  /**
   * Handles successful AJAX requests.
   */
  _ajaxSuccess(data: AjaxResponse): void {
    DomUtil.setInnerHtml(this.boxConditions, data.returnValues.template);
  }

  /**
   * Updates the displayed box conditions based on the selected dynamic box controller.
   */
  private updateConditions(): void {
    EventHandler.fire("com.woltlab.wcf.boxControllerHandler", "updateConditions");

    Ajax.api(this, {
      parameters: {
        objectTypeID: ~~this.boxController.value,
      },
    });
  }
}

let acpUiBoxControllerHandler: AcpUiBoxControllerHandler;

export function init(initialObjectTypeId: number | undefined): void {
  if (!acpUiBoxControllerHandler) {
    acpUiBoxControllerHandler = new AcpUiBoxControllerHandler(initialObjectTypeId);
  }
}

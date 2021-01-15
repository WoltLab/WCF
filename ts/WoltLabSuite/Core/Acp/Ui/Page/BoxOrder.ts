/**
 * Provides helper functions to sort boxes per page.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Acp/Ui/Page/BoxOrder
 */

import * as Ajax from "../../../Ajax";
import DomChangeListener from "../../../Dom/Change/Listener";
import * as Language from "../../../Language";
import * as UiConfirmation from "../../../Ui/Confirmation";
import * as UiNotification from "../../../Ui/Notification";
import { AjaxCallbackSetup } from "../../../Ajax/Data";

interface AjaxResponse {
  actionName: string;
}

interface BoxData {
  boxId: number;
  isDisabled: boolean;
  name: string;
}

class AcpUiPageBoxOrder {
  private readonly pageId: number;
  private readonly pbo: HTMLElement;

  /**
   * Initializes the sorting capabilities.
   */
  constructor(pageId: number, boxes: Map<string, BoxData[]>) {
    this.pageId = pageId;
    this.pbo = document.getElementById("pbo")!;

    boxes.forEach((boxData, position) => {
      const container = document.createElement("ul");
      boxData.forEach((box) => {
        const item = document.createElement("li");
        item.dataset.boxId = box.boxId.toString();

        let icon = "";
        if (box.isDisabled) {
          icon = ` <span class="icon icon16 fa-exclamation-triangle red jsTooltip" title="${Language.get(
            "wcf.acp.box.isDisabled",
          )}"></span>`;
        }

        item.innerHTML = box.name + icon;

        container.appendChild(item);
      });

      if (boxData.length > 1) {
        window.jQuery(container).sortable({
          opacity: 0.6,
          placeholder: "sortablePlaceholder",
        });
      }

      const wrapper = this.pbo.querySelector(`[data-placeholder="${position}"]`) as HTMLElement;
      wrapper.appendChild(container);
    });

    const submitButton = document.querySelector('button[data-type="submit"]') as HTMLButtonElement;
    submitButton.addEventListener("click", (ev) => this.save(ev));

    const buttonDiscard = document.querySelector(".jsButtonCustomShowOrder") as HTMLAnchorElement;
    if (buttonDiscard) buttonDiscard.addEventListener("click", (ev) => this.discard(ev));

    DomChangeListener.trigger();
  }

  /**
   * Saves the order of all boxes per position.
   */
  private save(event: MouseEvent): void {
    event.preventDefault();

    const data = {};

    // collect data
    this.pbo.querySelectorAll("[data-placeholder]").forEach((position: HTMLElement) => {
      const boxIds = Array.from(position.querySelectorAll("li"))
        .map((element) => ~~element.dataset.boxId!)
        .filter((id) => id > 0);

      const placeholder = position.dataset.placeholder!;
      data[placeholder] = boxIds;
    });

    Ajax.api(this, {
      parameters: {
        position: data,
      },
    });
  }

  /**
   * Shows an dialog to discard the individual box show order for this page.
   */
  private discard(event: MouseEvent): void {
    event.preventDefault();

    UiConfirmation.show({
      confirm: () => {
        Ajax.api(this, {
          actionName: "resetPosition",
        });
      },
      message: Language.get("wcf.acp.page.boxOrder.discard.confirmMessage"),
    });
  }

  _ajaxSuccess(data: AjaxResponse): void {
    switch (data.actionName) {
      case "updatePosition":
        UiNotification.show();
        break;

      case "resetPosition":
        UiNotification.show(undefined, () => {
          window.location.reload();
        });
        break;
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "updatePosition",
        className: "wcf\\data\\page\\PageAction",
        interfaceName: "wcf\\data\\ISortableAction",
        objectIDs: [this.pageId],
      },
    };
  }
}

let acpUiPageBoxOrder: AcpUiPageBoxOrder;

/**
 * Initializes the sorting capabilities.
 */
export function init(pageId: number, boxes: Map<string, BoxData[]>): void {
  if (!acpUiPageBoxOrder) {
    acpUiPageBoxOrder = new AcpUiPageBoxOrder(pageId, boxes);
  }
}

/**
 * Dialog based style changer.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Controller/Style/Changer
 */

import * as Ajax from "../../Ajax";
import * as Language from "../../Language";
import UiDialog from "../../Ui/Dialog";
import { DialogCallbackSetup } from "../../Ui/Dialog/Data";

class ControllerStyleChanger {
  /**
   * Adds the style changer to the bottom navigation.
   */
  constructor() {
    document.querySelectorAll(".jsButtonStyleChanger").forEach((link: HTMLAnchorElement) => {
      link.addEventListener("click", (ev) => this.showDialog(ev));
    });
  }

  /**
   * Loads and displays the style change dialog.
   */
  showDialog(event: MouseEvent): void {
    event.preventDefault();

    UiDialog.open(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "styleChanger",
      options: {
        disableContentPadding: true,
        title: Language.get("wcf.style.changeStyle"),
      },
      source: {
        data: {
          actionName: "getStyleChooser",
          className: "wcf\\data\\style\\StyleAction",
        },
        after: (content) => {
          content.querySelectorAll(".styleList > li").forEach((style: HTMLLIElement) => {
            style.classList.add("pointer");
            style.addEventListener("click", (ev) => this.click(ev));
          });
        },
      },
    };
  }

  /**
   * Changes the style and reloads current page.
   */
  private click(event: MouseEvent): void {
    event.preventDefault();

    const listElement = event.currentTarget as HTMLLIElement;

    Ajax.apiOnce({
      data: {
        actionName: "changeStyle",
        className: "wcf\\data\\style\\StyleAction",
        objectIDs: [listElement.dataset.styleId],
      },
      success: function () {
        window.location.reload();
      },
    });
  }
}

let controllerStyleChanger: ControllerStyleChanger;

/**
 * Adds the style changer to the bottom navigation.
 */
export function setup(): void {
  if (!controllerStyleChanger) {
    new ControllerStyleChanger();
  }
}

/**
 * Loads and displays the style change dialog.
 */
export function showDialog(event: MouseEvent): void {
  controllerStyleChanger.showDialog(event);
}

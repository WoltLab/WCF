/**
 * Dialog based style changer.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

import { getPhrase } from "../../Language";
import { changeStyle } from "WoltLabSuite/Core/Api/Styles/ChangeStyle";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { getStyleChooser } from "WoltLabSuite/Core/Api/Styles/GetStyleChooser";

class ControllerStyleChanger {
  /**
   * Adds the style changer to the bottom navigation.
   */
  constructor() {
    document.querySelectorAll(".jsButtonStyleChanger").forEach((link: HTMLAnchorElement) => {
      link.addEventListener(
        "click",
        promiseMutex((ev) => this.showDialog(ev)),
      );
    });
  }

  /**
   * Loads and displays the style change dialog.
   */
  async showDialog(event: MouseEvent): Promise<void> {
    event.preventDefault();

    const response = await getStyleChooser();
    if (!response.ok) {
      throw new Error("Failed to load style chooser.");
    }

    const dialog = dialogFactory().fromHtml(response.value).withoutControls();

    dialog.content.querySelectorAll(".styleList > li").forEach((style: HTMLLIElement) => {
      style.classList.add("pointer");
      style.addEventListener("click", (ev) => this.#click(ev));
    });

    dialog.show(getPhrase("wcf.style.changeStyle"));
  }

  /**
   * Changes the style and reloads current page.
   */
  async #click(event: MouseEvent): Promise<void> {
    event.preventDefault();

    const listElement = event.currentTarget as HTMLLIElement;
    const styleId = parseInt(listElement.dataset.styleId!, 10);
    const response = await changeStyle(styleId);

    if (!response.ok) {
      throw new Error("Failed to change style.");
    }

    window.location.reload();
  }
}

let controllerStyleChanger: ControllerStyleChanger;

/**
 * Adds the style changer to the bottom navigation.
 */
export function setup(): void {
  if (!controllerStyleChanger) {
    controllerStyleChanger = new ControllerStyleChanger();
  }
}

/**
 * Loads and displays the style change dialog.
 */
export function showDialog(event: MouseEvent): void {
  void controllerStyleChanger.showDialog(event);
}

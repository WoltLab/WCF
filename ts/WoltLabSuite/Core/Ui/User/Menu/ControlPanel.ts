/**
 * User menu for the control panel.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/User/Menu/ControlPanel
 * @woltlabExcludeBundle tiny
 */

import UiCloseOverlay from "../../CloseOverlay";
import { getContainer } from "./Manager";
import { createFocusTrap, FocusTrap } from "focus-trap";
import * as Alignment from "../../Alignment";

const button = document.getElementById("userMenu")!;
const element = button.querySelector(".userMenu") as HTMLElement;
let focusTrap: FocusTrap;
const link = button.querySelector("a")!;

function open(): void {
  if (!element.hidden) {
    return;
  }

  UiCloseOverlay.execute();

  element.hidden = false;

  button.classList.add("open");
  link.setAttribute("aria-expanded", "true");

  focusTrap.activate();

  Alignment.set(element, button, { horizontal: "right" });
}

function close(): void {
  focusTrap.deactivate();

  element.hidden = true;

  button.classList.remove("open");
  link.setAttribute("aria-expanded", "false");
}

let isInitialized = false;
export function setup(): void {
  if (!isInitialized) {
    UiCloseOverlay.add("WoltLabSuite/Core/Ui/User/Menu/ControlPanel", () => close());
    getContainer().append(element);

    element.addEventListener("click", (event) => event.stopPropagation());

    button.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();

      if (element.hidden) {
        open();
      } else {
        close();
      }
    });

    focusTrap = createFocusTrap(element, {
      allowOutsideClick: true,
      escapeDeactivates: (): boolean => {
        close();

        return false;
      },
      fallbackFocus: element,
    });

    isInitialized = true;
  }
}

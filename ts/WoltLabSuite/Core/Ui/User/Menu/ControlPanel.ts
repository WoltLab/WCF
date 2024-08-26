/**
 * User menu for the control panel.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */

import UiCloseOverlay from "../../CloseOverlay";
import { getContainer } from "./Manager";
import { createFocusTrap, FocusTrap } from "focus-trap";
import * as Alignment from "../../Alignment";
import DomUtil from "../../../Dom/Util";

let button: HTMLElement;
let element: HTMLElement;
let focusTrap: FocusTrap;
let link: HTMLAnchorElement;

function open(): void {
  if (!element.hidden) {
    return;
  }

  UiCloseOverlay.execute();

  element.hidden = false;

  button.classList.add("open");
  link.setAttribute("aria-expanded", "true");

  focusTrap.activate();

  setAlignment(element, button);
}

function setAlignment(element: HTMLElement, referenceElement: HTMLElement): void {
  Alignment.set(element, referenceElement, { horizontal: "right" });

  if (window.getComputedStyle(element).position === "fixed" && DomUtil.getFixedParent(referenceElement) !== null) {
    const { top, height } = referenceElement.getBoundingClientRect();
    element.style.setProperty("top", `${top + height}px`);
  }
}

function close(): void {
  focusTrap.deactivate();

  element.hidden = true;

  button.classList.remove("open");
  link.setAttribute("aria-expanded", "false");
}

export function getElement(): HTMLElement {
  return element;
}

let isInitialized = false;
export function setup(): void {
  if (!isInitialized) {
    button = document.getElementById("userMenu")!;
    element = button.querySelector(".userMenu") as HTMLElement;
    link = button.querySelector("a")!;

    UiCloseOverlay.add("WoltLabSuite/Core/Ui/User/Menu/ControlPanel", () => close());
    getContainer().append(element);

    element.addEventListener("click", (event) => event.stopPropagation());

    window.addEventListener(
      "resize",
      () => {
        if (element.hidden) {
          return;
        }

        setAlignment(element, button);
      },
      { passive: true },
    );

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
      escapeDeactivates(): boolean {
        close();

        return false;
      },
      fallbackFocus: element,
    });

    isInitialized = true;
  }
}

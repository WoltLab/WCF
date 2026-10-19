/**
 * Opens the language list of the page footer, a dropdown on large screens and
 * a modal sheet on small screens. `Component/LanguageSwitcher` handles the
 * selection of a language.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { createFocusTrap, FocusTrap } from "focus-trap";
import UiCloseOverlay, { Origin } from "../../Ui/CloseOverlay";
import {
  is as isScreen,
  on as onMediaQuery,
  pageOverlayClose,
  pageOverlayOpen,
  scrollDisable,
  scrollEnable,
} from "../../Ui/Screen";

let container: HTMLElement;
let toggle: HTMLButtonElement;
let popover: HTMLElement;
let backdrop: HTMLElement;
let focusTrap: FocusTrap;
let isSheet = false;

function isOpen(): boolean {
  return !popover.hidden;
}

function open(): void {
  if (isOpen()) {
    return;
  }

  // Closes dropdowns and other overlays.
  UiCloseOverlay.execute();

  popover.hidden = false;
  backdrop.hidden = false;
  toggle.setAttribute("aria-expanded", "true");

  isSheet = isScreen("screen-md-down");
  if (isSheet) {
    popover.setAttribute("aria-modal", "true");
    pageOverlayOpen();
    scrollDisable();
    focusTrap.activate();
  } else {
    popover.querySelector<HTMLElement>('[aria-current="true"]')?.focus();
  }
}

// The focus returns to the toggle unless it has moved elsewhere, e.g. by clicking outside.
function close(returnFocus = popover.contains(document.activeElement)): void {
  if (!isOpen()) {
    return;
  }

  popover.hidden = true;
  backdrop.hidden = true;
  toggle.setAttribute("aria-expanded", "false");

  if (isSheet) {
    popover.removeAttribute("aria-modal");
    pageOverlayClose();
    scrollEnable();
    focusTrap.deactivate({ returnFocus });
  } else if (returnFocus) {
    toggle.focus();
  }
}

export function setup(): void {
  const element = document.querySelector<HTMLElement>(".pageFooterLanguage");
  if (element === null) {
    return;
  }

  container = element;
  toggle = container.querySelector(".pageFooterLanguage__toggle")!;
  popover = container.querySelector(".pageFooterLanguage__popover")!;
  backdrop = container.querySelector(".pageFooterLanguage__backdrop")!;
  focusTrap = createFocusTrap(popover, {
    allowOutsideClick: true,
    escapeDeactivates: false,
    fallbackFocus: popover,
    initialFocus: () => popover.querySelector<HTMLElement>('[aria-current="true"]') ?? false,
    setReturnFocus: toggle,
  });

  // Safari does not focus a button on click, the focus would leave the open dropdown and close it
  // before the click on the toggle could.
  toggle.addEventListener("mousedown", (event) => {
    if (isOpen()) {
      event.preventDefault();
    }
  });

  toggle.addEventListener("click", () => {
    if (isOpen()) {
      close();
    } else {
      open();
    }
  });

  backdrop.addEventListener("click", () => close());
  container.querySelector(".pageFooterLanguage__close")!.addEventListener("click", () => close());

  container.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && isOpen()) {
      event.preventDefault();
      close();
    }
  });

  // The page navigates away, the focus must not jump back to the toggle meanwhile.
  popover.addEventListener("click", (event) => {
    if ((event.target as HTMLElement).closest("[data-switch-language]") !== null) {
      close(false);
    }
  });

  // The sheet traps the focus, the dropdown closes when the focus leaves it.
  container.addEventListener("focusout", (event) => {
    if (isOpen() && !isSheet && !container.contains(event.relatedTarget as Node | null)) {
      close();
    }
  });

  document.addEventListener("click", (event) => {
    if (isOpen() && !container.contains(event.target as Node)) {
      close();
    }
  });

  // Clicks bubbling up to the body are handled above, they would close the list right after opening it.
  UiCloseOverlay.add("WoltLabSuite/Core/Component/PageFooter/LanguageSwitch", (origin) => {
    if (origin !== Origin.Document) {
      close();
    }
  });

  // The dropdown and the sheet use different positions.
  onMediaQuery("screen-md-down", {
    match: () => close(),
    unmatch: () => close(),
  });
}

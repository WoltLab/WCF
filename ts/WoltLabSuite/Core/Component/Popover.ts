/**
 * Customizable popover overlays that show additional information after a short
 * delay.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

import Devtools from "../Devtools";
import DomUtil from "../Dom/Util";
import { getPageOverlayContainer } from "../Helper/PageOverlay";
import { wheneverFirstSeen } from "../Helper/Selector";
import RepeatingTimer from "../Timer/Repeating";
import * as UiAlignment from "../Ui/Alignment";
import UiDropdownSimple from "../Ui/Dropdown/Simple";
import SharedCache from "./Popover/SharedCache";

const enum Delay {
  Hide = 500,
  Show = 800,
}

class Popover {
  #activated = false;
  readonly #cache: SharedCache;
  #container: HTMLElement | undefined = undefined;
  #enabled = true;
  readonly #element: HTMLElement;
  readonly #identifier: string;
  #timerShouldShow: RepeatingTimer | undefined = undefined;
  #timerHide: RepeatingTimer | undefined = undefined;

  constructor(cache: SharedCache, element: HTMLElement, identifier: string) {
    this.#cache = cache;
    this.#element = element;
    this.#identifier = identifier;

    element.addEventListener("mouseenter", () => {
      this.#showPopover();
    });
    element.addEventListener("mouseleave", () => {
      this.#activated = false;
      this.#hidePopover();
    });

    // Links opened in a new tab or through the context menu keep the page in
    // place, leaving the popover to cover the adjacent elements.
    for (const eventName of ["click", "auxclick", "contextmenu"]) {
      element.addEventListener(eventName, () => {
        this.#activated = true;
        this.#hidePopoverImmediately();
      });
    }

    const mq = window.matchMedia("(hover:hover)");
    this.#setEnabled(mq.matches);

    mq.addEventListener("change", (event) => {
      this.#setEnabled(event.matches);
    });

    window.addEventListener("beforeunload", () => {
      this.#setEnabled(false);
    });

    this.#showPopover();
  }

  #showPopover(): void {
    if (!this.#enabled) {
      return;
    }

    this.#timerHide?.stop();

    if (this.#timerShouldShow === undefined) {
      this.#timerShouldShow = new RepeatingTimer((timer) => {
        timer.stop();

        const objectId = this.#getObjectId();
        if (objectId === undefined) {
          console.error("Missing attribute `data-object-id` for element", this.#element);
          return;
        }

        void this.#cache.get(objectId).then((content) => {
          // The element may have been activated while the request was pending.
          if (content === "" || this.#activated) {
            return;
          }

          const container = this.#getContainer();
          destroyDropdowns(container);
          DomUtil.setInnerHtml(container, content);

          UiAlignment.set(container, this.#element, { vertical: "top" });

          container.setAttribute("aria-hidden", "false");
        });
      }, Delay.Show);
    } else {
      this.#timerShouldShow.restart();
    }
  }

  #hidePopover(): void {
    if (!this.#enabled || Devtools._internal_.persistentPopover()) {
      return;
    }

    this.#timerShouldShow?.stop();

    if (this.#timerHide === undefined) {
      this.#timerHide = new RepeatingTimer((timer) => {
        if (this.#container?.querySelector(".dropdown.dropdownOpen") !== null) {
          return;
        }

        timer.stop();

        this.#container?.setAttribute("aria-hidden", "true");
      }, Delay.Hide);
    } else {
      this.#timerHide.restart();
    }
  }

  #hidePopoverImmediately(): void {
    this.#timerShouldShow?.stop();
    this.#timerHide?.stop();

    this.#container?.setAttribute("aria-hidden", "true");
  }

  #setEnabled(enabled: boolean): void {
    this.#enabled = enabled;

    this.#container?.setAttribute("aria-hidden", "true");
  }

  #getObjectId(): number | undefined {
    const objectId = parseInt(this.#element.dataset.objectId || "");
    if (Number.isNaN(objectId) || objectId === 0) {
      return undefined;
    }

    return objectId;
  }

  #getContainer(): HTMLElement {
    if (this.#container === undefined) {
      this.#container = document.createElement("div");
      this.#container.classList.add("popoverContainer");
      this.#container.dataset.identifier = this.#identifier;
      this.#container.setAttribute("aria-hidden", "true");

      this.#container.addEventListener("transitionend", () => {
        if (this.#container!.getAttribute("aria-hidden") === "true") {
          this.#container!.remove();
        }
      });

      this.#container.addEventListener("mouseenter", () => {
        this.#timerHide?.stop();
      });
      this.#container.addEventListener("mouseleave", () => {
        this.#hidePopover();
      });
    }

    if (this.#container.parentNode === null) {
      getPageOverlayContainer().append(this.#container);
    }

    return this.#container;
  }
}

type Configuration = {
  endpoint: string | ((objectId: number) => Promise<string>);
  identifier: string;
  selector: string;
};

const cacheByIdentifier = new Map<string, SharedCache>();

export function setupFor(configuration: Configuration): void {
  const { identifier, endpoint, selector } = configuration;

  const cache = new SharedCache(endpoint);
  cacheByIdentifier.set(identifier, cache);

  wheneverFirstSeen(selector, (element) => {
    // Disregard elements nested inside a popover.
    if (element.closest(".popover, .popoverContainer") !== null) {
      return;
    }

    element.addEventListener(
      "mouseenter",
      () => {
        new Popover(cache, element, identifier);
      },
      { once: true },
    );
  });
}

/**
 * The dropdown menus have been moved into the global container and must be
 * removed together with the content of the popover.
 */
export function destroyDropdowns(element: HTMLElement): void {
  element.querySelectorAll<HTMLElement>(".dropdownToggle[data-target]").forEach((toggle) => {
    UiDropdownSimple.destroy(toggle.dataset.target!);
  });
}

export function resetCache(identifier: string, objectId: number): void {
  cacheByIdentifier.get(identifier)?.reset(objectId);
}

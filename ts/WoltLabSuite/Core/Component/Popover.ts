import DomUtil from "../Dom/Util";
import { getPageOverlayContainer } from "../Helper/PageOverlay";
import { wheneverFirstSeen } from "../Helper/Selector";
import RepeatingTimer from "../Timer/Repeating";
import * as UiAlignment from "../Ui/Alignment";
import SharedCache from "./Popover/SharedCache";

const enum Delay {
  Hide = 500,
  Show = 800,
}

class Popover {
  readonly #cache: SharedCache;
  #container: HTMLElement | undefined = undefined;
  #enabled = true;
  readonly #identifier: string;
  #pendingObjectId: number | undefined = undefined;
  #timerStart: RepeatingTimer | undefined = undefined;
  #timerHide: RepeatingTimer | undefined = undefined;

  constructor(cache: SharedCache, selector: string, identifier: string) {
    this.#cache = cache;
    this.#identifier = identifier;

    wheneverFirstSeen(selector, (element) => {
      element.addEventListener("mouseenter", () => {
        this.#showPopover(element);
      });
      element.addEventListener("mouseleave", () => {
        this.#hidePopover();
      });
    });

    const mq = window.matchMedia("(hover:hover)");
    this.#setEnabled(mq.matches);

    mq.addEventListener("change", (event) => {
      this.#setEnabled(event.matches);
    });

    window.addEventListener("beforeunload", () => {
      this.#setEnabled(false);
    });
  }

  #showPopover(element: HTMLElement): void {
    const objectId = this.#getObjectId(element);

    this.#pendingObjectId = objectId;
    if (this.#timerStart === undefined) {
      this.#timerStart = new RepeatingTimer((timer) => {
        timer.stop();

        const objectId = this.#pendingObjectId!;
        void this.#cache.get(objectId).then((content) => {
          if (objectId !== this.#pendingObjectId) {
            return;
          }

          const container = this.#getContainer();
          DomUtil.setInnerHtml(container, content);

          UiAlignment.set(container, element, { vertical: "top" });

          container.setAttribute("aria-hidden", "false");
        });
      }, Delay.Show);
    } else {
      this.#timerStart.restart();
    }
  }

  #hidePopover(): void {
    if (this.#timerHide === undefined) {
      this.#timerHide = new RepeatingTimer((timer) => {
        timer.stop();

        this.#timerStart?.stop();
        this.#container?.setAttribute("aria-hidden", "true");
      }, Delay.Hide);
    } else {
      this.#timerHide.restart();
    }
  }

  #setEnabled(enabled: boolean): void {
    this.#enabled = enabled;
  }

  #getObjectId(element: HTMLElement): number {
    return parseInt(element.dataset.objectId!);
  }

  #getContainer(): HTMLElement {
    if (this.#container === undefined) {
      this.#container = document.createElement("div");
      this.#container.classList.add("popoverContainer");
      this.#container.dataset.identifier = this.#identifier;
      this.#container.setAttribute("aria-hidden", "true");
    }

    this.#container.remove();
    getPageOverlayContainer().append(this.#container);

    return this.#container;
  }
}

type Configuration = {
  endpoint: string;
  identifier: string;
  selector: string;
};

export function setupFor(configuration: Configuration): void {
  const { identifier, endpoint, selector } = configuration;

  const cache = new SharedCache(endpoint);

  new Popover(cache, selector, identifier);
}

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
      this.#hidePopover();
    });

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
    this.#timerHide?.stop();

    if (this.#timerShouldShow === undefined) {
      this.#timerShouldShow = new RepeatingTimer((timer) => {
        timer.stop();

        const objectId = this.#getObjectId();
        void this.#cache.get(objectId).then((content) => {
          const container = this.#getContainer();
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
    this.#timerShouldShow?.stop();

    if (this.#timerHide === undefined) {
      this.#timerHide = new RepeatingTimer((timer) => {
        timer.stop();

        this.#container?.setAttribute("aria-hidden", "true");
      }, Delay.Hide);
    } else {
      this.#timerHide.restart();
    }
  }

  #setEnabled(enabled: boolean): void {
    this.#enabled = enabled;
  }

  #getObjectId(): number {
    return parseInt(this.#element.dataset.objectId!);
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
    }

    if (this.#container.parentNode === null) {
      getPageOverlayContainer().append(this.#container);
    }

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

  wheneverFirstSeen(selector, (element) => {
    element.addEventListener(
      "mouseenter",
      () => {
        new Popover(cache, element, identifier);
      },
      { once: true },
    );
  });
}

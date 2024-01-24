import { prepareRequest } from "../Ajax/Backend";
import DomUtil from "../Dom/Util";
import { getPageOverlayContainer } from "../Helper/PageOverlay";
import { wheneverFirstSeen } from "../Helper/Selector";
import RepeatingTimer from "../Timer/Repeating";
import * as UiAlignment from "../Ui/Alignment";

const enum Delay {
  Hide = 500,
  Show = 800,
}

class Popover {
  readonly #cache = new Map<number, string>();
  #currentElement: HTMLElement | undefined = undefined;
  #container: HTMLElement | undefined = undefined;
  readonly #endpoint: URL;
  #enabled = true;
  readonly #identifier: string;
  #pendingElement: HTMLElement | undefined = undefined;
  #pendingObjectId: number | undefined = undefined;
  #timerStart: RepeatingTimer | undefined = undefined;
  #timerHide: RepeatingTimer | undefined = undefined;

  constructor(selector: string, endpoint: string, identifier: string) {
    this.#identifier = identifier;
    this.#endpoint = new URL(endpoint);

    wheneverFirstSeen(selector, (element) => {
      element.addEventListener("mouseenter", () => {
        this.#hoverStart(element);
      });
      element.addEventListener("mouseleave", () => {
        this.#hoverEnd(element);
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

  #hoverStart(element: HTMLElement): void {
    const objectId = this.#getObjectId(element);

    this.#pendingObjectId = objectId;
    if (this.#timerStart === undefined) {
      this.#timerStart = new RepeatingTimer((timer) => {
        timer.stop();

        const objectId = this.#pendingObjectId!;
        void this.#getContent(objectId).then((content) => {
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

  #hoverEnd(element: HTMLElement): void {
    this.#timerStart?.stop();
    this.#pendingObjectId = undefined;

    if (this.#timerHide === undefined) {
      this.#timerHide = new RepeatingTimer(() => {
        // do something
      }, Delay.Hide);
    } else {
      this.#timerHide.restart();
    }
  }

  async #getContent(objectId: number): Promise<string> {
    let content = this.#cache.get(objectId);
    if (content !== undefined) {
      return content;
    }

    this.#endpoint.searchParams.set("id", objectId.toString());

    const response = await prepareRequest(this.#endpoint).get().fetchAsResponse();
    if (!response?.ok) {
      return "";
    }

    content = await response.text();
    this.#cache.set(objectId, content);

    return content;
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

  new Popover(selector, endpoint, identifier);
}

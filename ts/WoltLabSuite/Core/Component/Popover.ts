import { prepareRequest } from "../Ajax/Backend";
import DomUtil from "../Dom/Util";
import { getPageOverlayContainer } from "../Helper/PageOverlay";
import { wheneverFirstSeen } from "../Helper/Selector";

class Popover {
  readonly #cache = new Map<number, string>();
  #currentElement: HTMLElement | undefined = undefined;
  #container: HTMLElement | undefined = undefined;
  readonly #endpoint: URL;
  #enabled = true;
  readonly #identifier: string;

  constructor(selector: string, endpoint: string, identifier: string) {
    this.#identifier = identifier;
    this.#endpoint = new URL(endpoint);

    wheneverFirstSeen(selector, (element) => {
      element.addEventListener("mouseenter", () => {
        void this.#hoverStart(element);
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

  async #hoverStart(element: HTMLElement): Promise<void> {
    const objectId = this.#getObjectId(element);

    let content = this.#cache.get(objectId);
    if (content === undefined) {
      content = await this.#fetch(objectId);
      this.#cache.set(objectId, content);
    }

    DomUtil.setInnerHtml(this.#getContainer(), content);
  }

  #hoverEnd(element: HTMLElement): void {}

  async #fetch(objectId: number): Promise<string> {
    this.#endpoint.searchParams.set("id", objectId.toString());

    const response = await prepareRequest(this.#endpoint).get().fetchAsResponse();
    if (response?.ok) {
      return await response.text();
    }

    return "";
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
      this.#container.dataset.identifier = this.#identifier;
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

export class WoltlabCoreCkeditorElement extends HTMLElement {
  #features?: Features;
  #sourceElement: HTMLTextAreaElement;

  connectedCallback() {
    this.setAttribute("source", this.#sourceElement.id);
  }

  setSourceElement(element: HTMLTextAreaElement): void {
    if (this.#sourceElement !== undefined) {
      throw new Error("Cannot initialize the editor element twice.");
    }

    this.#sourceElement = element;

    this.#sourceElement.insertAdjacentElement("beforebegin", this);
    this.append(this.#sourceElement);
  }

  setupFeatures(features: Features) {
    if (this.#features !== undefined) {
      throw new Error("Cannot set the features of the editor, features have already been set.");
    }

    this.dispatchEvent(
      new CustomEvent<Features>("setup:features", {
        detail: features,
      }),
    );

    Object.freeze(features);

    this.#features = features;
  }

  get features(): Features {
    if (this.#features === undefined) {
      throw new Error("Cannot access the features before the initilization took place.");
    }

    return this.#features;
  }

  get source(): string {
    return this.getAttribute("source")!;
  }

  public addEventListener<T extends keyof WoltlabCoreCkeditorEventMap>(
    type: T,
    listener: (this: WoltlabCoreCkeditorElement, ev: WoltlabCoreCkeditorEventMap[T]) => any,
    options?: boolean | AddEventListenerOptions,
  ): void;
  public addEventListener(
    type: string,
    listener: (this: WoltlabCoreCkeditorElement, ev: Event) => any,
    options?: boolean | AddEventListenerOptions,
  ): void {
    super.addEventListener(type, listener, options);
  }
}

export default WoltlabCoreCkeditorElement;

window.customElements.define("woltlab-core-ckeditor", WoltlabCoreCkeditorElement);

interface WoltlabCoreCkeditorEventMap {
  "setup:features": CustomEvent<Features>;
}

export type Features = {
  attachment: boolean;
  autosave: string;
  html: boolean;
  image: boolean;
  media: boolean;
  mention: boolean;
  spoiler: boolean;
  url: boolean;
};

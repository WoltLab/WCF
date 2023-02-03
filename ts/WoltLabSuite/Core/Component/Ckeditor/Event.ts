import type { CKEditor } from "../Ckeditor";

const enum EventNames {
  Ready = "ckeditor5:ready",
}

type CkeditorReadyEventPayload = CKEditor;

class EventDispatcher {
  readonly #element: HTMLElement;

  constructor(element: HTMLElement) {
    this.#element = element;
  }

  ready(payload: CkeditorReadyEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<CkeditorReadyEventPayload>(EventNames.Ready, {
        detail: payload,
      }),
    );
  }
}

class EventListener {
  readonly #element: HTMLElement;

  constructor(element: HTMLElement) {
    this.#element = element;
  }

  ready(callback: (payload: CkeditorReadyEventPayload) => void): void {
    this.#element.addEventListener(
      EventNames.Ready,
      (event: CustomEvent<CkeditorReadyEventPayload>) => {
        callback(event.detail);
      },
      { once: true },
    );
  }
}

export function dispatchToCkeditor(element: HTMLElement): EventDispatcher {
  return new EventDispatcher(element);
}

export function listenToCkeditor(element: HTMLElement): EventListener {
  return new EventListener(element);
}

import type { CKEditor } from "../Ckeditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import type { Features } from "./Configuration";

const enum EventNames {
  Ready = "ckeditor5:ready",
  SetupConfiguration = "ckeditor5:setup-configuration",
  SetupFeatures = "ckeditor5:setup-features",
}

type ReadyEventPayload = CKEditor;
type SetupFeaturesEventPayload = Features;
type SetupConfigurationEventPayload = {
  configuration: EditorConfig;
  features: Features;
};

class EventDispatcher {
  readonly #element: HTMLElement;

  constructor(element: HTMLElement) {
    this.#element = element;
  }

  configuration(payload: SetupConfigurationEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<SetupConfigurationEventPayload>(EventNames.SetupConfiguration, {
        detail: payload,
      }),
    );
  }

  features(payload: SetupFeaturesEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<SetupFeaturesEventPayload>(EventNames.SetupFeatures, {
        detail: payload,
      }),
    );
  }

  ready(payload: ReadyEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<ReadyEventPayload>(EventNames.Ready, {
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

  configuration(callback: (payload: SetupConfigurationEventPayload) => void): void {
    this.#element.addEventListener(
      EventNames.SetupConfiguration,
      (event: CustomEvent<SetupConfigurationEventPayload>) => {
        callback(event.detail);
      },
      { once: true },
    );
  }

  features(callback: (payload: SetupFeaturesEventPayload) => void): void {
    this.#element.addEventListener(
      EventNames.SetupFeatures,
      (event: CustomEvent<SetupFeaturesEventPayload>) => {
        callback(event.detail);
      },
      { once: true },
    );
  }

  ready(callback: (payload: ReadyEventPayload) => void): void {
    this.#element.addEventListener(
      EventNames.Ready,
      (event: CustomEvent<ReadyEventPayload>) => {
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

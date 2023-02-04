import type { CKEditor } from "../Ckeditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import type { Features } from "./Configuration";
import type { InsertAttachmentPayload, RemoveAttachmentPayload, UploadAttachmentEventPayload } from "./Attachment";
import type { UploadMediaEventPayload } from "./Media";
import type { InsertQuoteEventPayload } from "./Quote";

const enum EventNames {
  InsertAttachment = "ckeditor5:insert-attachment",
  InsertQuote = "ckeditor5:insert-quote",
  Ready = "ckeditor5:ready",
  RemoveAttachment = "ckeditor5:remove-attachment",
  Reset = "ckeditor5:reset",
  SetupConfiguration = "ckeditor5:setup-configuration",
  SetupFeatures = "ckeditor5:setup-features",
  UploadAttachment = "ckeditor5:upload-attachment",
  UploadMedia = "ckeditor5:upload-media",
}

type ReadyEventPayload = {
  ckeditor: CKEditor;
};
type ResetEventPayload = {
  ckeditor: CKEditor;
};
type SetupFeaturesEventPayload = {
  features: Features;
};
type SetupConfigurationEventPayload = {
  configuration: EditorConfig;
  features: Features;
};

class EventDispatcher {
  readonly #element: HTMLElement;

  constructor(element: HTMLElement) {
    this.#element = element;
  }

  insertAttachment(payload: InsertAttachmentPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<InsertAttachmentPayload>(EventNames.InsertAttachment, {
        detail: payload,
      }),
    );
  }

  insertQuote(payload: InsertQuoteEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<InsertQuoteEventPayload>(EventNames.InsertQuote, {
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

  removeAttachment(payload: RemoveAttachmentPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<RemoveAttachmentPayload>(EventNames.RemoveAttachment, {
        detail: payload,
      }),
    );
  }

  reset(payload: ResetEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<ResetEventPayload>(EventNames.Reset, {
        detail: payload,
      }),
    );
  }

  setupConfiguration(payload: SetupConfigurationEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<SetupConfigurationEventPayload>(EventNames.SetupConfiguration, {
        detail: payload,
      }),
    );
  }

  setupFeatures(payload: SetupFeaturesEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<SetupFeaturesEventPayload>(EventNames.SetupFeatures, {
        detail: payload,
      }),
    );
  }

  uploadAttachment(payload: UploadAttachmentEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<UploadAttachmentEventPayload>(EventNames.UploadAttachment, {
        detail: payload,
      }),
    );
  }

  uploadMedia(payload: UploadMediaEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<UploadMediaEventPayload>(EventNames.UploadMedia, {
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

  insertAttachment(callback: (payload: InsertAttachmentPayload) => void): this {
    this.#element.addEventListener(EventNames.InsertAttachment, (event: CustomEvent<InsertAttachmentPayload>) => {
      callback(event.detail);
    });

    return this;
  }

  insertQuote(callback: (payload: InsertQuoteEventPayload) => void): this {
    this.#element.addEventListener(EventNames.InsertQuote, (event: CustomEvent<InsertQuoteEventPayload>) => {
      callback(event.detail);
    });

    return this;
  }

  ready(callback: (payload: ReadyEventPayload) => void): this {
    this.#element.addEventListener(
      EventNames.Ready,
      (event: CustomEvent<ReadyEventPayload>) => {
        callback(event.detail);
      },
      { once: true },
    );

    return this;
  }

  removeAttachment(callback: (payload: RemoveAttachmentPayload) => void): this {
    this.#element.addEventListener(EventNames.RemoveAttachment, (event: CustomEvent<RemoveAttachmentPayload>) => {
      callback(event.detail);
    });

    return this;
  }

  reset(callback: (payload: ResetEventPayload) => void): this {
    this.#element.addEventListener(EventNames.Reset, (event: CustomEvent<ResetEventPayload>) => {
      callback(event.detail);
    });

    return this;
  }

  setupConfiguration(callback: (payload: SetupConfigurationEventPayload) => void): this {
    this.#element.addEventListener(
      EventNames.SetupConfiguration,
      (event: CustomEvent<SetupConfigurationEventPayload>) => {
        callback(event.detail);
      },
      { once: true },
    );

    return this;
  }

  setupFeatures(callback: (payload: SetupFeaturesEventPayload) => void): this {
    this.#element.addEventListener(
      EventNames.SetupFeatures,
      (event: CustomEvent<SetupFeaturesEventPayload>) => {
        callback(event.detail);
      },
      { once: true },
    );

    return this;
  }

  uploadAttachment(callback: (payload: UploadAttachmentEventPayload) => void): this {
    this.#element.addEventListener(EventNames.UploadAttachment, (event: CustomEvent<UploadAttachmentEventPayload>) => {
      callback(event.detail);
    });

    return this;
  }

  uploadMedia(callback: (payload: UploadMediaEventPayload) => void): this {
    this.#element.addEventListener(EventNames.UploadMedia, (event: CustomEvent<UploadMediaEventPayload>) => {
      callback(event.detail);
    });

    return this;
  }
}

export function dispatchToCkeditor(element: HTMLElement): EventDispatcher {
  return new EventDispatcher(element);
}

export function listenToCkeditor(element: HTMLElement): EventListener {
  return new EventListener(element);
}

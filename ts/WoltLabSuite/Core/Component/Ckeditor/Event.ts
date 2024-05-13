/**
 * Provides a strongly typed event interface for CKEditor on top of native DOM
 * events to prevent memory leaks.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import type { CKEditor } from "../Ckeditor";
import type { Features } from "./Configuration";
import type { InsertAttachmentPayload, RemoveAttachmentPayload, UploadAttachmentEventPayload } from "./Attachment";
import type { UploadMediaEventPayload } from "./Media";
import type { InsertQuoteEventPayload } from "./Quote";
import type { AutosavePayload } from "./Autosave";
import type { CKEditor5 } from "@woltlab/editor";

const enum EventNames {
  Autosave = "ckeditor5:autosave",
  Bbcode = "ckeditor5:bbcode",
  ChangeData = "ckeditor5:change-data",
  CollectMetaData = "ckeditor5:collect-meta-data",
  Destroy = "ckeditor5:destroy",
  DiscardRecoveredData = "ckeditor5:discard-recovered-data",
  InsertAttachment = "ckeditor5:insert-attachment",
  InsertQuote = "ckeditor5:insert-quote",
  Ready = "ckeditor5:ready",
  RemoveAttachment = "ckeditor5:remove-attachment",
  Reset = "ckeditor5:reset",
  SetupConfiguration = "ckeditor5:setup-configuration",
  SetupFeatures = "ckeditor5:setup-features",
  SubmitOnEnter = "ckeditor5:submit-on-enter",
  UploadAttachment = "ckeditor5:upload-attachment",
  UploadMedia = "ckeditor5:upload-media",
}
type BbcodeEventPayload = {
  bbcode: string;
};
type CollectMetaDataEventPayload = {
  metaData: Record<string, unknown>;
};
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
  configuration: CKEditor5.Core.EditorConfig;
  features: Features;
  modules: typeof CKEditor5;
};
type SubmitOnEnterPayload = {
  ckeditor: CKEditor;
  html: string;
};

class EventDispatcher {
  readonly #element: HTMLElement;

  constructor(element: HTMLElement) {
    this.#element = element;
  }

  collectMetaData(payload: CollectMetaDataEventPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<CollectMetaDataEventPayload>(EventNames.CollectMetaData, {
        detail: payload,
      }),
    );
  }

  changeData(): void {
    this.#element.dispatchEvent(new CustomEvent<void>(EventNames.ChangeData));
  }

  destroy(): void {
    this.#element.dispatchEvent(new CustomEvent<void>(EventNames.Destroy));
  }

  discardRecoveredData(): void {
    this.#element.dispatchEvent(new CustomEvent<void>(EventNames.DiscardRecoveredData));
  }

  autosave(payload: AutosavePayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<AutosavePayload>(EventNames.Autosave, {
        detail: payload,
      }),
    );
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

  submitOnEnter(payload: SubmitOnEnterPayload): void {
    this.#element.dispatchEvent(
      new CustomEvent<SubmitOnEnterPayload>(EventNames.SubmitOnEnter, {
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

  bbcode(callback: (payload: BbcodeEventPayload) => boolean): this {
    this.#element.addEventListener(EventNames.Bbcode, (event: CustomEvent<BbcodeEventPayload>) => {
      const result = callback(event.detail);
      if (result === true) {
        event.preventDefault();
      } else if (result !== false) {
        throw new Error(
          "An event listener for the bbcode event did not return a boolean to indicate if the BBCode is handled.",
        );
      }
    });

    return this;
  }

  changeData(callback: () => void): this {
    this.#element.addEventListener(EventNames.ChangeData, () => {
      callback();
    });

    return this;
  }

  collectMetaData(callback: (payload: CollectMetaDataEventPayload) => void): this {
    this.#element.addEventListener(EventNames.CollectMetaData, (event: CustomEvent<CollectMetaDataEventPayload>) => {
      callback(event.detail);
    });

    return this;
  }

  destroy(callback: () => void): this {
    this.#element.addEventListener(EventNames.Destroy, () => {
      callback();
    });

    return this;
  }

  discardRecoveredData(callback: () => void): this {
    this.#element.addEventListener(
      EventNames.DiscardRecoveredData,
      () => {
        callback();
      },
      { once: true },
    );

    return this;
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

  submitOnEnter(callback: (payload: SubmitOnEnterPayload) => void): this {
    this.#element.addEventListener(EventNames.SubmitOnEnter, (event: CustomEvent<SubmitOnEnterPayload>) => {
      callback(event.detail);
    });

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

  autosave(callback: (payload: AutosavePayload) => void): this {
    this.#element.addEventListener(EventNames.Autosave, (event: CustomEvent<AutosavePayload>) => {
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

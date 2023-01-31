import { getMentionConfiguration } from "./Ckeditor/Mention";
import { setup as setupQuotes } from "./Ckeditor/Quote";
import { setupRemoveAttachment, uploadAttachment } from "./Ckeditor/Attachment";
import { uploadMedia } from "./Ckeditor/Media";
import { removeExpiredDrafts, saveDraft, setupRestoreDraft } from "./Ckeditor/Autosave";

import type ClassicEditor from "@ckeditor/ckeditor5-editor-classic/src/classiceditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import type CkeElement from "@ckeditor/ckeditor5-engine/src/model/element";

type Features = {
  attachment: boolean;
  autosave: string;
  media: boolean;
  mention: boolean;
};

const instances = new WeakMap<HTMLElement, CKEditor>();

class Ckeditor {
  readonly #editor: ClassicEditor;
  readonly #features: Features;

  constructor(editor: ClassicEditor, features: Features) {
    this.#editor = editor;

    Object.freeze(features);
    this.#features = features;

    setupQuotes(this);
  }

  focus(): void {
    this.#editor.editing.view.focus();
  }

  getHtml(): string {
    return this.#editor.data.get();
  }

  insertHtml(html: string): void {
    const viewFragment = this.#editor.data.processor.toView(html);
    const modelFragment = this.#editor.data.toModel(viewFragment);

    this.#editor.model.insertContent(modelFragment);
  }

  insertText(text: string): void {
    const div = document.createElement("div");
    div.textContent = text;

    this.insertHtml(div.innerHTML);
  }

  isVisible(): boolean {
    return this.#editor.ui.element!.clientWidth !== 0;
  }

  setHtml(html: string): void {
    this.#editor.data.set(html);
  }

  removeAll(model: string, attributes: Record<string, string | number | boolean>): void {
    this.#editor.model.change((writer) => {
      const elements = findModelForRemoval(this.#editor.model.document.getRoot()!, model, attributes);
      for (const element of elements) {
        writer.remove(element);
      }
    });
  }

  get element(): HTMLElement {
    return this.#editor.ui.element!;
  }

  get features(): Features {
    return this.#features;
  }

  get sourceElement(): HTMLElement {
    return this.#editor.sourceElement!;
  }
}

function* findModelForRemoval(
  element: CkeElement,
  model: string,
  attributes: Record<string, string | number | boolean>,
): Generator<CkeElement> {
  if (element.is("element", model)) {
    let isMatch = true;
    Object.entries(attributes).forEach(([key, value]) => {
      if (!element.hasAttribute(key)) {
        isMatch = false;
      } else if (element.getAttribute(key) !== value) isMatch = false;
    });

    if (isMatch) {
      yield element;

      return;
    }
  }

  for (const child of element.getChildren()) {
    if (child.is("element")) {
      yield* findModelForRemoval(child, model, attributes);
    }
  }
}

function enableAttachments(element: HTMLElement, configuration: EditorConfig): void {
  // TODO: The typings do not include our custom plugins yet.
  (configuration as any).woltlabUpload = {
    uploadImage: (file: File, abortController: AbortController) => uploadAttachment(element.id, file, abortController),
    uploadOther: (file: File) => uploadAttachment(element.id, file),
  };
}

function enableAutosave(autosave: string, configuration: EditorConfig): void {
  removeExpiredDrafts();

  configuration.autosave = {
    save(editor) {
      saveDraft(autosave, editor.data.get());

      return Promise.resolve();
    },
    // TODO: This should be longer, because exporting the data is potentially expensive.
    waitingTime: 2_000,
  };
}

function enableMedia(element: HTMLElement, configuration: EditorConfig): void {
  // TODO: The typings do not include our custom plugins yet.
  (configuration as any).woltlabUpload = {
    uploadImage: (file: File, abortController: AbortController) => uploadMedia(element.id, file, abortController),
    uploadOther: (file: File) => uploadMedia(element.id, file),
  };
}

function enableMentions(configuration: EditorConfig): void {
  configuration.mention = getMentionConfiguration();
}

export async function setupCkeditor(
  element: HTMLElement,
  configuration: EditorConfig,
  features: Features,
): Promise<CKEditor> {
  let editor = instances.get(element);
  if (editor === undefined) {
    if (features.attachment) {
      enableAttachments(element, configuration);
    } else if (features.media) {
      enableMedia(element, configuration);
    }

    if (features.mention) {
      enableMentions(configuration);
    }

    if (features.autosave !== "") {
      enableAutosave(features.autosave, configuration);
    }

    const cke = await window.CKEditor5.create(element, configuration);
    editor = new Ckeditor(cke, features);

    if (features.attachment) {
      setupRemoveAttachment(editor);
    }

    if (features.autosave) {
      setupRestoreDraft(cke, features.autosave);
    }

    instances.set(element, editor);
  }

  return editor;
}

export function getCkeditor(element: HTMLElement): CKEditor | undefined {
  return instances.get(element);
}

export function getCkeditorById(id: string): Ckeditor | undefined {
  const element = document.getElementById(id);
  if (element === null) {
    throw new Error(`Unable to find an element with the id '${id}'.`);
  }

  return getCkeditor(element);
}

export type CKEditor = InstanceType<typeof Ckeditor>;

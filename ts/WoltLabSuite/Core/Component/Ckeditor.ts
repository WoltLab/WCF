import { getMentionConfiguration } from "./Ckeditor/Mention";
import { setup as setupQuotes } from "./Ckeditor/Quote";
import { setupRemoveAttachment, uploadAttachment } from "./Ckeditor/Attachment";
import { uploadMedia } from "./Ckeditor/Media";
import { deleteDraft, removeExpiredDrafts, saveDraft, setupRestoreDraft } from "./Ckeditor/Autosave";
import { fire as fireEvent } from "../Event/Handler";

import "./Ckeditor/woltlab-core-ckeditor";

import type ClassicEditor from "@ckeditor/ckeditor5-editor-classic/src/classiceditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import type CkeElement from "@ckeditor/ckeditor5-engine/src/model/element";
import type { Features } from "./Ckeditor/Configuration";
import WoltlabCoreCkeditorElement from "./Ckeditor/woltlab-core-ckeditor";

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

  destroy(): Promise<void> {
    return this.#editor.destroy();
  }

  discardDraft(): void {
    if (this.#features.autosave) {
      deleteDraft(this.#features.autosave);
    }
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

  reset(): void {
    this.setHtml("");

    const identifier = this.sourceElement.id;
    fireEvent("com.woltlab.wcf.ckeditor5", `reset_${identifier}`);
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

type WoltlabBbcodeItem = {
  icon: string;
  name: string;
  label: string;
};

function enableFeatures(element: HTMLElement, configuration: EditorConfig, features: Features): void {
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

  const bbcodes = (configuration as any).woltlabBbcode as WoltlabBbcodeItem[];
  for (const { name } of bbcodes) {
    (configuration.toolbar as any).push(`woltlabBbcode_${name}`);
  }
}

export function initializeCkeditor(elementId: string): void {
  const element = document.getElementById(elementId);
  if (element === null) {
    throw new Error(`Unable to find the source element '${elementId}' for the editor.`);
  }

  if (element.nodeName !== "TEXTAREA") {
    throw new Error(`Expected a <textarea> as the source element '${elementId}' for the editor.`);
  }

  const ckeditor = document.createElement("woltlab-core-ckeditor");
  ckeditor.setSourceElement(element as HTMLTextAreaElement);
}

export async function setupCkeditor(
  element: HTMLElement,
  configuration: EditorConfig,
  features: Features,
): Promise<CKEditor> {
  if (instances.has(element)) {
    throw new TypeError(`Cannot initialize the editor for '${element.id}' twice.`);
  }

  enableFeatures(element, configuration, features);

  const cke = await window.CKEditor5.create(element, configuration);
  const editor = new Ckeditor(cke, features);

  if (features.attachment) {
    setupRemoveAttachment(editor);
  }

  if (features.autosave) {
    setupRestoreDraft(cke, features.autosave);
  }

  if (features.media) {
    void import("../Media/Manager/Editor").then(({ MediaManagerEditor }) => {
      new MediaManagerEditor({
        ckeditor: editor,
      });
    });
  }

  instances.set(element, editor);

  const event = new CustomEvent<CKEditor>("ckeditor5:ready", {
    detail: editor,
  });
  element.dispatchEvent(event);

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

// TODO: The name of this function should be `getCkeditor()`,
//       but this way it is easier for development purposes.
export function getCkeditor2(elementId: string): WoltlabCoreCkeditorElement {
  const element = document.querySelector<WoltlabCoreCkeditorElement>(`woltlab-core-ckeditor[source="${elementId}"]`);
  if (element === null) {
    throw new Error(`There is no editor instance for the source '${elementId}'.`);
  }

  return element;
}

export type CKEditor = InstanceType<typeof Ckeditor>;

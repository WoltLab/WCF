import { initializeMention } from "./Ckeditor/Mention";
import { setup as setupQuote } from "./Ckeditor/Quote";
import { setup as setupAttachment } from "./Ckeditor/Attachment";
import { initializeMedia } from "./Ckeditor/Media";
import { deleteDraft, initializeAutosave, setupRestoreDraft } from "./Ckeditor/Autosave";

import type ClassicEditor from "@ckeditor/ckeditor5-editor-classic/src/classiceditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import type CkeElement from "@ckeditor/ckeditor5-engine/src/model/element";
import { createConfigurationFor, Features } from "./Ckeditor/Configuration";

const instances = new WeakMap<HTMLElement, CKEditor>();

class Ckeditor {
  readonly #editor: ClassicEditor;
  readonly #features: Features;

  constructor(editor: ClassicEditor, features: Features) {
    this.#editor = editor;
    this.#features = features;

    setupQuote(this);
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

    this.sourceElement.dispatchEvent(new CustomEvent("ckeditor5:reset"));
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

type WoltlabBbcodeItem = {
  icon: string;
  name: string;
  label: string;
};

function initializeFeatures(element: HTMLElement, features: Features): void {
  element.dispatchEvent(
    new CustomEvent<Features>("ckeditor5:features", {
      detail: features,
    }),
  );
  Object.freeze(features);
}

type CkeditorConfigurationEventPayload = {
  configuration: EditorConfig;
  features: Features;
};
export type CkeditorConfigurationEvent = CustomEvent<CkeditorConfigurationEventPayload>;

type CkeditorReadyEventPayload = CKEditor;
export type CkeditorReadyEvent = CustomEvent<CkeditorReadyEventPayload>;

function initializeConfiguration(element: HTMLElement, features: Features, bbcodes: WoltlabBbcodeItem[]): EditorConfig {
  const configuration = createConfigurationFor(features);
  (configuration as any).woltlabBbcode = bbcodes;

  if (!features.attachment && features.media) {
    initializeMedia(element, configuration);
  }

  if (features.mention) {
    initializeMention(configuration);
  }

  if (features.autosave !== "") {
    initializeAutosave(features.autosave, configuration);
  }

  element.dispatchEvent(
    new CustomEvent<CkeditorConfigurationEventPayload>("ckeditor5:configuration", {
      detail: {
        configuration,
        features,
      },
    }),
  );

  for (const { name } of bbcodes) {
    (configuration.toolbar as any).push(`woltlabBbcode_${name}`);
  }

  return configuration;
}

export async function setupCkeditor(
  element: HTMLElement,
  features: Features,
  bbcodes: WoltlabBbcodeItem[],
): Promise<CKEditor> {
  if (instances.has(element)) {
    throw new TypeError(`Cannot initialize the editor for '${element.id}' twice.`);
  }

  initializeFeatures(element, features);

  setupAttachment(element);

  const configuration = initializeConfiguration(element, features, bbcodes);

  const cke = await window.CKEditor5.create(element, configuration);
  const editor = new Ckeditor(cke, features);

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

  const event = new CustomEvent<CkeditorReadyEventPayload>("ckeditor5:ready", {
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

export type CKEditor = InstanceType<typeof Ckeditor>;

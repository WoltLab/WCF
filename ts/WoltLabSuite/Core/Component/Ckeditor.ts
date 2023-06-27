/**
 * The userland API for interactions with a CKEditor instance.
 *
 * The purpose of this implementation is to provide a stable and strongly typed
 * API that can be reused in components. Access to the raw API of CKEditor is
 * not exposed, if you feel that you need additional helper methods then please
 * submit an issue.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { setup as setupAttachment } from "./Ckeditor/Attachment";
import { setup as setupMedia } from "./Ckeditor/Media";
import { setup as setupMention } from "./Ckeditor/Mention";
import { setup as setupQuote } from "./Ckeditor/Quote";
import { deleteDraft, initializeAutosave, setupRestoreDraft } from "./Ckeditor/Autosave";
import { createConfigurationFor, Features } from "./Ckeditor/Configuration";
import { dispatchToCkeditor } from "./Ckeditor/Event";
import { setup as setupSubmitOnEnter } from "./Ckeditor/SubmitOnEnter";
import { normalizeLegacyMessage } from "./Ckeditor/Normalizer";
import Devtools from "../Devtools";

import { ClassicEditor, CodeBlockConfig, EditorConfig, Element as CkeElement } from "./Ckeditor/Types";

import "ckeditor5-bundle";

const instances = new WeakMap<HTMLElement, CKEditor>();

class Ckeditor {
  readonly #editor: ClassicEditor;
  readonly #features: Features;

  constructor(editor: ClassicEditor, features: Features) {
    this.#editor = editor;
    this.#features = features;
  }

  async destroy(): Promise<void> {
    dispatchToCkeditor(this.sourceElement).destroy();

    await this.#editor.destroy();
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
    this.#editor.model.change((writer) => {
      const viewFragment = this.#editor.data.processor.toView(html);
      const modelFragment = this.#editor.data.toModel(viewFragment);

      const range = this.#editor.model.insertContent(modelFragment);

      writer.setSelection(range.end);
      this.#editor.focus();
    });
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

    dispatchToCkeditor(this.sourceElement).reset({
      ckeditor: this,
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

type WoltlabBbcodeItem = {
  icon: string;
  name: string;
  label: string;
};

function initializeFeatures(element: HTMLElement, features: Features): void {
  dispatchToCkeditor(element).setupFeatures({
    features,
  });

  if (features.autosave && Devtools._internal_.editorAutosave() === false) {
    features.autosave = "";
  }

  Object.freeze(features);
}

function initializeConfiguration(
  element: HTMLElement,
  features: Features,
  bbcodes: WoltlabBbcodeItem[],
  codeBlockLanguages: CodeBlockConfig["languages"],
): EditorConfig {
  const configuration = createConfigurationFor(features);
  configuration.codeBlock = {
    languages: codeBlockLanguages,
  };

  (configuration as any).woltlabBbcode = bbcodes;

  if (features.autosave !== "") {
    initializeAutosave(element, configuration, features.autosave);
  }

  dispatchToCkeditor(element).setupConfiguration({
    configuration,
    features,
  });

  for (const { name } of bbcodes) {
    (configuration.toolbar as any).push(`woltlabBbcode_${name}`);
  }

  return configuration;
}

export async function setupCkeditor(
  element: HTMLElement,
  features: Features,
  bbcodes: WoltlabBbcodeItem[],
  codeBlockLanguages: CodeBlockConfig["languages"],
): Promise<CKEditor> {
  if (instances.has(element)) {
    throw new TypeError(`Cannot initialize the editor for '${element.id}' twice.`);
  }

  initializeFeatures(element, features);

  if (features.attachment) {
    setupAttachment(element);
  }
  if (features.media) {
    setupMedia(element);
  }
  setupMention(element);
  if (features.quoteBlock) {
    setupQuote(element);
  }

  const configuration = initializeConfiguration(element, features, bbcodes, codeBlockLanguages);

  normalizeLegacyMessage(element);

  const cke = await window.CKEditor5.create(element, configuration);
  const ckeditor = new Ckeditor(cke, features);

  if (features.autosave) {
    setupRestoreDraft(cke, features.autosave);
  }

  instances.set(element, ckeditor);

  dispatchToCkeditor(element).ready({
    ckeditor,
  });

  if (features.submitOnEnter) {
    setupSubmitOnEnter(cke, ckeditor);
  }

  if (ckeditor.getHtml() === "") {
    dispatchToCkeditor(element).discardRecoveredData();
  }

  const enableDebug = window.ENABLE_DEBUG_MODE && window.ENABLE_DEVELOPER_TOOLS;
  if (enableDebug && Devtools._internal_.editorInspector()) {
    void import("@ckeditor/ckeditor5-inspector").then((inspector) => {
      inspector.default.attach(cke);
    });
  }

  return ckeditor;
}

export function getCkeditor(element: HTMLElement): CKEditor | undefined {
  return instances.get(element);
}

export function getCkeditorById(id: string, throwIfNotExists = true): Ckeditor | undefined {
  const element = document.getElementById(id);
  if (element === null) {
    if (throwIfNotExists) {
      throw new Error(`Unable to find an element with the id '${id}'.`);
    } else {
      return undefined;
    }
  }

  return getCkeditor(element);
}

export type CKEditor = InstanceType<typeof Ckeditor>;

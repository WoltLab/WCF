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
 * @woltlabExcludeBundle tiny
 */

import { setup as setupAttachment } from "./Ckeditor/Attachment";
import { setup as setupMedia } from "./Ckeditor/Media";
import { setup as setupMention } from "./Ckeditor/Mention";
import { setup as setupQuote } from "./Ckeditor/Quote";
import { deleteDraft, initializeAutosave, setupRestoreDraft } from "./Ckeditor/Autosave";
import { createConfigurationFor, Features } from "./Ckeditor/Configuration";
import { dispatchToCkeditor } from "./Ckeditor/Event";
import { setup as setupSubmitOnEnter } from "./Ckeditor/SubmitOnEnter";
import { normalizeLegacyHtml, normalizeLegacyMessage } from "./Ckeditor/Normalizer";
import { element as scrollToElement } from "../Ui/Scroll";
import Devtools from "../Devtools";
import { ClassicEditor, CodeBlockConfig, EditorConfig, Element as CkeElement } from "./Ckeditor/Types";
import { setupSubmitShortcut } from "./Ckeditor/Keyboard";
import { setup as setupLayer } from "./Ckeditor/Layer";
import { browser, touch } from "../Environment";

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
    // Check if the editor is (at least partially) in the viewport otherwise
    // scroll to it before setting the focus.
    const editorContainer = this.#editor.ui.element!;
    const { bottom, top } = editorContainer.getBoundingClientRect();
    const viewportHeight = window.innerHeight;

    let isPartiallyVisible = false;
    if (top > 0 && top < viewportHeight) {
      isPartiallyVisible = true;
    } else if (bottom > 0 && bottom < viewportHeight) {
      isPartiallyVisible = true;
    }

    if (isPartiallyVisible) {
      this.#editor.editing.view.focus();
    } else {
      scrollToElement(editorContainer, () => {
        this.#editor.editing.view.focus();
      });
    }
  }

  getHtml(): string {
    return this.#editor.data.get();
  }

  insertHtml(html: string): void {
    html = normalizeLegacyHtml(html);

    this.#editor.model.change((writer) => {
      const viewFragment = this.#editor.data.processor.toView(html);
      const modelFragment = this.#editor.data.toModel(viewFragment);

      const range = this.#editor.model.insertContent(modelFragment);

      writer.setSelection(range.end);
      this.focus();
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

    if (browser() === "safari" && !touch()) {
      // Safari sometimes suffers from a “reverse typing” effect caused by the
      // improper shift of the focus out of the editing area.
      // https://github.com/ckeditor/ckeditor5/issues/14702
      const editor = this.#editor.ui.element!;
      editor.focus();
      window.setTimeout(() => {
        editor.blur();
      }, 0);
    }
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
    const isMatch = Object.entries(attributes).every(([key, value]) => {
      if (!element.hasAttribute(key)) {
        return false;
      }

      return String(element.getAttribute(key)) === value.toString();
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

  setupLayer();

  await import("ckeditor5-bundle");

  await new Promise((resolve) => {
    window.requestAnimationFrame(resolve);
  });

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

  setupSubmitShortcut(ckeditor);

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

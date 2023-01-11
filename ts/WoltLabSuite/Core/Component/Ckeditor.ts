import { getPossibleMentions } from "./Ckeditor/Mention";
import { setup as setupQuotes } from "./Ckeditor/Quote";

import type ClassicEditor from "@ckeditor/ckeditor5-editor-classic/src/classiceditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import { uploadAttachment } from "./Ckeditor/Attachment";

const instances = new WeakMap<HTMLElement, CKEditor>();

class Ckeditor {
  readonly #editor: ClassicEditor;

  constructor(editor: ClassicEditor) {
    this.#editor = editor;

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

  setHtml(html: string): void {
    this.#editor.data.set(html);
  }

  get sourceElement(): HTMLElement {
    return this.#editor.sourceElement!;
  }
}

function enableAttachments(element: HTMLElement, configuration: EditorConfig): void {
  // TODO: The typings do not include our custom plugins yet.
  (configuration as any).woltlabUpload = {
    upload: (file: File, abortController: AbortController) => uploadAttachment(element.id, file, abortController),
  };
}

function enableMentions(configuration: EditorConfig): void {
  configuration.mention = {
    feeds: [
      {
        feed: (query) => {
          // TODO: The typings are outdated, cast the result to `any`.
          return getPossibleMentions(query) as any;
        },
        marker: "@",
        minimumCharacters: 3,
      },
    ],
  };
}

export async function setupCkeditor(element: HTMLElement, configuration: EditorConfig): Promise<CKEditor> {
  let editor = instances.get(element);
  if (editor === undefined) {
    if (element.dataset.disableAttachments !== "true") {
      enableAttachments(element, configuration);
    }

    if (element.dataset.supportMention === "true") {
      enableMentions(configuration);
    }

    const cke = await window.CKEditor5.create(element, configuration);
    editor = new Ckeditor(cke);

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

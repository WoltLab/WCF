import { getPossibleMentions } from "./Ckeditor/Mention";
import { setup as setupQuotes } from "./Ckeditor/Quote";

import type ClassicEditor from "@ckeditor/ckeditor5-editor-classic/src/classiceditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import { uploadAttachment } from "./Ckeditor/Attachment";
import { createFragmentFromHtml } from "../Dom/Util";
import { uploadMedia } from "./Ckeditor/Media";

type Features = {
  attachment: boolean;
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

  setHtml(html: string): void {
    this.#editor.data.set(html);
  }

  get features(): Features {
    return this.#features;
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

function enableMedia(element: HTMLElement, configuration: EditorConfig): void {
  // TODO: The typings do not include our custom plugins yet.
  (configuration as any).woltlabUpload = {
    upload: (file: File, abortController: AbortController) => uploadMedia(element.id, file, abortController),
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
        itemRenderer: (item: Awaited<ReturnType<typeof getPossibleMentions>>[0]) => {
          // TODO: This is ugly.
          return createFragmentFromHtml(`
            <span>${item.icon} ${item.text}</span>
          `).firstElementChild as HTMLElement;
        },
        marker: "@",
        minimumCharacters: 3,
      },
    ],
  };
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

    const cke = await window.CKEditor5.create(element, configuration);
    editor = new Ckeditor(cke, features);

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

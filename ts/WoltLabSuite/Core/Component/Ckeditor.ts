import type { Editor } from "@ckeditor/ckeditor5-core";

const instances = new WeakMap<HTMLElement, CKEditor>();

class Ckeditor {
  readonly #editor: Editor;

  constructor(editor: Editor) {
    this.#editor = editor;
  }

  focus(): void {
    this.#editor.editing.view.focus();
  }

  getHtml(): string {
    return this.#editor.data.get();
  }

  setHtml(html: string): void {
    this.#editor.data.set(html);
  }
}

export async function setupCkeditor(element: HTMLElement): Promise<CKEditor> {
  let editor = instances.get(element);
  if (editor === undefined) {
    const cke = await window.CKEditor5.create(element);
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

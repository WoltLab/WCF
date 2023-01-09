import ClassicEditor from "@ckeditor/ckeditor5-editor-classic/src/classiceditor";
import { setup as setupQuotes } from "./Ckeditor/Quote";

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

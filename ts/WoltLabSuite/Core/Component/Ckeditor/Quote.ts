import { escapeHTML } from "../../StringUtil";
import type { CKEditor } from "../Ckeditor";

type Payload = {
  author: string;
  content: string;
  isText: boolean;
  link: string;
};

function insertQuote(editor: CKEditor, content: string, contentIsText: boolean, author: string, link: string) {
  if (contentIsText) {
    content = escapeHTML(content);
  }

  author = escapeHTML(author);
  link = escapeHTML(link);

  editor.insertHtml(
    `<woltlab-ckeditor-blockquote author="${author}" link="${link}">${content}</woltlab-ckeditor-blockquote>`,
  );
}

export function setup(editor: CKEditor): void {
  editor.sourceElement.addEventListener("ckeditor5:insert-quote", (event: CustomEvent<Payload>) => {
    const { author, content, isText, link } = event.detail;

    insertQuote(editor, content, isText, author, link);
  });
}

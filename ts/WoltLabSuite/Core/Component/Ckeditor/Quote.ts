import * as EventHandler from "../../Event/Handler";
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
  EventHandler.add("com.woltlab.wcf.ckeditor5", `insertQuote_${editor.sourceElement.id}`, (data: Payload) => {
    const { author, content, isText, link } = data;

    insertQuote(editor, content, isText, author, link);
  });
}

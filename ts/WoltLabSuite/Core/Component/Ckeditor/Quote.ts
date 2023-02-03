import { escapeHTML } from "../../StringUtil";
import { listenToCkeditor } from "./Event";

import type { CKEditor } from "../Ckeditor";

function insertQuote(editor: CKEditor, payload: CkeditorInsertQuoteEventPayload) {
  let { author, content, link } = payload;

  if (payload.isText) {
    content = escapeHTML(content);
  }

  author = escapeHTML(author);
  link = escapeHTML(link);

  editor.insertHtml(
    `<woltlab-ckeditor-blockquote author="${author}" link="${link}">${content}</woltlab-ckeditor-blockquote>`,
  );
}

type CkeditorInsertQuoteEventPayload = {
  author: string;
  content: string;
  isText: boolean;
  link: string;
};
export type CkeditorInsertQuoteEvent = CustomEvent<CkeditorInsertQuoteEventPayload>;

export function setup(element: HTMLElement): void {
  listenToCkeditor(element).ready((ckeditor) => {
    element.addEventListener("ckeditor5:insert-quote", (event: CustomEvent<CkeditorInsertQuoteEventPayload>) => {
      insertQuote(ckeditor, event.detail);
    });
  });
}

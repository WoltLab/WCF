/**
 * Inserts quotes into the editor.
 *
 * @author Alexander Ebert, Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle tiny
 */

import { escapeHTML } from "../../StringUtil";
import { listenToCkeditor } from "./Event";

import type { CKEditor } from "../Ckeditor";

function insertQuote(editor: CKEditor, payload: InsertQuoteEventPayload) {
  let { author, content, link } = payload;

  if (payload.isText) {
    content = approximateHtmlRepresentation(content);
  }

  author = escapeHTML(author);
  link = escapeHTML(link);
  const quoteId = payload.uuid !== undefined ? ` data-quote-id="${escapeHTML(payload.uuid)}"` : "";

  editor.insertHtml(
    `<woltlab-quote data-author="${author}" data-link="${link}"${quoteId}>${content}</woltlab-quote>
    <p><br data-cke-filler="true"></p>`,
  );
}

function approximateHtmlRepresentation(text: string): string {
  text = escapeHTML(text);

  // An empty paragraph is marked by 5 consecutive new lines.
  text = text.replaceAll("\n\n\n\n\n", '</p><p><br data-cke-filler="true"></p><p>');

  return text
    .split("\n\n")
    .map((value) => {
      value = value.replaceAll("\n", "<br>");

      return `<p>${value}</p>`;
    })
    .join("");
}

export type InsertQuoteEventPayload = {
  author: string;
  content: string;
  isText: boolean;
  link: string;
  uuid?: string;
};

export function setup(element: HTMLElement): void {
  listenToCkeditor(element).ready(({ ckeditor }) => {
    listenToCkeditor(element).insertQuote((payload) => {
      insertQuote(ckeditor, payload);
    });
  });
}

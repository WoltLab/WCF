/**
 * Inserts quotes into the editor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
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

  editor.insertHtml(
    `<woltlab-quote data-author="${author}" data-link="${link}">${content}</woltlab-quote>
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
};

export function setup(element: HTMLElement): void {
  listenToCkeditor(element).ready(({ ckeditor }) => {
    listenToCkeditor(element).insertQuote((payload) => {
      insertQuote(ckeditor, payload);
    });
  });
}

/**
 * Inserts quotes into the editor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import { escapeHTML } from "../../StringUtil";
import { listenToCkeditor } from "./Event";

import type { CKEditor } from "../Ckeditor";

function insertQuote(editor: CKEditor, payload: InsertQuoteEventPayload) {
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

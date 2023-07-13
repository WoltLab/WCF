/**
 * Provides a custom editor button to insert article links.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @woltlabExcludeBundle all
 */

import { getPhrase } from "../../Language";
import { open as searchArticle } from "../../Ui/Article/Search";
import { listenToCkeditor } from "../Ckeditor/Event";

import type { CKEditor } from "../Ckeditor";

function setupBbcode(ckeditor: CKEditor): void {
  ckeditor.sourceElement.addEventListener("bbcode", (evt: CustomEvent<string>) => {
    const bbcode = evt.detail;
    if (bbcode === "wsa") {
      evt.preventDefault();

      searchArticle((articleId) => {
        ckeditor.insertText(`[wsa='${articleId}'][/wsa]`);
      });
    }
  });
}

export function setup(element: HTMLElement) {
  listenToCkeditor(element).setupConfiguration(({ configuration }) => {
    (configuration as any).woltlabBbcode.push({
      icon: "file-word;false",
      name: "wsa",
      label: getPhrase("wcf.editor.button.article"),
    });
  });

  listenToCkeditor(element).ready(({ ckeditor }) => {
    setupBbcode(ckeditor);
  });
}

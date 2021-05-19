/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Article
 * @woltlabExcludeBundle tiny
 */

import * as Core from "../../Core";
import * as UiArticleSearch from "../Article/Search";
import { RedactorEditor } from "./Editor";

class UiRedactorArticle {
  protected readonly _editor: RedactorEditor;

  constructor(editor: RedactorEditor, button: HTMLAnchorElement) {
    this._editor = editor;

    button.addEventListener("click", (ev) => this._click(ev));
  }

  protected _click(event: MouseEvent): void {
    event.preventDefault();

    UiArticleSearch.open((articleId) => this._insert(articleId));
  }

  protected _insert(articleId: number): void {
    this._editor.buffer.set();

    this._editor.insert.text(`[wsa='${articleId}'][/wsa]`);
  }
}

Core.enableLegacyInheritance(UiRedactorArticle);

export = UiRedactorArticle;

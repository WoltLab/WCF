/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Redactor/Page
 * @woltlabExcludeBundle tiny
 */

import * as Core from "../../Core";
import * as UiPageSearch from "../Page/Search";
import { RedactorEditor } from "./Editor";

class UiRedactorPage {
  protected _editor: RedactorEditor;

  constructor(editor: RedactorEditor, button: HTMLAnchorElement) {
    this._editor = editor;

    button.addEventListener("click", (ev) => this._click(ev));
  }

  protected _click(event: MouseEvent): void {
    event.preventDefault();

    UiPageSearch.open((pageId) => this._insert(pageId));
  }

  protected _insert(pageId: string): void {
    this._editor.buffer.set();

    this._editor.insert.text(`[wsp='${pageId}'][/wsp]`);
  }
}

Core.enableLegacyInheritance(UiRedactorPage);

export = UiRedactorPage;

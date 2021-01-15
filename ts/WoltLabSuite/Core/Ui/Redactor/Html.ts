/**
 * Manages html code blocks.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Html
 */

import * as Core from "../../Core";
import * as EventHandler from "../../Event/Handler";
import * as Language from "../../Language";
import { RedactorEditor } from "./Editor";

class UiRedactorHtml {
  protected readonly _editor: RedactorEditor;
  protected readonly _elementId: string;
  protected _pre: HTMLElement | null = null;

  /**
   * Initializes the source code management.
   */
  constructor(editor: RedactorEditor) {
    this._editor = editor;
    this._elementId = this._editor.$element[0].id;

    EventHandler.add("com.woltlab.wcf.redactor2", `bbcode_woltlabHtml_${this._elementId}`, (data) =>
      this._bbcodeCode(data),
    );
    EventHandler.add("com.woltlab.wcf.redactor2", `observe_load_${this._elementId}`, () => this._observeLoad());

    // support for active button marking
    this._editor.opts.activeButtonsStates["woltlab-html"] = "woltlabHtml";

    // bind listeners on init
    this._observeLoad();
  }

  /**
   * Intercepts the insertion of `[woltlabHtml]` tags and uses a native `<pre>` instead.
   */
  protected _bbcodeCode(data: { cancel: boolean }): void {
    data.cancel = true;

    let pre = this._editor.selection.block();
    if (pre && pre.nodeName === "PRE" && !pre.classList.contains("woltlabHtml")) {
      return;
    }

    this._editor.button.toggle({}, "pre", "func", "block.format");

    pre = this._editor.selection.block();
    if (pre && pre.nodeName === "PRE") {
      pre.classList.add("woltlabHtml");

      if (pre.childElementCount === 1 && pre.children[0].nodeName === "BR") {
        // drop superfluous linebreak
        pre.removeChild(pre.children[0]);
      }

      this._setTitle(pre);

      // work-around for Safari
      this._editor.caret.end(pre);
    }
  }

  /**
   * Binds event listeners and sets quote title on both editor
   * initialization and when switching back from code view.
   */
  protected _observeLoad(): void {
    this._editor.$editor[0].querySelectorAll("pre.woltlabHtml").forEach((pre: HTMLElement) => {
      this._setTitle(pre);
    });
  }

  /**
   * Sets or updates the code's header title.
   */
  protected _setTitle(pre: HTMLElement): void {
    ["title", "description"].forEach((title) => {
      const phrase = Language.get(`wcf.editor.html.${title}`);

      if (pre.dataset[title] !== phrase) {
        pre.dataset[title] = phrase;
      }
    });
  }
}

Core.enableLegacyInheritance(UiRedactorHtml);

export = UiRedactorHtml;

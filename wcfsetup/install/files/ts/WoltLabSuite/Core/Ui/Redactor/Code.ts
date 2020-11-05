/**
 * Manages code blocks.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Code
 */

import * as Core from "../../Core";
import DomUtil from "../../Dom/Util";
import * as EventHandler from "../../Event/Handler";
import * as Language from "../../Language";
import * as StringUtil from "../../StringUtil";
import UiDialog from "../Dialog";
import { DialogCallbackObject, DialogCallbackSetup } from "../Dialog/Data";
import { RedactorEditor, WoltLabEventData } from "./Editor";
import * as UiRedactorPseudoHeader from "./PseudoHeader";
import PrismMeta from "../../prism-meta";

type Highlighter = [string, string];

let _headerHeight = 0;

class UiRedactorCode implements DialogCallbackObject {
  protected readonly _callbackEdit: (ev: MouseEvent) => void;
  protected readonly _editor: RedactorEditor;
  protected readonly _elementId: string;
  protected _pre: HTMLElement | null = null;

  /**
   * Initializes the source code management.
   */
  constructor(editor: RedactorEditor) {
    this._editor = editor;
    this._elementId = this._editor.$element[0].id;

    EventHandler.add("com.woltlab.wcf.redactor2", `bbcode_code_${this._elementId}`, (data) => this._bbcodeCode(data));
    EventHandler.add("com.woltlab.wcf.redactor2", `observe_load_${this._elementId}`, () => this._observeLoad());

    // support for active button marking
    this._editor.opts.activeButtonsStates.pre = "code";

    // static bind to ensure that removing works
    this._callbackEdit = this._edit.bind(this);

    // bind listeners on init
    this._observeLoad();
  }

  /**
   * Intercepts the insertion of `[code]` tags and uses a native `<pre>` instead.
   */
  protected _bbcodeCode(data: WoltLabEventData): void {
    data.cancel = true;

    let pre = this._editor.selection.block();
    if (pre && pre.nodeName === "PRE" && pre.classList.contains("woltlabHtml")) {
      return;
    }

    this._editor.button.toggle({}, "pre", "func", "block.format");

    pre = this._editor.selection.block();
    if (pre && pre.nodeName === "PRE" && !pre.classList.contains("woltlabHtml")) {
      if (pre.childElementCount === 1 && pre.children[0].nodeName === "BR") {
        // drop superfluous linebreak
        pre.removeChild(pre.children[0]);
      }

      this._setTitle(pre);

      pre.addEventListener("click", this._callbackEdit);

      // work-around for Safari
      this._editor.caret.end(pre);
    }
  }

  /**
   * Binds event listeners and sets quote title on both editor
   * initialization and when switching back from code view.
   */
  protected _observeLoad(): void {
    this._editor.$editor[0].querySelectorAll("pre:not(.woltlabHtml)").forEach((pre: HTMLElement) => {
      pre.addEventListener("mousedown", this._callbackEdit);
      this._setTitle(pre);
    });
  }

  /**
   * Opens the dialog overlay to edit the code's properties.
   */
  protected _edit(event: MouseEvent): void {
    const pre = event.currentTarget as HTMLPreElement;

    if (_headerHeight === 0) {
      _headerHeight = UiRedactorPseudoHeader.getHeight(pre);
    }

    // check if the click hit the header
    const offset = DomUtil.offset(pre);
    if (event.pageY > offset.top && event.pageY < offset.top + _headerHeight) {
      event.preventDefault();

      this._editor.selection.save();
      this._pre = pre;

      UiDialog.open(this);
    }
  }

  /**
   * Saves the changes to the code's properties.
   */
  _dialogSubmit(): void {
    const id = "redactor-code-" + this._elementId;
    const pre = this._pre!;

    ["file", "highlighter", "line"].forEach((attr) => {
      const input = document.getElementById(`${id}-${attr}`) as HTMLInputElement;
      pre.dataset[attr] = input.value;
    });

    this._setTitle(pre);
    this._editor.caret.after(pre);

    UiDialog.close(this);
  }

  /**
   * Sets or updates the code's header title.
   */
  protected _setTitle(pre: HTMLElement): void {
    const file = pre.dataset.file!;
    let highlighter = pre.dataset.highlighter!;

    highlighter =
      this._editor.opts.woltlab.highlighters.indexOf(highlighter) !== -1 ? PrismMeta[highlighter].title : "";

    const title = Language.get("wcf.editor.code.title", {
      file,
      highlighter,
    });

    if (pre.dataset.title !== title) {
      pre.dataset.title = title;
    }
  }

  protected _delete(event: MouseEvent): void {
    event.preventDefault();

    const pre = this._pre!;
    let caretEnd = pre.nextElementSibling || pre.previousElementSibling;
    if (caretEnd === null && pre.parentElement !== this._editor.core.editor()[0]) {
      caretEnd = pre.parentElement;
    }

    if (caretEnd === null) {
      this._editor.code.set("");
      this._editor.focus.end();
    } else {
      pre.remove();
      this._editor.caret.end(caretEnd);
    }

    UiDialog.close(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    const id = `redactor-code-${this._elementId}`;
    const idButtonDelete = `${id}-button-delete`;
    const idButtonSave = `${id}-button-save`;
    const idFile = `${id}-file`;
    const idHighlighter = `${id}-highlighter`;
    const idLine = `${id}-line`;

    return {
      id: id,
      options: {
        onClose: () => {
          this._editor.selection.restore();

          UiDialog.destroy(this);
        },

        onSetup: () => {
          document.getElementById(idButtonDelete)!.addEventListener("click", (ev) => this._delete(ev));

          // set highlighters
          let highlighters = `<option value="">${Language.get("wcf.editor.code.highlighter.detect")}</option>
            <option value="plain">${Language.get("wcf.editor.code.highlighter.plain")}</option>`;

          const values: Highlighter[] = this._editor.opts.woltlab.highlighters.map((highlighter) => {
            return [highlighter, PrismMeta[highlighter].title];
          });

          // sort by label
          values.sort((a, b) => a[1].localeCompare(b[1]));

          highlighters += values
            .map(([highlighter, title]) => {
              return `<option value="${highlighter}">${StringUtil.escapeHTML(title)}</option>`;
            })
            .join("\n");

          document.getElementById(idHighlighter)!.innerHTML = highlighters;
        },

        onShow: () => {
          const pre = this._pre!;

          const highlighter = document.getElementById(idHighlighter) as HTMLSelectElement;
          highlighter.value = pre.dataset.highlighter || "";
          const line = ~~(pre.dataset.line || 1);

          const lineInput = document.getElementById(idLine) as HTMLInputElement;
          lineInput.value = line.toString();

          const filename = document.getElementById(idFile) as HTMLInputElement;
          filename.value = pre.dataset.file || "";
        },

        title: Language.get("wcf.editor.code.edit"),
      },
      source: `<div class="section">
          <dl>
            <dt>
              <label for="${idHighlighter}">${Language.get("wcf.editor.code.highlighter")}</label>
            </dt>
            <dd>
              <select id="${idHighlighter}"></select>
              <small>${Language.get("wcf.editor.code.highlighter.description")}</small>
            </dd>
          </dl>
          <dl>
            <dt>
              <label for="${idLine}">${Language.get("wcf.editor.code.line")}</label>
            </dt>
            <dd>
              <input type="number" id="${idLine}" min="0" value="1" class="long" data-dialog-submit-on-enter="true">
              <small>${Language.get("wcf.editor.code.line.description")}</small>
            </dd>
          </dl>
          <dl>
            <dt>
              <label for="${idFile}">${Language.get("wcf.editor.code.file")}</label>
            </dt>
            <dd>
              <input type="text" id="${idFile}" class="long" data-dialog-submit-on-enter="true">
              <small>${Language.get("wcf.editor.code.file.description")}</small>
            </dd>
          </dl>
        </div>
        <div class="formSubmit">
          <button id="${idButtonSave}" class="buttonPrimary" data-type="submit">${Language.get(
        "wcf.global.button.save",
      )}</button>
          <button id="${idButtonDelete}">${Language.get("wcf.global.button.delete")}</button>
        </div>`,
    };
  }
}

Core.enableLegacyInheritance(UiRedactorCode);

export = UiRedactorCode;

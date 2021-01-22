/**
 * Manages quotes.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Ui/Redactor/Quote
 */

import * as Core from "../../Core";
import DomUtil from "../../Dom/Util";
import * as EventHandler from "../../Event/Handler";
import * as Language from "../../Language";
import * as StringUtil from "../../StringUtil";
import UiDialog from "../Dialog";
import { DialogCallbackSetup } from "../Dialog/Data";
import { RedactorEditor } from "./Editor";
import * as UiRedactorMetacode from "./Metacode";
import * as UiRedactorPseudoHeader from "./PseudoHeader";

interface QuoteData {
  author: string;
  content: string;
  isText: boolean;
  link: string;
}

let _headerHeight = 0;

class UiRedactorQuote {
  protected readonly _editor: RedactorEditor;
  protected readonly _elementId: string;
  protected _quote: HTMLElement | null = null;

  /**
   * Initializes the quote management.
   */
  constructor(editor: RedactorEditor, button: JQuery) {
    this._editor = editor;
    this._elementId = this._editor.$element[0].id;

    EventHandler.add("com.woltlab.wcf.redactor2", `observe_load_${this._elementId}`, () => this._observeLoad());

    this._editor.button.addCallback(button, this._click.bind(this));

    // bind listeners on init
    this._observeLoad();

    // quote manager
    EventHandler.add("com.woltlab.wcf.redactor2", `insertQuote_${this._elementId}`, (data) => this._insertQuote(data));
  }

  /**
   * Inserts a quote.
   */
  protected _insertQuote(data: QuoteData): void {
    if (this._editor.WoltLabSource.isActive()) {
      return;
    }

    EventHandler.fire("com.woltlab.wcf.redactor2", "showEditor");

    const editor = this._editor.core.editor()[0];
    this._editor.selection.restore();

    this._editor.buffer.set();

    // caret must be within a `<p>`, if it is not: move it
    let block = this._editor.selection.block();
    if (block === false) {
      this._editor.focus.end();
      block = this._editor.selection.block() as HTMLElement;
    }

    while (block && block.parentElement !== editor) {
      block = block.parentElement!;
    }

    const quote = document.createElement("woltlab-quote");
    quote.dataset.author = data.author;
    quote.dataset.link = data.link;

    let content = data.content;
    if (data.isText) {
      content = StringUtil.escapeHTML(content);
      content = `<p>${content}</p>`;
      content = content.replace(/\n\n/g, "</p><p>");
      content = content.replace(/\n/g, "<br>");
    } else {
      content = UiRedactorMetacode.convertFromHtml(this._editor.$element[0].id, content);
    }

    // bypass the editor as `insert.html()` doesn't like us
    quote.innerHTML = content;

    const blockParent = block.parentElement!;
    blockParent.insertBefore(quote, block.nextSibling);

    if (block.nodeName === "P" && (block.innerHTML === "<br>" || block.innerHTML.replace(/\u200B/g, "") === "")) {
      blockParent.removeChild(block);
    }

    // avoid adjacent blocks that are not paragraphs
    let sibling = quote.previousElementSibling;
    if (sibling && sibling.nodeName !== "P") {
      sibling = document.createElement("p");
      sibling.textContent = "\u200B";
      quote.insertAdjacentElement("beforebegin", sibling);
    }

    this._editor.WoltLabCaret.paragraphAfterBlock(quote);

    this._editor.buffer.set();
  }

  /**
   * Toggles the quote block on button click.
   */
  protected _click(): void {
    this._editor.button.toggle({}, "woltlab-quote", "func", "block.format");

    const quote = this._editor.selection.block();
    if (quote && quote.nodeName === "WOLTLAB-QUOTE") {
      this._setTitle(quote);

      quote.addEventListener("click", (ev) => this._edit(ev));

      // work-around for Safari
      this._editor.caret.end(quote);
    }
  }

  /**
   * Binds event listeners and sets quote title on both editor
   * initialization and when switching back from code view.
   */
  protected _observeLoad(): void {
    document.querySelectorAll("woltlab-quote").forEach((quote: HTMLElement) => {
      quote.addEventListener("mousedown", (ev) => this._edit(ev));
      this._setTitle(quote);
    });
  }

  /**
   * Opens the dialog overlay to edit the quote's properties.
   */
  protected _edit(event: MouseEvent): void {
    const quote = event.currentTarget as HTMLElement;

    if (_headerHeight === 0) {
      _headerHeight = UiRedactorPseudoHeader.getHeight(quote);
    }

    // check if the click hit the header
    const offset = DomUtil.offset(quote);
    if (event.pageY > offset.top && event.pageY < offset.top + _headerHeight) {
      event.preventDefault();

      this._editor.selection.save();
      this._quote = quote;

      UiDialog.open(this);
    }
  }

  /**
   * Saves the changes to the quote's properties.
   *
   * @protected
   */
  _dialogSubmit(): void {
    const id = `redactor-quote-${this._elementId}`;
    const urlInput = document.getElementById(`${id}-url`) as HTMLInputElement;

    const url = urlInput.value.replace(/\u200B/g, "").trim();
    // simple test to check if it at least looks like it could be a valid url
    if (url.length && !/^https?:\/\/[^/]+/.test(url)) {
      DomUtil.innerError(urlInput, Language.get("wcf.editor.quote.url.error.invalid"));

      return;
    } else {
      DomUtil.innerError(urlInput, false);
    }

    const quote = this._quote!;

    // set author
    const author = document.getElementById(id + "-author") as HTMLInputElement;
    quote.dataset.author = author.value;

    // set url
    quote.dataset.link = url;

    this._setTitle(quote);
    this._editor.caret.after(quote);

    UiDialog.close(this);
  }

  /**
   * Sets or updates the quote's header title.
   */
  protected _setTitle(quote: HTMLElement): void {
    const title = Language.get("wcf.editor.quote.title", {
      author: quote.dataset.author!,
      url: quote.dataset.url!,
    });

    if (quote.dataset.title !== title) {
      quote.dataset.title = title;
    }
  }

  protected _delete(event: MouseEvent): void {
    event.preventDefault();

    const quote = this._quote!;

    let caretEnd = quote.nextElementSibling || quote.previousElementSibling;
    if (caretEnd === null && quote.parentElement !== this._editor.core.editor()[0]) {
      caretEnd = quote.parentElement;
    }

    if (caretEnd === null) {
      this._editor.code.set("");
      this._editor.focus.end();
    } else {
      quote.remove();
      this._editor.caret.end(caretEnd);
    }

    UiDialog.close(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    const id = `redactor-quote-${this._elementId}`;
    const idAuthor = `${id}-author`;
    const idButtonDelete = `${id}-button-delete`;
    const idButtonSave = `${id}-button-save`;
    const idUrl = `${id}-url`;

    return {
      id: id,
      options: {
        onClose: () => {
          this._editor.selection.restore();

          UiDialog.destroy(this);
        },

        onSetup: () => {
          const button = document.getElementById(idButtonDelete) as HTMLButtonElement;
          button.addEventListener("click", (ev) => this._delete(ev));
        },

        onShow: () => {
          const author = document.getElementById(idAuthor) as HTMLInputElement;
          author.value = this._quote!.dataset.author || "";

          const url = document.getElementById(idUrl) as HTMLInputElement;
          url.value = this._quote!.dataset.link || "";
        },

        title: Language.get("wcf.editor.quote.edit"),
      },
      source: `<div class="section">
          <dl>
            <dt>
              <label for="${idAuthor}">${Language.get("wcf.editor.quote.author")}</label>
            </dt>
            <dd>
              <input type="text" id="${idAuthor}" class="long" data-dialog-submit-on-enter="true">
            </dd>
          </dl>
          <dl>
            <dt>
              <label for="${idUrl}">${Language.get("wcf.editor.quote.url")}</label>
            </dt>
            <dd>
              <input type="text" id="${idUrl}" class="long" data-dialog-submit-on-enter="true">
              <small>${Language.get("wcf.editor.quote.url.description")}</small>
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

Core.enableLegacyInheritance(UiRedactorQuote);

export = UiRedactorQuote;

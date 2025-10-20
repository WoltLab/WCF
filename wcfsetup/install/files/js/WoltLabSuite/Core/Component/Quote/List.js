/**
 * Handles quotes for CKEditor 5 message fields.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "WoltLabSuite/Core/Component/Ckeditor/Event", "WoltLabSuite/Core/Component/Message/MessageTabMenu", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Component/Quote/Message", "WoltLabSuite/Core/Component/Quote/Storage", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/StringUtil"], function (require, exports, tslib_1, Event_1, MessageTabMenu_1, Language_1, Message_1, Storage_1, Util_1, StringUtil_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.getQuoteList = getQuoteList;
    exports.refreshQuoteLists = refreshQuoteLists;
    exports.setup = setup;
    Util_1 = tslib_1.__importDefault(Util_1);
    const quoteLists = new Map();
    class QuoteList {
        #container;
        #editor;
        #editorId;
        constructor(editorId, editor, containerId) {
            this.#editorId = editorId;
            this.#editor = editor;
            this.#container = document.getElementById(containerId ? containerId : `quotes_${editorId}`);
            if (this.#container === null) {
                throw new Error(`The quotes container for '${editorId}' does not exist.`);
            }
            this.#editor.closest("form")?.addEventListener("submit", () => {
                this.#formSubmitted();
            });
            this.renderQuotes();
        }
        renderQuotes() {
            this.#container.innerHTML = "";
            let quotesCount = 0;
            for (const [key, quotes] of (0, Storage_1.getQuotes)()) {
                const message = (0, Storage_1.getMessage)(key);
                quotesCount += quotes.size;
                quotes.forEach((quote, uuid) => {
                    const fragment = Util_1.default.createFragmentFromHtml(`
<div class="quoteBox quoteBox--tabMenu">
  <div class="quoteBoxIcon">
    <img src="${(0, StringUtil_1.escapeHTML)(message.avatar)}" alt="" class="userAvatarImage" height="24" width="24">
  </div>
  <div class="quoteBoxTitle">
    <a href="${(0, StringUtil_1.escapeHTML)(message.link)}" target="_blank">${(0, StringUtil_1.escapeHTML)(message.author)}</a>
  </div>
  <div class="quoteBoxButtons">
    <button type="button" class="button small jsTooltip" title="${(0, Language_1.getPhrase)("wcf.global.button.delete")}" data-action="delete">
      <fa-icon name="times"></fa-icon>
    </button>
    <button type="button" class="button buttonPrimary small jsTooltip" title="${(0, Language_1.getPhrase)("wcf.message.quote.insertQuote")}" data-action="insert">
      <fa-icon name="paste"></fa-icon>
    </button>
  </div>
  <div class="quoteBoxContent htmlContent">
    ${quote.rawMessage ?? quote.message}
  </div>
</div>
        `);
                    fragment.querySelector('button[data-action="insert"]').addEventListener("click", () => {
                        (0, Storage_1.markQuoteAsUsed)(this.#editorId, uuid);
                        const content = quote.rawMessage || quote.message;
                        if (content === null) {
                            throw new Error("Expected either the `rawMessage` or `message` to be a string.");
                        }
                        (0, Event_1.dispatchToCkeditor)(this.#editor).insertQuote({
                            author: message.author,
                            content,
                            isText: !quote.rawMessage,
                            link: message.link,
                        });
                    });
                    fragment.querySelector('button[data-action="delete"]').addEventListener("click", () => {
                        (0, Storage_1.removeQuote)(key, uuid);
                        (0, Message_1.removeQuoteStatus)(key);
                    });
                    this.#container.append(fragment);
                });
            }
            const tabMenu = (0, MessageTabMenu_1.getTabMenu)(this.#editorId);
            if (tabMenu === undefined) {
                throw new Error(`Could not find the tab menu for '${this.#editorId}'.`);
            }
            tabMenu.setTabCounter("quotes", quotesCount);
            if (quotesCount > 0) {
                tabMenu.showTab("quotes");
            }
            else {
                tabMenu.hideTab("quotes");
            }
        }
        #formSubmitted() {
            const formSubmit = this.#editor.closest("form").querySelector(".formSubmit");
            (0, Storage_1.getUsedQuotes)(this.#editorId).forEach((uuid) => {
                formSubmit.append(Util_1.default.createFragmentFromHtml(`<input type="hidden" name="__removeQuoteIDs[${(0, StringUtil_1.escapeHTML)(this.#editorId)}][]" value="${(0, StringUtil_1.escapeHTML)(uuid)}">`));
            });
        }
    }
    function getQuoteList(editorId) {
        return quoteLists.get(editorId);
    }
    function refreshQuoteLists() {
        for (const quoteList of quoteLists.values()) {
            quoteList.renderQuotes();
        }
    }
    function setup(editorId, containerId) {
        if (quoteLists.has(editorId)) {
            return;
        }
        const editor = document.getElementById(editorId);
        if (editor === null) {
            throw new Error(`The editor '${editorId}' does not exist.`);
        }
        (0, Event_1.listenToCkeditor)(editor)
            .ready(({ ckeditor }) => {
            if (ckeditor.features.quoteBlock) {
                quoteLists.set(editorId, new QuoteList(editorId, editor, containerId));
            }
            if (ckeditor.isVisible()) {
                (0, Message_1.setActiveEditor)(ckeditor, ckeditor.features.quoteBlock);
            }
            ckeditor.focusTracker.on("change:isFocused", (_evt, _name, isFocused) => {
                if (isFocused) {
                    (0, Message_1.setActiveEditor)(ckeditor, ckeditor.features.quoteBlock);
                }
            });
        })
            .destroy(() => {
            (0, Message_1.removeActiveEditor)(editor);
        });
    }
});

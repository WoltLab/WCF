/**
 * Highlights code in the Code bbcode.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Bbcode/Code
 */
define(["require", "exports", "tslib", "../Language", "../Clipboard", "../Ui/Notification", "../Prism", "../Prism/Helper", "../prism-meta"], function (require, exports, tslib_1, Language, Clipboard, UiNotification, Prism_1, PrismHelper, prism_meta_1) {
    "use strict";
    Language = tslib_1.__importStar(Language);
    Clipboard = tslib_1.__importStar(Clipboard);
    UiNotification = tslib_1.__importStar(UiNotification);
    Prism_1 = tslib_1.__importDefault(Prism_1);
    PrismHelper = tslib_1.__importStar(PrismHelper);
    prism_meta_1 = tslib_1.__importDefault(prism_meta_1);
    async function waitForIdle() {
        return new Promise((resolve, _reject) => {
            if (window.requestIdleCallback) {
                window.requestIdleCallback(resolve, { timeout: 5000 });
            }
            else {
                setTimeout(resolve, 0);
            }
        });
    }
    class Code {
        constructor(container) {
            var _a;
            this.container = container;
            this.codeContainer = this.container.querySelector(".codeBoxCode > code");
            this.language = (_a = Array.from(this.codeContainer.classList)
                .find((klass) => /^language-([a-z0-9_-]+)$/.test(klass))) === null || _a === void 0 ? void 0 : _a.replace(/^language-/, "");
        }
        static processAll() {
            document.querySelectorAll(".codeBox:not([data-processed])").forEach((codeBox) => {
                codeBox.dataset.processed = "1";
                const handle = new Code(codeBox);
                if (handle.language) {
                    void handle.highlight();
                }
                handle.createCopyButton();
            });
        }
        createCopyButton() {
            const header = this.container.querySelector(".codeBoxHeader");
            if (!header) {
                return;
            }
            const button = document.createElement("span");
            button.className = "icon icon24 fa-files-o pointer jsTooltip";
            button.setAttribute("title", Language.get("wcf.message.bbcode.code.copy"));
            button.addEventListener("click", () => {
                void Clipboard.copyElementTextToClipboard(this.codeContainer).then(() => {
                    UiNotification.show(Language.get("wcf.message.bbcode.code.copy.success"));
                });
            });
            header.appendChild(button);
        }
        async highlight() {
            if (!this.language) {
                throw new Error("No language detected");
            }
            if (!prism_meta_1.default[this.language]) {
                throw new Error(`Unknown language '${this.language}'`);
            }
            this.container.classList.add("highlighting");
            // Step 1) Load the requested grammar.
            await new Promise((resolve_1, reject_1) => { require(["prism/components/prism-" + prism_meta_1.default[this.language].file], resolve_1, reject_1); }).then(tslib_1.__importStar);
            // Step 2) Perform the highlighting into a temporary element.
            await waitForIdle();
            const grammar = Prism_1.default.languages[this.language];
            if (!grammar) {
                throw new Error(`Invalid language '${this.language}' given.`);
            }
            const container = document.createElement("div");
            container.innerHTML = Prism_1.default.highlight(this.codeContainer.textContent, grammar, this.language);
            // Step 3) Insert the highlighted lines into the page.
            // This is performed in small chunks to prevent the UI thread from being blocked for complex
            // highlight results.
            await waitForIdle();
            const originalLines = this.codeContainer.querySelectorAll(".codeBoxLine > span");
            const highlightedLines = PrismHelper.splitIntoLines(container);
            for (let chunkStart = 0, max = originalLines.length; chunkStart < max; chunkStart += Code.chunkSize) {
                await waitForIdle();
                const chunkEnd = Math.min(chunkStart + Code.chunkSize, max);
                for (let offset = chunkStart; offset < chunkEnd; offset++) {
                    const toReplace = originalLines[offset];
                    const replacement = highlightedLines.next().value;
                    toReplace.parentNode.replaceChild(replacement, toReplace);
                }
            }
            this.container.classList.remove("highlighting");
            this.container.classList.add("highlighted");
        }
    }
    Code.chunkSize = 50;
    return Code;
});

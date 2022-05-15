/**
 * Highlights code in the Code bbcode.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Bbcode/Code
 */

import * as Language from "../Language";
import * as Clipboard from "../Clipboard";
import * as UiNotification from "../Ui/Notification";
import Prism from "../Prism";
import * as PrismHelper from "../Prism/Helper";

async function waitForIdle(): Promise<void> {
  return new Promise((resolve, _reject) => {
    if ((window as any).requestIdleCallback) {
      (window as any).requestIdleCallback(resolve, { timeout: 5000 });
    } else {
      setTimeout(resolve, 0);
    }
  });
}

class Code {
  private static readonly chunkSize = 50;

  private readonly container: HTMLElement;
  private codeContainer: HTMLElement;
  private language: string | undefined;

  constructor(container: HTMLElement) {
    this.container = container;
    this.codeContainer = this.container.querySelector(".codeBoxCode > code") as HTMLElement;

    this.language = Array.from(this.codeContainer.classList)
      .find((klass) => /^language-([a-z0-9_-]+)$/.test(klass))
      ?.replace(/^language-/, "");

    this.calculateLineNumberWidth();
  }

  public static processAll(): void {
    document.querySelectorAll(".codeBox:not([data-processed])").forEach((codeBox: HTMLElement) => {
      codeBox.dataset.processed = "1";

      const handle = new Code(codeBox);

      if (handle.language) {
        void handle.highlight();
      }

      handle.createCopyButton();
    });
  }

  public createCopyButton(): void {
    const header = this.container.querySelector(".codeBoxHeader");

    if (!header) {
      return;
    }

    const button = document.createElement("span");
    button.tabIndex = 0;
    button.setAttribute("role", "button");
    button.className = "icon icon24 fa-files-o pointer jsTooltip";
    button.setAttribute("title", Language.get("wcf.message.bbcode.code.copy"));

    const clickCallback = async () => {
      await Clipboard.copyElementTextToClipboard(this.codeContainer);

      UiNotification.show(Language.get("wcf.message.bbcode.code.copy.success"));
    };
    button.addEventListener("click", clickCallback);
    button.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();

        void clickCallback();
      }
    });

    header.appendChild(button);
  }

  public async highlight(): Promise<void> {
    if (!this.language) {
      throw new Error("No language detected");
    }

    const PrismMeta = (await import("../prism-meta")).default;
    if (!PrismMeta[this.language]) {
      throw new Error(`Unknown language '${this.language}'`);
    }

    this.container.classList.add("highlighting");

    // Step 1) Load the requested grammar.
    await import("prism/components/prism-" + PrismMeta[this.language].file);

    // Step 2) Perform the highlighting into a temporary element.
    await waitForIdle();

    const grammar = Prism.languages[this.language];
    if (!grammar) {
      throw new Error(`Invalid language '${this.language}' given.`);
    }

    const container = document.createElement("div");
    container.innerHTML = Prism.highlight(this.codeContainer.textContent!, grammar, this.language);

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
        const toReplace = originalLines[offset]!;
        const replacement = highlightedLines.next().value as Element;
        toReplace.parentNode!.replaceChild(replacement, toReplace);
      }
    }

    this.container.classList.remove("highlighting");
    this.container.classList.add("highlighted");
  }

  private calculateLineNumberWidth(): void {
    if (!this.codeContainer) {
      return;
    }

    let maxLength = 0;
    this.codeContainer.querySelectorAll(".lineAnchor").forEach((anchor: HTMLAnchorElement) => {
      const length = anchor.title.length;
      if (length > maxLength) {
        maxLength = length;
      }
    });

    // Clamp the max length to 6 (up to line 999,999) to prevent layout issues.
    if (maxLength > 6) {
      maxLength = 6;
    }

    this.container.style.setProperty("--line-number-width", maxLength.toString());
  }
}

export = Code;

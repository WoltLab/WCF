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
import PrismMeta from "../prism-meta";

const CHUNK_SIZE = 50;

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
  private readonly container: HTMLElement;
  private codeContainer: HTMLElement;
  private language: string | undefined;

  constructor(container: HTMLElement) {
    this.container = container;
    this.codeContainer = this.container.querySelector(".codeBoxCode > code") as HTMLElement;

    this.language = Array.from(this.codeContainer.classList)
      .find((klass) => /^language-([a-z0-9_-]+)$/.test(klass))
      ?.replace(/^language-/, "");
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
    button.className = "icon icon24 fa-files-o pointer jsTooltip";
    button.setAttribute("title", Language.get("wcf.message.bbcode.code.copy"));
    button.addEventListener("click", () => {
      void Clipboard.copyElementTextToClipboard(this.codeContainer).then(() => {
        UiNotification.show(Language.get("wcf.message.bbcode.code.copy.success"));
      });
    });

    header.appendChild(button);
  }

  public async highlight(): Promise<void> {
    if (!this.language) {
      throw new Error("No language detected");
    }
    if (!PrismMeta[this.language]) {
      throw new Error(`Unknown language '${this.language}'`);
    }

    this.container.classList.add("highlighting");

    await import("prism/components/prism-" + PrismMeta[this.language].file);

    await waitForIdle();

    const grammar = Prism.languages[this.language];
    if (!grammar) {
      throw new Error(`Invalid language '${this.language}' given.`);
    }

    const container = document.createElement("div");
    container.innerHTML = Prism.highlight(this.codeContainer.textContent!, grammar, this.language);

    await waitForIdle();

    const highlighted = PrismHelper.splitIntoLines(container);
    const highlightedLines = highlighted.querySelectorAll("[data-number]");
    const originalLines = this.codeContainer.querySelectorAll(".codeBoxLine > span");

    if (highlightedLines.length !== originalLines.length) {
      throw new Error("Unreachable");
    }

    for (let chunkStart = 0, max = highlightedLines.length; chunkStart < max; chunkStart += CHUNK_SIZE) {
      await waitForIdle();
      const chunkEnd = Math.min(chunkStart + CHUNK_SIZE, max);

      for (let offset = chunkStart; offset < chunkEnd; offset++) {
        originalLines[offset]!.parentNode!.replaceChild(highlightedLines[offset], originalLines[offset]);
      }
    }

    this.container.classList.remove("highlighting");
    this.container.classList.add("highlighted");
  }
}

export = Code;

/**
 * Cleans up the markup of legacy messages.
 *
 * Messages created in the previous editor used empty paragraphs to create empty
 * lines. In addition, Firefox kept trailing <br> in lines with content, which
 * causes issues with CKEditor.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import DomUtil from "../../Dom/Util";

function normalizeBr(div: HTMLElement): void {
  div.querySelectorAll("br").forEach((br) => {
    unwrapBr(br);
    removeTrailingBr(br);
  });
}

function unwrapBr(br: HTMLElement): void {
  for (;;) {
    if (br.previousSibling || br.nextSibling) {
      return;
    }

    const parent = br.parentElement!;
    switch (parent.tagName) {
      case "B":
      case "DEL":
      case "EM":
      case "I":
      case "STRONG":
      case "SUB":
      case "SUP":
      case "SPAN":
      case "U":
        parent.insertAdjacentElement("afterend", br);
        parent.remove();
        break;

      default:
        return;
    }
  }
}

function removeTrailingBr(br: HTMLElement): void {
  if (br.dataset.ckeFiller === "true") {
    return;
  }

  const paragraphOrTableCell = br.closest("p, td");
  if (paragraphOrTableCell === null) {
    return;
  }

  if (!DomUtil.isAtNodeEnd(br, paragraphOrTableCell)) {
    return;
  }

  if (paragraphOrTableCell.tagName === "TD" || paragraphOrTableCell.childNodes.length > 1) {
    br.remove();
  }
}

function getPossibleSpacerParagraphs(div: HTMLElement): HTMLParagraphElement[] {
  const paragraphs: HTMLParagraphElement[] = [];

  div.querySelectorAll("p").forEach((paragraph) => {
    if (paragraph.childElementCount === 1) {
      const child = paragraph.children[0] as HTMLElement;
      if (child.tagName === "BR" && child.dataset.ckeFiller !== "true") {
        paragraphs.push(paragraph);
      }
    }
  });

  return paragraphs;
}

function reduceSpacerParagraphs(paragraphs: HTMLParagraphElement[]): void {
  if (paragraphs.length === 0) {
    return;
  }

  for (let i = 0, length = paragraphs.length; i < length; i++) {
    const candidate = paragraphs[i];
    let offset = 0;

    // Searches for adjacent paragraphs.
    while (i + offset + 1 < length) {
      const nextCandidate = paragraphs[i + offset + 1];
      if (candidate.nextElementSibling !== nextCandidate) {
        break;
      }

      offset++;
    }

    if (offset === 0) {
      // An offset of 0 means that this is a single paragraph and we
      // can safely remove it.
      candidate.remove();
    } else {
      let numberOfParagraphsToRemove: number;

      // We need to reduce the number of paragraphs by half, unless it
      // is an uneven number in which case we need to remove one
      // additional paragraph.
      if (offset % 2 === 1) {
        // 2 -> 1, 4 -> 2
        numberOfParagraphsToRemove = Math.ceil(offset / 2);
      } else {
        // 3 -> 1, 5 -> 2
        numberOfParagraphsToRemove = Math.ceil(offset / 2) + 1;
      }

      const removeParagraphs = paragraphs.slice(i, i + numberOfParagraphsToRemove);
      removeParagraphs.forEach((paragraph) => {
        paragraph.remove();
      });

      i += offset;
    }
  }
}

export function normalizeLegacyMessage(element: HTMLElement): void {
  if (!(element instanceof HTMLTextAreaElement)) {
    throw new TypeError("Expected the element to be a <textarea>.");
  }

  const div = document.createElement("div");
  div.innerHTML = element.value;

  normalizeBr(div);
  const paragraphs = getPossibleSpacerParagraphs(div);
  reduceSpacerParagraphs(paragraphs);

  element.value = div.innerHTML;
}

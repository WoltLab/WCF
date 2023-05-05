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

function unwrapBr(div: HTMLElement): void {
  div.querySelectorAll("br").forEach((br) => {
    if (br.previousSibling || br.nextSibling) {
      return;
    }

    let parent: HTMLElement | null = br;
    while ((parent = parent.parentElement) !== null) {
      switch (parent.tagName) {
        case "B":
        case "EM":
        case "I":
        case "STRONG":
        case "SUB":
        case "SUP":
        case "SPAN":
        case "U":
          if (br.previousSibling || br.nextSibling) {
            return;
          }

          parent.insertAdjacentElement("afterend", br);
          parent.remove();
          parent = br;
          break;

        default:
          return;
      }
    }
  });
}

function removeTrailingBr(div: HTMLElement): void {
  div.querySelectorAll("br").forEach((br) => {
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

    if (paragraphOrTableCell.tagName === "P" && paragraphOrTableCell.innerHTML === "<br>") {
      paragraphOrTableCell.remove();
    } else {
      br.remove();
    }
  });
}

function stripLegacySpacerParagraphs(div: HTMLElement): void {
  div.querySelectorAll("p").forEach((paragraph) => {
    if (paragraph.childElementCount === 1) {
      const child = paragraph.children[0] as HTMLElement;
      if (child.tagName === "BR" && child.dataset.ckeFiller !== "true") {
        if (paragraph.textContent!.trim() === "") {
          paragraph.remove();
        }
      }
    }
  });
}

export function normalizeLegacyMessage(element: HTMLElement): void {
  if (!(element instanceof HTMLTextAreaElement)) {
    throw new TypeError("Expected the element to be a <textarea>.");
  }

  const div = document.createElement("div");
  div.innerHTML = element.value;

  unwrapBr(div);
  removeTrailingBr(div);
  stripLegacySpacerParagraphs(div);

  element.value = div.innerHTML;
}

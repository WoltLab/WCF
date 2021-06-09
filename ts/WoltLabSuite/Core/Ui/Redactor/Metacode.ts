/**
 * Converts `<woltlab-metacode>` into the bbcode representation.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Redactor/Metacode
 * @woltlabExcludeBundle tiny
 */

import * as EventHandler from "../../Event/Handler";
import DomUtil from "../../Dom/Util";
import * as StringUtil from "../../StringUtil";

type Attributes = string[];

/**
 * Returns a text node representing the opening bbcode tag.
 */
function getOpeningTag(name: string, attributes: Attributes): Text {
  let buffer = "[" + name;
  if (attributes.length) {
    buffer += "=";
    buffer += attributes
      .map((attribute) => StringUtil.unescapeHTML(attribute))
      .map((attribute) => `'${attribute}'`)
      .join(",");
  }

  return document.createTextNode(buffer + "]");
}

/**
 * Returns a text node representing the closing bbcode tag.
 */
function getClosingTag(name: string): Text {
  return document.createTextNode(`[/${name}]`);
}

/**
 * Returns the first paragraph of provided element. If there are no children or
 * the first child is not a paragraph, a new paragraph is created and inserted
 * as first child.
 */
function getFirstParagraph(element: HTMLElement): HTMLElement {
  let paragraph: HTMLElement;
  if (element.childElementCount === 0) {
    paragraph = document.createElement("p");
    element.appendChild(paragraph);
  } else {
    const firstChild = element.children[0] as HTMLElement;

    if (firstChild.nodeName === "P") {
      paragraph = firstChild;
    } else {
      paragraph = document.createElement("p");
      element.insertBefore(paragraph, firstChild);
    }
  }

  return paragraph;
}

/**
 * Returns the last paragraph of provided element. If there are no children or
 * the last child is not a paragraph, a new paragraph is created and inserted
 * as last child.
 */
function getLastParagraph(element: HTMLElement): HTMLElement {
  const count = element.childElementCount;

  let paragraph: HTMLElement;
  if (count === 0) {
    paragraph = document.createElement("p");
    element.appendChild(paragraph);
  } else {
    const lastChild = element.children[count - 1] as HTMLElement;

    if (lastChild.nodeName === "P") {
      paragraph = lastChild;
    } else {
      paragraph = document.createElement("p");
      element.appendChild(paragraph);
    }
  }

  return paragraph;
}

/**
 * Parses the attributes string.
 */
function parseAttributes(attributes: string): Attributes {
  try {
    attributes = JSON.parse(atob(attributes));
  } catch (e) {
    /* invalid base64 data or invalid json */
  }

  if (!Array.isArray(attributes)) {
    return [];
  }

  return attributes.map((attribute: string | number) => {
    return attribute.toString().replace(/^'(.*)'$/, "$1");
  });
}

export function convertFromHtml(editorId: string, html: string): string {
  const div = document.createElement("div");
  div.innerHTML = html;

  div.querySelectorAll("woltlab-metacode").forEach((metacode: HTMLElement) => {
    const name = metacode.dataset.name!;
    const attributes = parseAttributes(metacode.dataset.attributes || "");

    const data = {
      attributes: attributes,
      cancel: false,
      metacode: metacode,
    };

    EventHandler.fire("com.woltlab.wcf.redactor2", `metacode_${name}_${editorId}`, data);
    if (data.cancel) {
      return;
    }

    const tagOpen = getOpeningTag(name, attributes);
    const tagClose = getClosingTag(name);

    if (metacode.parentElement === div) {
      const paragraph = getFirstParagraph(metacode);
      paragraph.insertBefore(tagOpen, paragraph.firstChild);
      getLastParagraph(metacode).appendChild(tagClose);
    } else {
      metacode.insertBefore(tagOpen, metacode.firstChild);
      metacode.appendChild(tagClose);
    }

    DomUtil.unwrapChildNodes(metacode);
  });

  // convert `<kbd>…</kbd>` to `[tt]…[/tt]`
  div.querySelectorAll("kbd").forEach((inlineCode) => {
    inlineCode.insertBefore(document.createTextNode("[tt]"), inlineCode.firstChild);
    inlineCode.appendChild(document.createTextNode("[/tt]"));

    DomUtil.unwrapChildNodes(inlineCode);
  });

  return div.innerHTML;
}

/**
 * Handles quotes selection in messages.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 * @woltlabExcludeBundle tiny
 */

import DomUtil from "WoltLabSuite/Core/Dom/Util";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { CKEditor } from "WoltLabSuite/Core/Component/Ckeditor";
import {
  saveQuote,
  getFullQuoteUuid,
  saveFullQuote,
  markQuoteAsUsed,
  isFullQuoted,
  getKey,
  removeQuotes,
  legacySaveQuote,
  legacySaveFullQuote,
} from "WoltLabSuite/Core/Component/Quote/Storage";
import { promiseMutex } from "WoltLabSuite/Core/Helper/PromiseMutex";
import { dispatchToCkeditor } from "WoltLabSuite/Core/Component/Ckeditor/Event";

type Container = {
  element: HTMLElement;
  messageBodySelector: string;
  objectType: string;
  className: string;
  objectId: number;
};

let selectedMessage:
  | undefined
  | {
      message: string;
      container: Container;
    };

type ElementBoundaries = {
  bottom: number;
  left: number;
  right: number;
  top: number;
};

const containers = new Map<string, Container>();
const quoteMessageButtons = new Map<string, HTMLElement>();
let activeMessageId = "";
let activeEditor: CKEditor | undefined = undefined;
let timerSelectionChange: number | undefined = undefined;
let isMouseDown = false;
const copyQuote = document.createElement("div");

export function registerContainer(
  containerSelector: string,
  messageBodySelector: string,
  className: string,
  objectType: string,
): void {
  wheneverFirstSeen(containerSelector, (container: HTMLElement) => {
    const id = DomUtil.identify(container);
    const objectId = ~~container.dataset.objectId!;

    containers.set(id, {
      element: container,
      messageBodySelector,
      objectType,
      className,
      objectId,
    });

    if (container.classList.contains("jsInvalidQuoteTarget")) {
      return;
    }

    container.addEventListener("mousedown", (event) => onMouseDown(event));
    container.classList.add("jsQuoteMessageContainer");

    const quoteMessage = container.querySelector<HTMLElement>(".jsQuoteMessage");
    let quoteMessageButton = quoteMessage?.querySelector<HTMLElement>(".button");
    if (!quoteMessageButton && quoteMessage?.classList.contains("button")) {
      quoteMessageButton = quoteMessage;
    }

    if (quoteMessageButton) {
      quoteMessageButtons.set(getKey(objectType, objectId), quoteMessageButton);

      if (isFullQuoted(objectType, objectId)) {
        quoteMessageButton.classList.add("active");
      }
    }

    quoteMessage?.addEventListener(
      "click",
      promiseMutex(async (event: MouseEvent) => {
        event.preventDefault();

        if (isFullQuoted(objectType, objectId)) {
          removeQuotes([getFullQuoteUuid(objectType, objectId)!]);
          quoteMessageButton!.classList.remove("active");
          return;
        }

        // TODO
        let quoteMessage: any;
        if (className.endsWith("Action")) {
          quoteMessage = await legacySaveFullQuote(objectType, className, ~~container.dataset.objectId!);
        } else {
          quoteMessage = await saveFullQuote(objectType, ~~container.dataset.objectId!);
        }
        quoteMessageButton!.classList.add("active");

        if (activeEditor !== undefined) {
          dispatchToCkeditor(activeEditor.sourceElement).insertQuote({
            author: quoteMessage.author,
            content: quoteMessage.rawMessage === undefined ? quoteMessage.message : quoteMessage.rawMessage,
            isText: quoteMessage.rawMessage === undefined,
            link: quoteMessage.link,
          });

          markQuoteAsUsed(activeEditor.sourceElement.id, quoteMessage.uuid);
        }
      }),
    );
  });
}

export function setActiveEditor(editor?: CKEditor, supportDirectInsert: boolean = false) {
  copyQuote.querySelector<HTMLButtonElement>(".jsQuoteManagerQuoteAndInsert")!.hidden = !supportDirectInsert;

  activeEditor = editor;
}

export function removeActiveEditor(editorSource: HTMLElement): void {
  if (!activeEditor) {
    return;
  }

  if (activeEditor.sourceElement === editorSource) {
    setActiveEditor();
  }
}

export function removeQuoteStatus(key: string): void {
  quoteMessageButtons.get(key)?.classList.remove("active");
}

function setup() {
  copyQuote.classList.add("balloonTooltip", "interactive", "quoteManagerCopy");

  const buttonSaveQuote = document.createElement("button");
  buttonSaveQuote.type = "button";
  buttonSaveQuote.classList.add("jsQuoteManagerStore");
  buttonSaveQuote.textContent = getPhrase("wcf.message.quote.quoteSelected");
  buttonSaveQuote.addEventListener(
    "click",
    promiseMutex(async () => {
      if (selectedMessage!.container.className.endsWith("Action")) {
        await legacySaveQuote(
          selectedMessage!.container.objectType,
          selectedMessage!.container.objectId,
          selectedMessage!.container.className,
          selectedMessage!.message,
        );
      } else {
        await saveQuote(
          selectedMessage!.container.objectType,
          selectedMessage!.container.objectId,
          selectedMessage!.message,
        );
      }

      removeSelection();
    }),
  );
  copyQuote.appendChild(buttonSaveQuote);
  const buttonSaveAndInsertQuote = document.createElement("button");
  buttonSaveAndInsertQuote.type = "button";
  buttonSaveAndInsertQuote.hidden = true;
  buttonSaveAndInsertQuote.classList.add("jsQuoteManagerQuoteAndInsert");
  buttonSaveAndInsertQuote.textContent = getPhrase("wcf.message.quote.quoteAndReply");
  buttonSaveAndInsertQuote.addEventListener(
    "click",
    promiseMutex(async () => {
      // TODO
      let quoteMessage: any;
      if (selectedMessage!.container.className.endsWith("Action")) {
        quoteMessage = await legacySaveQuote(
          selectedMessage!.container.objectType,
          selectedMessage!.container.objectId,
          selectedMessage!.container.className,
          selectedMessage!.message,
        );
      } else {
        quoteMessage = await saveQuote(
          selectedMessage!.container.objectType,
          selectedMessage!.container.objectId,
          selectedMessage!.message,
        );
      }

      if (activeEditor !== undefined) {
        dispatchToCkeditor(activeEditor.sourceElement).insertQuote({
          author: quoteMessage.author,
          content: quoteMessage.rawMessage ? quoteMessage.rawMessage : quoteMessage.message,
          isText: !quoteMessage.rawMessage,
          link: quoteMessage.link,
        });

        markQuoteAsUsed(activeEditor.sourceElement.id, quoteMessage.uuid);
      }

      removeSelection();
    }),
  );
  copyQuote.appendChild(buttonSaveAndInsertQuote);

  document.body.appendChild(copyQuote);

  document.addEventListener("mouseup", (event) => onMouseUp(event));
  document.addEventListener("selectionchange", () => onSelectionchange());

  // Prevent the tooltip from being selectable while the touch pointer is being moved.
  document.addEventListener(
    "touchstart",
    (event) => {
      const target = event.target as HTMLElement;
      if (target !== copyQuote && !copyQuote.contains(target)) {
        copyQuote.classList.add("touchForceInaccessible");

        document.addEventListener(
          "touchend",
          () => {
            copyQuote.classList.remove("touchForceInaccessible");
          },
          { once: true, passive: false },
        );
      }
    },
    { passive: false },
  );

  window.addEventListener(
    "resize",
    () => {
      copyQuote.classList.remove("active");
    },
    { passive: true },
  );
}

setup();

function getSelectedText(): string {
  const selection = window.getSelection()!;
  if (selection.rangeCount) {
    return getNodeText(selection.getRangeAt(0).cloneContents());
  }

  return "";
}

/**
 * Returns the text of a node and its children.
 */
function getNodeText(node: Node): string {
  const treeWalker = document.createTreeWalker(node, NodeFilter.SHOW_ELEMENT | NodeFilter.SHOW_TEXT, {
    acceptNode(node: Node): number {
      if (node.nodeName === "BLOCKQUOTE" || node.nodeName === "SCRIPT") {
        return NodeFilter.FILTER_REJECT;
      }

      if (node instanceof HTMLImageElement) {
        // Skip any image that is not a smiley or contains no alt text.
        if (!node.classList.contains("smiley") || !node.alt) {
          return NodeFilter.FILTER_REJECT;
        }
      }

      return NodeFilter.FILTER_ACCEPT;
    },
  });

  let text = "";
  const ignoreLinks: HTMLAnchorElement[] = [];
  while (treeWalker.nextNode()) {
    const node = treeWalker.currentNode as HTMLElement | Text;

    if (node instanceof Text) {
      const parent = node.parentElement!;
      if (parent instanceof HTMLAnchorElement && ignoreLinks.includes(parent)) {
        // ignore text content of links that have already been captured
        continue;
      }

      // Firefox loves to arbitrarily wrap pasted text at weird line lengths, causing
      // pointless linebreaks to be inserted. Replacing them with a simple space will
      // preserve the spacing between words that would otherwise be lost.
      text += node.nodeValue!.replace(/\n/g, " ");

      continue;
    }

    if (node instanceof HTMLAnchorElement) {
      // \u2026 === &hellip;
      const value = node.textContent!;
      if (value.indexOf("\u2026") > 0) {
        const tmp = value.split(/\u2026/);
        if (tmp.length === 2) {
          const href = node.href;
          if (href.indexOf(tmp[0]) === 0 && href.substring(tmp[1].length * -1) === tmp[1]) {
            // This is a truncated url, use the original href instead to preserve the link.
            text += href;
            ignoreLinks.push(node);
          }
        }
      }
    }

    switch (node.nodeName) {
      case "BR":
      case "LI":
      case "TD":
      case "UL":
        text += "\n";
        break;

      case "P":
        text += "\n\n";
        break;

      // smilies
      case "IMG": {
        const img = node as HTMLImageElement;
        text += ` ${img.alt} `;
        break;
      }

      // Code listing
      case "DIV":
        if (node.classList.contains("codeBoxHeadline") || node.classList.contains("codeBoxLine")) {
          text += "\n";
        }
        break;
    }
  }

  return text;
}

function normalizeTextForComparison(text: string): string {
  return text
    .replace(/\r?\n|\r/g, "\n")
    .replace(/\s/g, " ")
    .replace(/\s{2,}/g, " ");
}

function onSelectionchange(): void {
  if (isMouseDown) {
    return;
  }

  if (activeMessageId === "") {
    // check if the selection is non-empty and is entirely contained
    // inside a single message container that is registered for quoting
    const selection = window.getSelection()!;
    if (selection.rangeCount !== 1 || selection.isCollapsed) {
      return;
    }

    const range = selection.getRangeAt(0);
    const startContainer = DomUtil.closest(range.startContainer, ".jsQuoteMessageContainer");
    const endContainer = DomUtil.closest(range.endContainer, ".jsQuoteMessageContainer");
    if (
      startContainer &&
      startContainer === endContainer &&
      !startContainer.classList.contains("jsInvalidQuoteTarget")
    ) {
      // Check if the selection is visible, such as text marked inside containers with an
      // active overflow handling attached to it. This can be a side effect of the browser
      // search which modifies the text selection, but cannot be distinguished from manual
      // selections initiated by the user.
      let commonAncestor = range.commonAncestorContainer as HTMLElement;
      if (commonAncestor.nodeType !== Node.ELEMENT_NODE) {
        commonAncestor = commonAncestor.parentElement!;
      }

      const offsetParent = commonAncestor.offsetParent!;
      if (startContainer.contains(offsetParent)) {
        if (offsetParent.scrollTop + offsetParent.clientHeight < commonAncestor.offsetTop) {
          // The selected text is not visible to the user.
          return;
        }
      }

      activeMessageId = startContainer.id;
    }
  }

  if (timerSelectionChange) {
    window.clearTimeout(timerSelectionChange);
  }

  timerSelectionChange = window.setTimeout(() => onMouseUp(), 100);
}

function onMouseDown(event: MouseEvent): void {
  // hide copy quote
  copyQuote.classList.remove("active");

  const message = event.currentTarget as HTMLElement;
  activeMessageId = message.classList.contains("jsInvalidQuoteTarget") ? "" : message.id;

  if (timerSelectionChange) {
    window.clearTimeout(timerSelectionChange);
    timerSelectionChange = undefined;
  }

  isMouseDown = true;
}

function onMouseUp(event?: MouseEvent): void {
  if (event instanceof Event) {
    if (timerSelectionChange) {
      // Prevent collisions of the `selectionchange` and the `mouseup` event.
      window.clearTimeout(timerSelectionChange);
      timerSelectionChange = undefined;
    }

    isMouseDown = false;
  }

  // ignore event
  if (activeMessageId === "") {
    copyQuote.classList.remove("active");

    return;
  }

  const selection = window.getSelection()!;
  if (selection.rangeCount !== 1 || selection.isCollapsed) {
    copyQuote.classList.remove("active");

    return;
  }

  const container = containers.get(activeMessageId);
  if (container === undefined) {
    // Since 5.4 we listen for global mouse events, because those are much
    // more reliable on mobile devices. However, this can cause conflicts
    // if two or more types of message types with quote support coexist on
    // the same page.
    return;
  }

  const content = container.messageBodySelector
    ? (container.element.querySelector(container.messageBodySelector) as HTMLElement)
    : container;

  let anchorNode = selection.anchorNode;
  while (anchorNode) {
    if (anchorNode === content) {
      break;
    }

    anchorNode = anchorNode.parentNode;
  }

  // selection spans unrelated nodes
  if (anchorNode !== content) {
    copyQuote.classList.remove("active");

    return;
  }

  const selectedText = getSelectedText();
  const text = selectedText.trim();
  if (text === "") {
    copyQuote.classList.remove("active");

    return;
  }

  // check if mousedown/mouseup took place inside a blockquote
  const range = selection.getRangeAt(0);
  const startContainer = DomUtil.getClosestElement(range.startContainer);
  const endContainer = DomUtil.getClosestElement(range.endContainer);
  if (startContainer.closest("blockquote") || endContainer.closest("blockquote")) {
    copyQuote.classList.remove("active");

    return;
  }

  // compare selection with message text of given container
  const messageText = getNodeText(content);

  // selected text is not part of $messageText or contains text from unrelated nodes
  if (!normalizeTextForComparison(messageText).includes(normalizeTextForComparison(text))) {
    return;
  }

  copyQuote.classList.add("active");
  const wasInaccessible = copyQuote.classList.contains("touchForceInaccessible");
  if (wasInaccessible) {
    copyQuote.classList.remove("touchForceInaccessible");
  }

  alignQuoteButtons(content);

  copyQuote.classList.remove("active");
  if (wasInaccessible) {
    copyQuote.classList.add("touchForceInaccessible");
  }

  if (!timerSelectionChange) {
    // reset containerID
    activeMessageId = "";
  } else {
    window.clearTimeout(timerSelectionChange);
    timerSelectionChange = undefined;
  }

  // show element after a delay, to prevent display if text was unmarked again (clicking into marked text)
  window.setTimeout(() => {
    const text = getSelectedText().trim();
    if (text !== "") {
      copyQuote.classList.add("active");
      selectedMessage = {
        message: text,
        container: container,
      };
    }
  }, 10);
}

function removeSelection(): void {
  copyQuote.classList.remove("active");

  const selection = window.getSelection()!;
  if (selection.rangeCount) {
    selection.removeAllRanges();
  }
}

function alignQuoteButtons(content: HTMLElement): void {
  const coordinates = getElementBoundaries(window.getSelection());
  const dimensions = { height: copyQuote.offsetHeight, width: copyQuote.offsetWidth };
  let left = (coordinates.right - coordinates.left) / 2 - dimensions.width / 2 + coordinates.left;

  // Prevent the overlay from overflowing the left or right boundary of the container.
  const containerBoundaries = content.getBoundingClientRect();
  if (left < containerBoundaries.left) {
    left = containerBoundaries.left;
  } else if (left + dimensions.width > containerBoundaries.right) {
    left = containerBoundaries.right - dimensions.width;
  }

  copyQuote.style.setProperty("top", `${coordinates.bottom + 7}px`);
  copyQuote.style.setProperty("left", `${left}px`);
}

function getElementBoundaries(selection: Selection | null): ElementBoundaries {
  if (!selection) {
    throw new Error("Nothing is selected");
  }

  if (selection.rangeCount <= 0) {
    throw new Error("Selection has no range");
  }

  // The coordinates returned by getBoundingClientRect() are relative to the
  // viewport, not the document.
  const rect = selection.getRangeAt(0).getBoundingClientRect();

  const scrollTop = window.scrollY;
  return {
    bottom: rect.bottom + scrollTop,
    left: rect.left,
    right: rect.right,
    top: rect.top + scrollTop,
  };
}

import { ModalDialog } from "./Dialog/modal-dialog";

export function dialogFromElement(element: HTMLElement | DocumentFragment): ModalDialog {
  if (!(element instanceof HTMLElement) && !(element instanceof DocumentFragment)) {
    throw new TypeError("Expected an HTML element or a document fragment.");
  }

  const dialog = document.createElement("modal-dialog");
  dialog.content.append(element);

  return dialog;
}

export function dialogFromId(id: string): ModalDialog {
  const element = document.getElementById(id);
  if (element === null) {
    throw new Error(`Unable to find the element identified by '${id}'.`);
  }

  return dialogFromElement(element);
}

export function dialogFromHtml(html: string): ModalDialog {
  const element = document.createElement("div");
  element.innerHTML = html;
  if (element.childElementCount === 0) {
    throw new TypeError("The provided HTML string did not contain any elements.");
  }

  const fragment = document.createDocumentFragment();
  fragment.append(...element.childNodes);

  return dialogFromElement(fragment);
}

export * from "./Dialog/modal-dialog";

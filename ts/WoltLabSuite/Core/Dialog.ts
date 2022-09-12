import { ModalDialog } from "./Dialog/modal-dialog";

export function dialogFromElement(element: HTMLElement): ModalDialog {
  if (!(element instanceof HTMLElement) || element.nodeName !== "DIV") {
    throw new TypeError("Only '<div>' elements are allowed as the content element.");
  }

  const dialog = document.createElement("modal-dialog");
  dialog.content = element;

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

  return dialogFromElement(element);
}

export * from "./Dialog/modal-dialog";

import DomUtil from "../Dom/Util";

type CallbackReturnFocus = () => HTMLElement | null;

const dialogContainer = document.createElement("div");

export class ModalDialog extends HTMLElement {
  #content?: HTMLElement = undefined;
  readonly #dialog: HTMLDialogElement;
  #returnFocus?: CallbackReturnFocus = undefined;
  readonly #title: HTMLElement;

  constructor() {
    super();

    this.#dialog = document.createElement("dialog");
    this.#title = document.createElement("div");
  }

  connectedCallback(): void {
    this.#attachDialog();
  }

  show(): void {
    if (this.#title.textContent!.trim().length === 0) {
      throw new Error("Cannot open the modal dialog without a title.");
    }

    if (this.#dialog.parentElement === null) {
      if (dialogContainer.parentElement === null) {
        document.getElementById("content")!.append(dialogContainer);
      }

      dialogContainer.append(this);
    }

    this.#dialog.showModal();
  }

  close(): void {
    this.#dialog.close();

    if (this.#returnFocus !== undefined) {
      const element = this.#returnFocus();
      element?.focus();
    }
  }

  get dialog(): HTMLDialogElement {
    return this.#dialog;
  }

  get content(): HTMLElement {
    if (this.#content === undefined) {
      this.#content = document.createElement("div");
    }

    return this.#content;
  }

  set content(element: HTMLElement) {
    if (this.#content !== undefined) {
      throw new Error("There is already a content element for this dialog.");
    }

    if (!(element instanceof HTMLElement) || element.nodeName !== "DIV") {
      throw new TypeError("Only '<div>' elements are allowed as the content element.");
    }

    this.#content = element;
  }

  set title(title: string) {
    this.#title.textContent = title;
  }

  set returnFocus(returnFocus: CallbackReturnFocus) {
    if (typeof returnFocus !== "function") {
      throw new TypeError("Expected a callback function for the return focus.");
    }

    this.#returnFocus = returnFocus;
  }

  get open(): boolean {
    return this.#dialog.open;
  }

  get closable(): boolean {
    return this.hasAttribute("closable");
  }

  set closable(closable: boolean) {
    if (closable) {
      this.setAttribute("closable", "");
    } else {
      this.removeAttribute("closable");
    }
  }

  #attachDialog(): void {
    if (this.#dialog.parentElement !== null) {
      return;
    }

    const closeButton = document.createElement("button");
    closeButton.innerHTML = '<fa-icon name="xmark"></fa-icon>';
    closeButton.addEventListener("click", () => {
      this.close();
    });

    const header = document.createElement("div");
    header.append(this.#title, closeButton);

    const doc = document.createElement("div");
    doc.setAttribute("role", "document");
    doc.append(header, this.content);

    this.#dialog.append(doc);
    this.#dialog.setAttribute("aria-labelledby", DomUtil.identify(this.#title));

    this.#dialog.addEventListener("cancel", (event) => {
      if (!this.closable) {
        event.preventDefault();
        return;
      }
    });

    // Close the dialog by clicking on the backdrop.
    this.#dialog.addEventListener("click", (event) => {
      if (event.target === this.#dialog) {
        this.close();
      }
    });

    this.append(this.#dialog);
  }
}

window.customElements.define("modal-dialog", ModalDialog);

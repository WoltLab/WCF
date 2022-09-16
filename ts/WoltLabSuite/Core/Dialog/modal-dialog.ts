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

    const event = new CustomEvent("closed");
    this.dispatchEvent(event);
  }

  get dialog(): HTMLDialogElement {
    return this.#dialog;
  }

  get content(): HTMLElement {
    if (this.#content === undefined) {
      this.#content = document.createElement("div");
      this.#content.classList.add("dialog__content");
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
    this.#content.classList.add("dialog__content");
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

  #attachDialog(): void {
    if (this.#dialog.parentElement !== null) {
      return;
    }

    const closeButton = document.createElement("button");
    closeButton.innerHTML = '<fa-icon size="24" name="xmark"></fa-icon>';
    closeButton.classList.add("dialog__closeButton");
    closeButton.addEventListener("click", () => {
      this.close();
    });

    const header = document.createElement("div");
    header.classList.add("dialog__header");
    this.#title.classList.add("dialog__title");
    header.append(this.#title, closeButton);

    const doc = document.createElement("div");
    doc.classList.add("dialog__document");
    doc.setAttribute("role", "document");
    doc.append(header, this.content);

    this.#dialog.append(doc);
    this.#dialog.classList.add("dialog");
    this.#dialog.setAttribute("aria-labelledby", DomUtil.identify(this.#title));

    this.#dialog.addEventListener("cancel", (event) => {
      if (!this.#shouldClose()) {
        event.preventDefault();
        return;
      }
    });

    // Close the dialog by clicking on the backdrop.
    //
    // Using the `close` event is not an option because it will
    // also trigger when holding the mouse button inside the
    // dialog and then releasing it on the backdrop.
    this.#dialog.addEventListener("mousedown", (event) => {
      if (event.target === this.#dialog) {
        if (this.#shouldClose()) {
          this.close();
        }
      }
    });

    this.append(this.#dialog);
  }

  #shouldClose(): boolean {
    const event = new CustomEvent("close");
    this.dispatchEvent(event);

    return event.defaultPrevented === false;
  }
}

window.customElements.define("modal-dialog", ModalDialog);

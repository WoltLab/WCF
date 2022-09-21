import DomUtil from "../Dom/Util";

type CallbackReturnFocus = () => HTMLElement | null;

interface WoltlabCoreDialogEventMap {
  afterClose: CustomEvent;
  cancel: CustomEvent;
  close: CustomEvent;
  primary: CustomEvent;
  validate: CustomEvent;
}

const dialogContainer = document.createElement("div");

export type WoltlabCoreDialogFormControl = {
  cancel: string | undefined;
  extra: string | undefined;
  isAlert: boolean;
  primary: string;
};

export class WoltlabCoreDialogElement extends HTMLElement {
  readonly #content: HTMLElement;
  readonly #dialog: HTMLDialogElement;
  #form?: HTMLFormElement;
  #returnFocus?: CallbackReturnFocus;
  readonly #title: HTMLElement;

  constructor() {
    super();

    this.#content = document.createElement("div");
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

    const event = new CustomEvent("afterClose");
    this.dispatchEvent(event);
  }

  get dialog(): HTMLDialogElement {
    return this.#dialog;
  }

  get content(): HTMLElement {
    return this.#content;
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

  attachFormControls(options: WoltlabCoreDialogFormControl): void {
    if (this.#form !== undefined) {
      throw new Error("There is already a form control attached to this dialog.");
    }

    if (options.extra !== undefined && options.cancel === undefined) {
      options.cancel = "";
    }

    const formControl = document.createElement("form-control");
    formControl.primary = options.primary;

    if (options.cancel !== undefined) {
      formControl.cancel = options.cancel;
    }

    this.#form = document.createElement("form");
    this.#form.method = "dialog";
    this.#form.classList.add("dialog__form");
    this.#content.insertAdjacentElement("beforebegin", this.#form);

    this.#form.append(this.#content, formControl);

    if (options.isAlert) {
      if (options.cancel === undefined) {
        this.#dialog.setAttribute("role", "alert");
      } else {
        this.#dialog.setAttribute("role", "alertdialog");
      }
    }

    this.#form.addEventListener("submit", (event) => {
      const evt = new CustomEvent("validate", { cancelable: true });
      this.dispatchEvent(evt);

      if (evt.defaultPrevented) {
        event.preventDefault();
      }
    });

    this.#dialog.addEventListener("close", () => {
      if (this.#dialog.returnValue === "") {
        // Dialog was not closed by submitting it.
        return;
      }

      const evt = new CustomEvent("primary");
      this.dispatchEvent(evt);
    });

    formControl.addEventListener("cancel", () => {
      const event = new CustomEvent("cancel", { cancelable: true });
      this.dispatchEvent(event);

      if (!event.defaultPrevented) {
        this.close();
      }
    });
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
    doc.append(header);

    this.#content.classList.add("dialog__content");
    if (this.#form) {
      doc.append(this.#form);
    } else {
      doc.append(this.#content);
    }

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

  public addEventListener<T extends keyof WoltlabCoreDialogEventMap>(
    type: T,
    listener: (this: WoltlabCoreDialogElement, ev: WoltlabCoreDialogEventMap[T]) => any,
    options?: boolean | AddEventListenerOptions,
  ): void;
  public addEventListener(
    type: string,
    listener: (this: WoltlabCoreDialogElement, ev: Event) => any,
    options?: boolean | AddEventListenerOptions,
  ): void {
    super.addEventListener(type, listener, options);
  }
}

window.customElements.define("woltlab-core-dialog", WoltlabCoreDialogElement);

/**
 * The web component `<woltlab-core-dialog>` represents a
 * modal dialog with a unified event access for consistent
 * interactions. This is the low-level API of dialogs, you
 * should use the `dialogFactory()` to create them.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import DomUtil from "../Dom/Util";
import { adoptPageOverlayContainer, releasePageOverlayContainer } from "../Helper/PageOverlay";
import * as Language from "../Language";
import { scrollDisable, scrollEnable } from "../Ui/Screen";

type ValidateCallback = Promise<boolean>;

interface WoltlabCoreDialogEventMap {
  afterClose: CustomEvent;
  backdrop: CustomEvent;
  cancel: CustomEvent;
  close: CustomEvent;
  extra: CustomEvent;
  primary: CustomEvent;
  validate: CustomEvent<ValidateCallback[]>;
}

const dialogContainer = document.createElement("div");

export type WoltlabCoreDialogControlOptions = {
  cancel: string | undefined;
  extra: string | undefined;
  isAlert: boolean;
  primary: string;
};

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export class WoltlabCoreDialogElement extends HTMLElement {
  readonly #content: HTMLElement;
  readonly #dialog: HTMLDialogElement;
  #form?: HTMLFormElement;
  readonly #title: HTMLElement;

  constructor() {
    super();

    this.#content = document.createElement("div");
    this.#dialog = document.createElement("dialog");
    this.#title = document.createElement("div");
  }

  show(title: string): void {
    if (title.trim().length === 0) {
      throw new Error("Cannot open the modal dialog without a title.");
    }

    this.#title.textContent = title;

    if (this.open) {
      return;
    }

    if (dialogContainer.parentElement === null) {
      document.getElementById("content")!.append(dialogContainer);
    }

    if (this.parentElement !== dialogContainer) {
      dialogContainer.append(this);
    }

    this.#dialog.showModal();

    adoptPageOverlayContainer(this.#dialog);
    scrollDisable();
  }

  close(): void {
    this.#dialog.close();

    this.#detachDialog();
  }

  #detachDialog(): void {
    const event = new CustomEvent("afterClose");
    this.dispatchEvent(event);

    releasePageOverlayContainer(this.#dialog);
    scrollEnable();

    // Remove the dialog from the DOM, preventing it from
    // causing any collisions caused by elements with IDs
    // contained inside it. Will also cause the DOM element
    // to be garbage collected when there are no more
    // references to it.
    this.remove();
  }

  get dialog(): HTMLDialogElement {
    return this.#dialog;
  }

  get content(): HTMLElement {
    return this.#content;
  }

  get open(): boolean {
    return this.#dialog.open;
  }

  get incomplete(): boolean {
    return this.hasAttribute("incomplete");
  }

  set incomplete(incomplete: boolean) {
    if (incomplete) {
      this.setAttribute("incomplete", "");
    } else {
      this.removeAttribute("incomplete");
    }
  }

  attachControls(options: WoltlabCoreDialogControlOptions): void {
    if (this.#form !== undefined) {
      throw new Error("There is already a form control attached to this dialog.");
    }

    if (options.extra !== undefined && options.cancel === undefined) {
      options.cancel = "";
    }

    const formControl = document.createElement("woltlab-core-dialog-control");
    formControl.primary = options.primary;

    if (options.cancel !== undefined) {
      formControl.cancel = options.cancel;
    }

    if (options.extra !== undefined) {
      formControl.extra = options.extra;
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
      if (this.incomplete) {
        event.preventDefault();
        return;
      }

      const callbacks: ValidateCallback[] = [];
      const evt = new CustomEvent("validate", {
        cancelable: true,
        detail: callbacks,
      });
      this.dispatchEvent(evt);

      if (evt.defaultPrevented) {
        event.preventDefault();
      }

      if (evt.detail.length > 0) {
        // DOM events cannot wait for async functions. We must
        // reject the event and then wait for the async
        // callbacks to complete.
        event.preventDefault();

        // Blocking further attempts to submit the dialog
        // while the validation is running.
        this.incomplete = true;

        void Promise.all(evt.detail).then((results) => {
          this.incomplete = false;

          const failedValidation = results.some((result) => result === false);
          if (!failedValidation) {
            // The `primary` event is triggered once the validation
            // has completed. Triggering the submit again would cause
            // `validate` to run again, causing an infinite loop.
            this.#dispatchPrimaryEvent();

            this.close();
          }
        });
      }
    });

    this.#dialog.addEventListener("close", () => {
      if (this.#dialog.returnValue === "") {
        // Dialog was programmatically closed.
        return;
      }

      this.#dispatchPrimaryEvent();

      this.#detachDialog();
    });

    formControl.addEventListener("cancel", () => {
      const event = new CustomEvent("cancel", { cancelable: true });
      this.dispatchEvent(event);

      if (!event.defaultPrevented) {
        this.close();
      }
    });

    if (options.extra !== undefined) {
      formControl.addEventListener("extra", () => {
        const event = new CustomEvent("extra");
        this.dispatchEvent(event);
      });
    }
  }

  #dispatchPrimaryEvent(): void {
    const evt = new CustomEvent("primary");
    this.dispatchEvent(evt);
  }

  connectedCallback(): void {
    if (this.#dialog.parentElement !== null) {
      return;
    }

    let closeButton: HTMLButtonElement | undefined;
    const dialogRole = this.#dialog.getAttribute("role");
    if (dialogRole !== "alert" && dialogRole !== "alertdialog") {
      closeButton = document.createElement("button");
      closeButton.innerHTML = '<fa-icon size="24" name="xmark"></fa-icon>';
      closeButton.classList.add("dialog__closeButton", "jsTooltip");
      closeButton.title = Language.get("wcf.dialog.button.close");
      closeButton.addEventListener("click", () => {
        this.close();
      });
    }

    const header = document.createElement("div");
    header.classList.add("dialog__header");
    this.#title.classList.add("dialog__title");
    header.append(this.#title);
    if (closeButton) {
      header.append(closeButton);
    }

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

    this.#dialog.addEventListener("cancel", () => {
      const event = new CustomEvent("cancel");
      this.dispatchEvent(event);

      this.#detachDialog();
    });

    // Close the dialog by clicking on the backdrop.
    //
    // Using the `close` event is not an option because it will
    // also trigger when holding the mouse button inside the
    // dialog and then releasing it on the backdrop.
    this.#dialog.addEventListener("mousedown", (event) => {
      if (event.target === this.#dialog) {
        const event = new CustomEvent("backdrop", { cancelable: true });
        this.dispatchEvent(event);
        if (event.defaultPrevented) {
          return;
        }

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

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export interface WoltlabCoreDialogElement extends HTMLElement {
  addEventListener: {
    <T extends keyof WoltlabCoreDialogEventMap>(
      type: T,
      listener: (this: WoltlabCoreDialogElement, ev: WoltlabCoreDialogEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
  } & HTMLElement["addEventListener"];
}

window.customElements.define("woltlab-core-dialog", WoltlabCoreDialogElement);

export default WoltlabCoreDialogElement;

/**
 * The web component `<woltlab-core-dialog-control>` adds
 * buttons to a dialog to allow for a consistent interaction
 * with dialogs in generals and dialogs containing forms in
 * particular. This is the low-level API, the controls are
 * automatically added through the `dialogFactory()`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */

import * as Language from "../Language";

interface WoltlabCoreDialogControlEventMap {
  cancel: CustomEvent;
  extra: CustomEvent;
}

export class WoltlabCoreDialogControlElement extends HTMLElement {
  #cancelButton?: HTMLButtonElement;
  #extraButton?: HTMLButtonElement;
  #primaryButton?: HTMLButtonElement;

  set primary(primary: string) {
    this.setAttribute("primary", primary);
  }

  get primary(): string {
    let label = this.getAttribute("primary")!;
    if (!label) {
      label = Language.get("wcf.dialog.button.primary");
    }

    return label;
  }

  set cancel(cancel: string | undefined) {
    if (cancel === undefined) {
      this.removeAttribute("cancel");
    } else {
      this.setAttribute("cancel", cancel);
    }
  }

  get cancel(): string | undefined {
    let label = this.getAttribute("cancel");
    if (label === null) {
      return undefined;
    }

    if (label === "") {
      label = Language.get("wcf.global.confirmation.cancel");
    }

    return label;
  }

  set extra(extra: string | undefined) {
    if (extra === undefined) {
      this.removeAttribute("extra");
    } else {
      this.setAttribute("extra", extra);
    }
  }

  get extra(): string | undefined {
    const label = this.getAttribute("extra");
    if (label === null) {
      return undefined;
    }

    return label;
  }

  connectedCallback() {
    const dialog = this.closest("woltlab-core-dialog")!;

    this.classList.add("dialog__control");

    if (!this.hasAttribute("primary")) {
      this.setAttribute("primary", "");
    }

    if (this.#primaryButton === undefined) {
      this.#primaryButton = document.createElement("button");
      this.#primaryButton.type = "submit";
      this.#primaryButton.value = "primary";
      this.#primaryButton.autofocus = true;
      this.#primaryButton.classList.add(
        "button",
        "buttonPrimary",
        "dialog__control__button",
        "dialog__control__button--primary",
      );
      this.#primaryButton.textContent = this.primary;

      this.append(this.#primaryButton);

      const observer = new MutationObserver(() => {
        this.#primaryButton!.disabled = dialog.incomplete;
      });
      observer.observe(dialog, {
        attributeFilter: ["incomplete"],
      });

      if (dialog.incomplete) {
        this.#primaryButton.disabled = true;
      }
    }

    if (this.#cancelButton === undefined && this.cancel !== undefined) {
      this.#cancelButton = document.createElement("button");
      this.#cancelButton.type = "button";
      this.#cancelButton.value = "cancel";
      this.#cancelButton.classList.add("button", "dialog__control__button", "dialog__control__button--cancel");
      this.#cancelButton.textContent = this.cancel;
      this.#cancelButton.addEventListener("click", () => {
        const event = new CustomEvent("cancel");
        this.dispatchEvent(event);
      });

      this.append(this.#cancelButton);

      dialog.addEventListener("backdrop", (event) => {
        event.preventDefault();

        this.#cancelButton!.click();
      });
    }

    if (this.#extraButton === undefined && this.extra !== undefined) {
      this.#extraButton = document.createElement("button");
      this.#extraButton.type = "button";
      this.#extraButton.value = "extra";
      this.#extraButton.classList.add("button", "dialog__control__button", "dialog__control__button--extra");
      this.#extraButton.textContent = this.extra;
      this.#extraButton.addEventListener("click", () => {
        const event = new CustomEvent("extra");
        this.dispatchEvent(event);
      });

      this.append(this.#extraButton);
    }
  }

  public addEventListener<T extends keyof WoltlabCoreDialogControlEventMap>(
    type: T,
    listener: (this: WoltlabCoreDialogControlElement, ev: WoltlabCoreDialogControlEventMap[T]) => any,
    options?: boolean | AddEventListenerOptions,
  ): void;
  public addEventListener(
    type: string,
    listener: (this: WoltlabCoreDialogControlElement, ev: Event) => any,
    options?: boolean | AddEventListenerOptions,
  ): void {
    super.addEventListener(type, listener, options);
  }
}

export function setup(): void {
  const name = "woltlab-core-dialog-control";
  if (window.customElements.get(name) === undefined) {
    window.customElements.define(name, WoltlabCoreDialogControlElement);
  }
}

export default WoltlabCoreDialogControlElement;

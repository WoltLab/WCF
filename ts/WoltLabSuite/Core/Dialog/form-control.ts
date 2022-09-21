import * as Language from "../Language";

interface WoltlabCoreDialogControlEventMap {
  cancel: CustomEvent;
}

export class WoltlabCoreDialogControl extends HTMLElement {
  #cancelButton?: HTMLButtonElement;
  #primaryButton?: HTMLButtonElement;

  set primary(primary: string) {
    this.setAttribute("primary", primary);
  }

  get primary(): string {
    let label = this.getAttribute("default")!;
    if (!label) {
      label = Language.get("wcf.global.confirmation.confirm");
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

  connectedCallback() {
    this.classList.add("formControl");

    if (!this.hasAttribute("default")) {
      this.setAttribute("default", "");
    }

    if (this.#primaryButton === undefined) {
      this.#primaryButton = document.createElement("button");
      this.#primaryButton.type = "submit";
      this.#primaryButton.value = "primary";
      this.#primaryButton.autofocus = true;
      this.#primaryButton.classList.add(
        "button",
        "buttonPrimary",
        "formControl__button",
        "formControl__button--primary",
      );
      this.#primaryButton.textContent = this.primary;

      this.append(this.#primaryButton);
    }

    if (this.#cancelButton === undefined && this.cancel !== undefined) {
      this.#cancelButton = document.createElement("button");
      this.#cancelButton.type = "button";
      this.#cancelButton.value = "cancel";
      this.#cancelButton.classList.add("button", "formControl__button", "formControl__button--cancel");
      this.#cancelButton.textContent = this.cancel;
      this.#cancelButton.addEventListener("click", () => {
        const event = new CustomEvent("cancel");
        this.dispatchEvent(event);
      });

      this.append(this.#cancelButton);
    }
  }

  public addEventListener<T extends keyof WoltlabCoreDialogControlEventMap>(
    type: T,
    listener: (this: WoltlabCoreDialogControl, ev: WoltlabCoreDialogControlEventMap[T]) => any,
    options?: boolean | AddEventListenerOptions,
  ): void;
  public addEventListener(
    type: string,
    listener: (this: WoltlabCoreDialogControl, ev: Event) => any,
    options?: boolean | AddEventListenerOptions,
  ): void {
    super.addEventListener(type, listener, options);
  }
}

export function setup(): void {
  const name = "woltlab-core-dialog-control";
  if (window.customElements.get(name) === undefined) {
    window.customElements.define(name, WoltlabCoreDialogControl);
  }
}

export default WoltlabCoreDialogControl;

import * as Language from "../Language";

export class FormControl extends HTMLElement {
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

  connectedCallback() {
    this.classList.add("formControl");

    if (!this.hasAttribute("default")) {
      this.setAttribute("default", "");
    }

    if (this.#primaryButton === undefined) {
      this.#primaryButton = document.createElement("button");
      this.#primaryButton.type = "button";
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
  }
}

export function setup(): void {
  const name = "form-control";
  if (window.customElements.get(name) === undefined) {
    window.customElements.define(name, FormControl);
  }
}

export default FormControl;

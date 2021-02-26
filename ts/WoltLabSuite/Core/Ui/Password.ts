import DomChangeListener from "../Dom/Change/Listener";
import * as Language from "../Language";

const _knownElements = new WeakSet();

export function setup(): void {
  initElements();
  DomChangeListener.add("WoltLabSuite/Core/Ui/Password", () => initElements());
}

function initElements(): void {
  document.querySelectorAll("input[type=password]").forEach((input: HTMLInputElement) => {
    if (!_knownElements.has(input)) {
      initElement(input);
    }
  });
}

function initElement(input: HTMLInputElement): void {
  _knownElements.add(input);

  const inputAddon = document.createElement("div");
  inputAddon.classList.add("inputAddon");
  input.insertAdjacentElement("beforebegin", inputAddon);
  inputAddon.appendChild(input);

  const button = document.createElement("span");
  button.title = Language.get("wcf.global.form.password.button.show");
  button.classList.add("button", "inputSuffix", "jsTooltip");
  button.setAttribute("role", "button");
  button.tabIndex = 0;
  button.setAttribute("aria-hidden", "true");
  inputAddon.appendChild(button);

  const icon = document.createElement("span");
  icon.classList.add("icon", "icon16", "fa-eye-slash");
  button.appendChild(icon);

  button.addEventListener("click", () => {
    toggle(input, button, icon);
  });
  button.addEventListener("keydown", (event) => {
    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      toggle(input, button, icon);
    }
  });
}

function toggle(input: HTMLInputElement, button: HTMLElement, icon: HTMLElement): void {
  icon.classList.toggle("fa-eye");
  icon.classList.toggle("fa-eye-slash");
  button.dataset.tooltip = Language.get(
    "wcf.global.form.password.button." + (input.type === "password" ? "hide" : "show"),
  );
  input.type = input.type === "password" ? "text" : "password";
}

import WoltlabCoreMenuItemElement from "./woltlab-core-menu-item";

export class WoltlabCoreMenuElement extends HTMLElement {
  #index = -1;

  constructor() {
    super();

    this.addEventListener("keydown", (event) => {
      this.#keydown(event);
    });
  }

  connectedCallback() {
    this.setAttribute("role", "menu");

    this.label = this.getAttribute("label")!;

    this.#index = 0;
    this.#focusCurrentItem();
  }

  get label(): string {
    return this.getAttribute("label")!;
  }

  set label(label: string) {
    this.setAttribute("label", label);
    this.setAttribute("aria-label", label);
  }

  #keydown(event: KeyboardEvent): void {
    const { code, key } = event;

    // Ignore any keystrokes that are most likely keyboard shortcuts.
    if (event.altKey !== false || event.ctrlKey !== false || event.metaKey !== false) {
      return;
    }

    if (code === "ArrowDown") {
      this.#index++;
      this.#focusCurrentItem();

      event.preventDefault();
      return;
    }

    if (code === "ArrowUp") {
      this.#index--;
      this.#focusCurrentItem();

      event.preventDefault();
      return;
    }

    if (code === "End") {
      this.#index = this.#getItems().length - 1;
      this.#focusCurrentItem();

      event.preventDefault();
      return;
    }

    if (code === "Home") {
      this.#index = 0;
      this.#focusCurrentItem();

      event.preventDefault();
      return;
    }

    if (key.length !== 1) {
      return;
    }

    const value = event.key.toLowerCase();
    const newIndex = this.#getItems().findIndex((item) => {
      return item.textContent!.trim().toLowerCase().startsWith(value);
    });

    if (newIndex !== -1) {
      this.#index = newIndex;
      this.#focusCurrentItem();

      event.preventDefault();
    }
  }

  #focusCurrentItem(): void {
    const items = this.#getItems();
    if (items.length === 0) {
      throw new Error("There are no focusable items");
    }

    if (this.#index < 0) {
      this.#index = items.length - 1;
    } else if (this.#index >= items.length) {
      this.#index = 0;
    }

    items[this.#index].focus();
  }

  #getItems(): WoltlabCoreMenuItemElement[] {
    return Array.from(this.querySelectorAll("woltlab-core-menu-item:not([disabled])"));
  }
}

export default WoltlabCoreMenuElement;

window.customElements.define("woltlab-core-menu", WoltlabCoreMenuElement);

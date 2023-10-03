import WoltlabCoreMenuGroupElement from "./woltlab-core-menu-group";
import WoltlabCoreMenuItemElement from "./woltlab-core-menu-item";

type MenuChild = WoltlabCoreMenuGroupElement | WoltlabCoreMenuItemElement;

interface WoltlabCoreMenuEventMap {
  change: CustomEvent;
  close: CustomEvent;
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export class WoltlabCoreMenuElement extends HTMLElement {
  #index = -1;
  #items = new Set<MenuChild>();

  constructor() {
    super();

    this.addEventListener("keydown", (event) => {
      this.#keydown(event);
    });
  }

  connectedCallback() {
    const shadow = this.attachShadow({ mode: "open" });

    const slot = document.createElement("slot");
    shadow.append(slot);

    slot.addEventListener("slotchange", () => {
      for (const element of slot.assignedElements()) {
        if (!(element instanceof WoltlabCoreMenuGroupElement) && !(element instanceof WoltlabCoreMenuItemElement)) {
          element.remove();
          continue;
        }

        if (this.#items.has(element)) {
          continue;
        }

        this.#items.add(element);

        element.addEventListener("change", () => {
          this.#items.forEach((item) => {
            if (item === element) {
              return;
            }

            if (item instanceof WoltlabCoreMenuGroupElement) {
              item.value = "";
            } else {
              item.selected = false;
            }
          });

          const evt = new CustomEvent("change");
          this.dispatchEvent(evt);

          if (element instanceof WoltlabCoreMenuItemElement) {
            const evt = new CustomEvent("close");
            this.dispatchEvent(evt);
          }
        });
      }
    });

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

  get value(): string {
    for (const item of Array.from(this.#items)) {
      const value = item.value;

      if (item instanceof WoltlabCoreMenuGroupElement) {
        if (value !== "") {
          return value;
        }
      } else if (item.selected) {
        return value;
      }
    }

    return "";
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

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export interface WoltlabCoreMenuElement extends HTMLElement {
  addEventListener: {
    <T extends keyof WoltlabCoreMenuEventMap>(
      type: T,
      listener: (this: WoltlabCoreMenuItemElement, ev: WoltlabCoreMenuEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
  } & HTMLElement["addEventListener"];
}

window.customElements.define("woltlab-core-menu", WoltlabCoreMenuElement);

export default WoltlabCoreMenuElement;

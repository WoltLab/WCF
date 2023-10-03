import WoltlabCoreMenuItemElement from "./woltlab-core-menu-item";

export class WoltlabCoreMenuGroupElement extends HTMLElement {
  readonly #items = new Set<WoltlabCoreMenuItemElement>();
  #value = "";

  connectedCallback() {
    const shadow = this.attachShadow({ mode: "open" });
    const slot = document.createElement("slot");
    slot.addEventListener("slotchange", () => {
      this.#items.clear();

      for (const element of slot.assignedElements()) {
        if (!(element instanceof WoltlabCoreMenuItemElement)) {
          element.remove();
          continue;
        }

        this.#items.add(element);

        if (this.multiple) {
          element.setRole("menuitemcheckbox");
        } else {
          element.setRole("menuitemradio");
        }

        element.addEventListener("change", () => {
          if (!this.multiple) {
            this.#items.forEach((item) => {
              item.selected = item === element;
            });
          }

          this.#updateValue();
        });
      }
    });

    shadow.append(slot);

    this.setAttribute("role", "group");

    this.label = this.getAttribute("label")!;
  }

  get multiple(): boolean {
    return this.hasAttribute("multiple");
  }

  get label(): string {
    return this.getAttribute("label")!;
  }

  set label(label: string) {
    this.setAttribute("label", label);
    this.setAttribute("aria-label", label);
  }

  get value(): string {
    return this.#value;
  }

  set value(value: string) {
    const values = value.split(",");

    this.#items.forEach((item) => {
      item.selected = values.includes(item.value);
    });

    this.#updateValue();
  }

  #updateValue(): void {
    this.#value = Array.from(this.#items)
      .filter((item) => item.selected)
      .map((item) => item.value)
      .join(",");

    this.setAttribute("value", this.#value);
  }
}

export default WoltlabCoreMenuGroupElement;

window.customElements.define("woltlab-core-menu-group", WoltlabCoreMenuGroupElement);

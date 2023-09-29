import WoltlabCoreMenuGroupElement from "./woltlab-core-menu-group";

const enum MenuItemType {
  Checkbox,
  Item,
}

export class WoltlabCoreMenuItemElement extends HTMLElement {
  #type: MenuItemType = MenuItemType.Item;
  #checkmark?: FaIcon;

  connectedCallback() {
    const shadow = this.attachShadow({ mode: "open" });

    const checkmarkSlot = document.createElement("slot");
    checkmarkSlot.name = "checkmark";
    shadow.append(checkmarkSlot);

    const defaultSlot = document.createElement("slot");
    defaultSlot.id = "slot";
    shadow.append(defaultSlot);

    this.tabIndex = -1;
    this.disabled = this.hasAttribute("disabled");

    if (this.parentElement! instanceof WoltlabCoreMenuGroupElement) {
      this.#type = MenuItemType.Checkbox;
      this.setAttribute("role", "menuitemcheckbox");

      this.selected = this.hasAttribute("selected");

      if (this.#checkmark === undefined) {
        this.#checkmark = document.createElement("fa-icon");
        this.#checkmark.setIcon("check");
        this.#checkmark.slot = "checkmark";
      }

      this.append(this.#checkmark);
    } else {
      this.#type = MenuItemType.Item;
      this.setAttribute("role", "menuitem");

      this.removeAttribute("aria-checked");

      this.#checkmark?.remove();
    }
  }

  get selected(): boolean {
    if (this.#type !== MenuItemType.Item) {
      return false;
    }

    return this.hasAttribute("selected");
  }

  set selected(checked: boolean) {
    if (this.#type !== MenuItemType.Checkbox) {
      return;
    }

    if (checked) {
      this.setAttribute("selected", "");
    } else {
      this.removeAttribute("selected");
    }

    this.setAttribute("aria-checked", String(checked === true));
  }

  get disabled(): boolean {
    return this.hasAttribute("disabled");
  }

  set disabled(disabled: boolean) {
    if (disabled) {
      this.setAttribute("disabled", "");
    } else {
      this.removeAttribute("disabled");
    }

    this.setAttribute("aria-disabled", String(disabled === true));
  }

  get value(): string {
    return this.getAttribute("value")!;
  }

  set value(value: string) {
    this.setAttribute("value", value);
  }
}

export default WoltlabCoreMenuItemElement;

window.customElements.define("woltlab-core-menu-item", WoltlabCoreMenuItemElement);

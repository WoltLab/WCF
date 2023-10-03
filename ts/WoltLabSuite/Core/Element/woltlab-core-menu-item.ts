type Role = "menuitem" | "menuitemcheckbox" | "menuitemradio";

interface WoltlabCoreMenuItemEventMap {
  beforeSelect: CustomEvent;
  change: CustomEvent;
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export class WoltlabCoreMenuItemElement extends HTMLElement {
  #checkmark?: FaIcon;

  constructor() {
    super();

    this.addEventListener("click", () => {
      if (this.disabled) {
        return;
      }

      const role = this.getAttribute("role") as Role;
      if (role === "menuitemradio" && this.selected) {
        return;
      }

      const evt = new CustomEvent("beforeSelect", {
        cancelable: true,
      });
      this.dispatchEvent(evt);

      if (!evt.defaultPrevented) {
        this.selected = !this.selected;

        const evt = new CustomEvent("change");
        this.dispatchEvent(evt);
      }
    });
  }

  connectedCallback() {
    const shadow = this.attachShadow({ mode: "open" });

    const checkmarkSlot = document.createElement("slot");
    checkmarkSlot.name = "checkmark";
    shadow.append(checkmarkSlot);

    const defaultSlot = document.createElement("slot");
    defaultSlot.id = "slot";
    shadow.append(defaultSlot);

    this.tabIndex = -1;
    this.setAttribute("role", "menuitem");
  }

  get selected(): boolean {
    return this.hasAttribute("selected");
  }

  set selected(checked: boolean) {
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

  setRole(role: Role): void {
    this.setAttribute("role", role);
    this.#updateAriaSelected();

    if (role === "menuitem") {
      this.#checkmark?.remove();
    } else if (role === "menuitemcheckbox" || role === "menuitemradio") {
      if (this.#checkmark === undefined) {
        this.#checkmark = document.createElement("fa-icon");
        this.#checkmark.setIcon("check");
        this.#checkmark.slot = "checkmark";
      }

      this.append(this.#checkmark);
    }
  }

  #updateAriaSelected(): void {
    const role = this.getAttribute("role") as Role;
    if (role === "menuitemcheckbox" || role === "menuitemradio") {
      this.setAttribute("aria-checked", String(this.selected === true));
    }
  }
}

// eslint-disable-next-line @typescript-eslint/no-unsafe-declaration-merging
export interface WoltlabCoreMenuItemElement extends HTMLElement {
  addEventListener: {
    <T extends keyof WoltlabCoreMenuItemEventMap>(
      type: T,
      listener: (this: WoltlabCoreMenuItemElement, ev: WoltlabCoreMenuItemEventMap[T]) => any,
      options?: boolean | AddEventListenerOptions,
    ): void;
  } & HTMLElement["addEventListener"];
}

window.customElements.define("woltlab-core-menu-item", WoltlabCoreMenuItemElement);

export default WoltlabCoreMenuItemElement;

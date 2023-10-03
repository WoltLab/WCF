import type WoltlabCoreMenuElement from "WoltLabSuite/Core/Element/woltlab-core-menu";
import type WoltlabCoreMenuGroupElement from "WoltLabSuite/Core/Element/woltlab-core-menu-group";

export class MenuGroup {
  readonly #group: WoltlabCoreMenuGroupElement;

  constructor(menu: WoltlabCoreMenuElement) {
    this.#group = document.createElement("woltlab-core-menu-group");
    menu.append(this.#group);
  }

  addItem(value: string, label: string): this {
    const item = document.createElement("woltlab-core-menu-item");
    item.value = value;
    item.textContent = label;
    this.#group.append(item);

    return this;
  }

  addItemWithHtml(value: string, html: string): this {
    const item = document.createElement("woltlab-core-menu-item");
    item.value = value;
    item.innerHTML = html;
    this.#group.append(item);

    return this;
  }

  addDivider(): this {
    const divider = document.createElement("woltlab-core-menu-separator");
    this.#group.append(divider);

    return this;
  }
}

export default MenuGroup;

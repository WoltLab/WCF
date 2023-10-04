import type WoltlabCoreMenuElement from "WoltLabSuite/Core/Element/woltlab-core-menu";
import MenuGroup from "./Group";

export class MenuBuilder {
  readonly #menu: WoltlabCoreMenuElement;

  constructor(menu: WoltlabCoreMenuElement) {
    this.#menu = menu;
  }

  addGroup(label: string, callback: (group: MenuGroup) => void): this {
    const group = new MenuGroup(label, this.#menu);
    callback(group);

    return this;
  }

  addItem(value: string, label: string): this {
    const item = document.createElement("woltlab-core-menu-item");
    item.value = value;
    item.textContent = label;
    this.#menu.append(item);

    return this;
  }

  addItemWithHtml(value: string, html: string): this {
    const item = document.createElement("woltlab-core-menu-item");
    item.value = value;
    item.innerHTML = html;
    this.#menu.append(item);

    return this;
  }

  addLink(label: string, href: string): this {
    const link = document.createElement("a");
    link.href = href;
    link.setAttribute("role", "menuitem");
    link.textContent = label;
    this.#menu.append(link);

    return this;
  }

  addDivider(): this {
    const divider = document.createElement("woltlab-core-menu-separator");
    this.#menu.append(divider);

    return this;
  }

  finalize(): WoltlabCoreMenuElement {
    return this.#menu;
  }
}

export default MenuBuilder;

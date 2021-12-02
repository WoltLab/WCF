import { UserMenuButton, UserMenuData, UserMenuFooter, UserMenuProvider } from "./Data/Provider";
import { getTimeElement } from "../../../Date/Util";
import { escapeHTML } from "../../../StringUtil";
import * as DomChangeListener from "../../../Dom/Change/Listener";

export class UserMenuView {
  private readonly element: HTMLElement;
  private readonly markAllAsReadButton: HTMLElement;
  private readonly provider: UserMenuProvider;

  constructor(provider: UserMenuProvider) {
    this.provider = provider;
    this.element = document.createElement("div");

    this.buildElement();

    this.markAllAsReadButton = this.buildButton({
      icon: '<span class="icon icon24 fa-check"></span>',
      name: "markAllAsRead",
      title: "TODO: Mark all as read",
    });
  }

  getElement(): HTMLElement {
    return this.element;
  }

  async open(): Promise<void> {
    this.reset();

    this.element.hidden = false;

    const data = await this.provider.getData();

    this.setContent(data);
  }

  close(): void {
    this.element.hidden = true;
  }

  private setContent(data: UserMenuData[]): void {
    const content = this.getContent();

    this.markAllAsReadButton.remove();

    if (data.length === 0) {
      content.innerHTML = `<span class="userMenuContentStatus">TODO: Nothing to see here.</span>`;
    } else {
      let hasUnreadContent = false;

      const fragment = document.createDocumentFragment();
      data.forEach((itemData) => {
        if (itemData.isUnread) {
          hasUnreadContent = true;
        }

        fragment.append(this.createItem(itemData));
      });

      content.innerHTML = "";
      content.append(fragment);

      if (hasUnreadContent) {
        this.element.querySelector(".userMenuButtons")!.prepend(this.markAllAsReadButton);
      }

      DomChangeListener.trigger();
    }
  }

  private createItem(itemData: UserMenuData): HTMLElement {
    const element = document.createElement("div");
    element.classList.add("userMenuItem");
    element.dataset.isUnread = itemData.isUnread ? "true" : "false";

    const link = escapeHTML(itemData.link);

    element.innerHTML = `
      <div class="userMenuItemImage">${itemData.image}</div>
      <div class="userMenuItemContent">
        <a href="${link}" class="userMenuItemLink">${itemData.content}</a>
      </div>
      <div class="userMenuItemTime"></div>
      <div class="userMenuItemUnread">
        <a href="#" class="userMenuItemMarkAsRead" role="button">
          <span class="icon icon24 fa-check jsTooltip" title="TODO: Mark as read"></span>
        </a>
      </div>
    `;

    const time = getTimeElement(new Date(itemData.time * 1_000));
    element.querySelector(".userMenuItemTime")!.append(time);

    const markAsRead = element.querySelector(".userMenuItemMarkAsRead")!;
    markAsRead.addEventListener("click", (event) => {
      event.preventDefault();

      // TODO
      element.dataset.isUnread = "false";
    });

    return element;
  }

  private reset(): void {
    const content = this.getContent();
    content.innerHTML = `<span class="userMenuContentStatus"><span class="icon icon24 fa-spinner"></span></span>`;
  }

  private buildElement(): void {
    this.element.hidden = true;
    this.element.classList.add("userMenu");
    this.element.dataset.origin = this.provider.getPanelButton().id;
    this.element.innerHTML = `
      <div class="userMenuHeader">
        <div class="userMenuTitle">${this.provider.getTitle()}</div>
        <div class="userMenuButtons"></div>
      </div>
      <div class="userMenuContent"></div>
    `;

    // Prevent clicks inside the dialog to close it.
    this.element.addEventListener("click", (event) => {
      event.stopPropagation();
    });

    const buttons = this.element.querySelector(".userMenuButtons")!;
    this.provider.getMenuButtons().forEach((button) => {
      buttons.append(this.buildButton(button));
    });

    const footer = this.provider.getFooter();
    if (footer !== null) {
      this.element.append(this.buildFooter(footer));
    }
  }

  private buildButton(button: UserMenuButton): HTMLElement {
    const link = document.createElement("a");
    link.setAttribute("role", "button");
    link.classList.add("userMenuButton", "jsTooltip");
    link.title = button.title;
    link.innerHTML = button.icon;

    if (button.link) {
      link.href = button.link;
    } else {
      link.href = "#";
      link.addEventListener("click", (event) => {
        event.preventDefault();

        this.onButtonClick(button.name);
      });
    }

    return link;
  }

  private onButtonClick(name: string): void {
    if (name === "markAllAsRead") {
      void this.provider.markAllAsRead();

      this.getContent()
        .querySelectorAll(".userMenuItem")
        .forEach((element: HTMLElement) => {
          element.dataset.isUnread = "false";
        });

      this.markAllAsReadButton.remove();
    } else {
      this.provider.onButtonClick(name);
    }
  }

  private buildFooter(footer: UserMenuFooter): HTMLElement {
    const link = escapeHTML(footer.link);
    const title = escapeHTML(footer.title);

    const element = document.createElement("div");
    element.classList.add("userMenuFooter");
    element.innerHTML = `<a href="${link}" class="userMenuFooterLink">${title}</a>`;

    return element;
  }

  private getContent(): HTMLElement {
    return this.element.querySelector(".userMenuContent")!;
  }
}

export default UserMenuView;

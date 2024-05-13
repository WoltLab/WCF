/**
 * User menu for moderation queues.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */

import { dboAction } from "../../../../Ajax";
import UserMenuView from "../View";
import { EventUpdateCounter, UserMenuButton, UserMenuData, UserMenuFooter, UserMenuProvider } from "./Provider";
import { registerProvider } from "../Manager";

type Options = {
  noItems: string;
  deletedContent: string;
  deletedContentLink: string;
  showAllLink: string;
  showAllTitle: string;
  title: string;
};

type ResponseGetData = {
  items: UserMenuData[];
  totalCount: number;
};

type ResponseMarkAsRead = {
  markAsRead: number;
  totalCount: number;
};

class UserMenuDataModerationQueue implements UserMenuProvider {
  private readonly button: HTMLElement;
  private counter = 0;
  private readonly options: Options;
  private stale = true;
  private view: UserMenuView | undefined = undefined;

  constructor(button: HTMLElement, options: Options) {
    this.button = button;
    this.options = options;

    const badge = button.querySelector<HTMLElement>(".badge");
    if (badge) {
      const counter = parseInt(badge.textContent!.trim());
      if (counter) {
        this.counter = counter;
      }
    }
    this.button.addEventListener("updateCounter", (event: CustomEvent<EventUpdateCounter>) => {
      this.updateCounter(event.detail.counter);

      this.stale = true;
    });
  }

  getPanelButton(): HTMLElement {
    return this.button;
  }

  getMenuButtons(): UserMenuButton[] {
    return [
      {
        icon: '<fa-icon size="24" name="trash-can"></fa-icon>',
        link: this.options.deletedContentLink,
        name: "deletedContent",
        title: this.options.deletedContent,
      },
    ];
  }

  async getData(): Promise<UserMenuData[]> {
    const data = (await dboAction("getModerationQueueData", "wcf\\data\\moderation\\queue\\ModerationQueueAction")
      .disableLoadingIndicator()
      .dispatch()) as ResponseGetData;

    this.updateCounter(data.totalCount);

    this.stale = false;

    return data.items;
  }

  getFooter(): UserMenuFooter | null {
    return {
      link: this.options.showAllLink,
      title: this.options.showAllTitle,
    };
  }

  getTitle(): string {
    return this.options.title;
  }

  getView(): UserMenuView {
    if (this.view === undefined) {
      this.view = new UserMenuView(this);
    }

    return this.view;
  }

  getEmptyViewMessage(): string {
    return this.options.noItems;
  }

  isStale(): boolean {
    if (this.stale) {
      return true;
    }

    const unreadItems = this.getView()
      .getItems()
      .filter((item) => item.dataset.isUnread === "true");
    if (this.counter !== unreadItems.length) {
      return true;
    }

    return false;
  }

  getIdentifier(): string {
    return "com.woltlab.wcf.moderation";
  }

  hasPlainTitle(): boolean {
    return true;
  }

  hasUnreadContent(): boolean {
    return this.counter > 0;
  }

  async markAsRead(objectId: number): Promise<void> {
    const response = (await dboAction("markAsRead", "wcf\\data\\moderation\\queue\\ModerationQueueAction")
      .objectIds([objectId])
      .dispatch()) as ResponseMarkAsRead;

    this.updateCounter(response.totalCount);
  }

  async markAllAsRead(): Promise<void> {
    await dboAction("markAllAsRead", "wcf\\data\\moderation\\queue\\ModerationQueueAction").dispatch();

    this.updateCounter(0);
  }

  private updateCounter(counter: number): void {
    let badge = this.button.querySelector<HTMLElement>(".badge");
    if (badge === null && counter > 0) {
      badge = document.createElement("span");
      badge.classList.add("badge", "badgeUpdate");

      this.button.querySelector("a")!.append(badge);
    }

    if (badge) {
      if (counter === 0) {
        badge.remove();
      } else {
        badge.textContent = counter.toString();
      }
    }

    this.counter = counter;
  }
}

let isInitialized = false;
export function setup(options: Options): void {
  if (!isInitialized) {
    const button = document.getElementById("outstandingModeration");
    if (button !== null) {
      const provider = new UserMenuDataModerationQueue(button, options);
      registerProvider(provider);
    }

    isInitialized = true;
  }
}

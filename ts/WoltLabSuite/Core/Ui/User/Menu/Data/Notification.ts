/**
 * User menu for notifications.
 *
 * @author Alexander Ebert
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Ui/User/Menu/Data/Notification
 * @woltlabExcludeBundle tiny
 */

import { dboAction } from "../../../../Ajax";
import UserMenuView from "../View";
import { UserMenuButton, UserMenuData, UserMenuFooter, UserMenuProvider } from "./Provider";
import { registerProvider } from "../Manager";
import * as Language from "../../../../Language";
import { enableNotifications } from "../../../../Notification/Handler";

let originalFavicon = "";
function setFaviconCounter(counter: number): void {
  const favicon = document.querySelector<HTMLLinkElement>('link[rel="shortcut icon"]');
  if (!favicon) {
    return;
  }

  if (!originalFavicon) {
    originalFavicon = favicon.href;
  }

  const text = Math.trunc(counter).toString();
  if (text === "0") {
    favicon.href = originalFavicon;

    return;
  }

  const img = document.createElement("img");
  img.src = originalFavicon;
  img.addEventListener("load", () => {
    const canvas = document.createElement("canvas");
    canvas.width = img.naturalWidth;
    canvas.height = img.naturalHeight;

    const ctx = canvas.getContext("2d");
    if (ctx) {
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

      drawCounter(ctx, text);

      favicon.href = canvas.toDataURL("image/png");
    }
  });
}

// This is a cut-down version of `Favico.js` v0.3.10 which is both unmaintained
// since 2016 and quite bloated.
//
// Source: https://github.com/ejci/favico.js
// Author: Miroslav Magda, http://blog.ejci.net
// License: MIT or GPL-2.0
function drawCounter(ctx: CanvasRenderingContext2D, counter: string): void {
  const size = ctx.canvas.width;

  let more = false;
  let x = 0.4 * size;
  const y = 0.4 * size;
  let width = 0.6 * size;
  const height = 0.6 * size;
  if (counter.length === 2) {
    x = x - width * 0.4;
    width = width * 1.4;
    more = true;
  } else if (counter.length >= 3) {
    x = x - width * 0.65;
    width = width * 1.65;
    more = true;
  }

  ctx.beginPath();
  ctx.fillStyle = "#d00";

  if (more) {
    ctx.moveTo(x + width / 2, y);
    ctx.lineTo(x + width - height / 2, y);
    ctx.quadraticCurveTo(x + width, y, x + width, y + height / 2);
    ctx.lineTo(x + width, y + height - height / 2);
    ctx.quadraticCurveTo(x + width, y + height, x + width - height / 2, y + height);
    ctx.lineTo(x + height / 2, y + height);
    ctx.quadraticCurveTo(x, y + height, x, y + height - height / 2);
    ctx.lineTo(x, y + height / 2);
    ctx.quadraticCurveTo(x, y, x + height / 2, y);
  } else {
    ctx.arc(x + width / 2, y + height / 2, height / 2, 0, 2 * Math.PI);
  }

  ctx.fill();
  ctx.closePath();

  ctx.beginPath();
  ctx.stroke();
  ctx.font = "bold " + Math.floor(height * (counter.length > 2 ? 0.85 : 1)).toString() + "px sans-serif";
  ctx.textAlign = "center";
  ctx.fillStyle = "#fff";

  if (counter.length > 3) {
    ctx.fillText(
      (counter.length > 4 ? 9 : Math.floor(+counter / 1000)).toString() + "k+",
      Math.floor(x + width / 2),
      Math.floor(y + height - height * 0.2),
    );
  } else {
    ctx.fillText(counter, Math.floor(x + width / 2), Math.floor(y + height - height * 0.15));
  }

  ctx.closePath();
}

type Options = {
  noItems: string;
  settingsLink: string;
  settingsTitle: string;
  showAllLink: string;
  showAllTitle: string;
  title: string;
};

type ResponseMarkAsRead = {
  markAsRead: number;
  totalCount: number;
};

class UserMenuDataNotification implements DesktopNotifications, UserMenuProvider {
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
        setFaviconCounter(counter);
        this.counter = counter;
      }
    }

    window.WCF.System.PushNotification.addCallback("userNotificationCount", (counter: number) => {
      this.updateCounter(counter);

      this.stale = true;
    });
  }

  getPanelButton(): HTMLElement {
    return this.button;
  }

  getMenuButtons(): UserMenuButton[] {
    return [
      {
        icon: '<span class="icon icon24 fa-cog"></span>',
        link: this.options.settingsLink,
        name: "settings",
        title: this.options.settingsTitle,
      },
    ];
  }

  getIdentifier(): string {
    return "com.woltlab.wcf.notifications";
  }

  async getData(): Promise<UserMenuData[]> {
    const data = (await dboAction(
      "getNotificationData",
      "wcf\\data\\user\\notification\\UserNotificationAction",
    ).dispatch()) as UserMenuData[];

    const counter = data.filter((item) => item.isUnread).length;
    this.updateCounter(counter);

    this.stale = false;

    return data;
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

  hasPlainTitle(): boolean {
    return false;
  }

  hasUnreadContent(): boolean {
    return this.counter > 0;
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

  getDesktopNotifications(): HTMLElement | null {
    if (!("Notification" in window)) {
      return null;
    }

    if (Notification.permission === "granted" || Notification.permission === "denied") {
      return null;
    }

    const element = document.createElement("div");
    element.classList.add("userMenuNotifications");
    element.textContent = Language.get("wcf.user.notification.enableDesktopNotifications");

    const buttonContainer = document.createElement("div");
    buttonContainer.classList.add("userMenuNotificationsButtons");
    element.append(buttonContainer);

    const button = document.createElement("button");
    button.classList.add("button", "small", "userMenuNotificationsButton");
    button.textContent = Language.get("wcf.user.notification.enableDesktopNotifications.button");
    button.addEventListener("click", async (event) => {
      event.preventDefault();

      const permission = await Notification.requestPermission();
      if (permission === "granted") {
        enableNotifications();
      }

      element.remove();
    });
    buttonContainer.append(button);

    return element;
  }

  async markAsRead(objectId: number): Promise<void> {
    const response = (await dboAction("markAsConfirmed", "wcf\\data\\user\\notification\\UserNotificationAction")
      .objectIds([objectId])
      .dispatch()) as ResponseMarkAsRead;

    this.updateCounter(response.totalCount);
  }

  async markAllAsRead(): Promise<void> {
    await dboAction("markAllAsConfirmed", "wcf\\data\\user\\notification\\UserNotificationAction").dispatch();

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

    setFaviconCounter(counter);

    this.counter = counter;
  }
}

export interface DesktopNotifications {
  getDesktopNotifications(): HTMLElement | null;
}

let isInitialized = false;
export function setup(options: Options): void {
  if (!isInitialized) {
    const button = document.getElementById("userNotifications");
    if (button !== null) {
      const provider = new UserMenuDataNotification(button, options);
      registerProvider(provider);
    }

    isInitialized = true;
  }
}

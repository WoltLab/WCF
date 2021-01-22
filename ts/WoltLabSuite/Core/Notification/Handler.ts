/**
 * Provides desktop notifications via periodic polling with an
 * increasing request delay on inactivity.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Notification/Handler
 */

import * as Ajax from "../Ajax";
import { AjaxCallbackSetup } from "../Ajax/Data";
import * as Core from "../Core";
import * as EventHandler from "../Event/Handler";
import * as StringUtil from "../StringUtil";

interface NotificationHandlerOptions {
  enableNotifications: boolean;
  icon: string;
}

interface PollingResult {
  notification: {
    link: string;
    message?: string;
    title: string;
  };
}

interface AjaxResponse {
  returnValues: {
    keepAliveData: unknown;
    lastRequestTimestamp: number;
    pollData: PollingResult;
  };
}

class NotificationHandler {
  private allowNotification: boolean;
  private readonly icon: string;
  private inactiveSince = 0;
  private lastRequestTimestamp = window.TIME_NOW;
  private requestTimer?: number = undefined;

  /**
   * Initializes the desktop notification system.
   */
  constructor(options: NotificationHandlerOptions) {
    options = Core.extend(
      {
        enableNotifications: false,
        icon: "",
      },
      options,
    ) as NotificationHandlerOptions;

    this.icon = options.icon;

    this.prepareNextRequest();

    document.addEventListener("visibilitychange", (ev) => this.onVisibilityChange(ev));
    window.addEventListener("storage", () => this.onStorage());

    this.onVisibilityChange();

    if (options.enableNotifications) {
      void this.enableNotifications();
    }
  }

  private async enableNotifications(): Promise<void> {
    switch (window.Notification.permission) {
      case "granted":
        this.allowNotification = true;
        break;

      case "default": {
        const result = await window.Notification.requestPermission();
        if (result === "granted") {
          this.allowNotification = true;
        }
        break;
      }
    }
  }

  /**
   * Detects when this window is hidden or restored.
   */
  private onVisibilityChange(event?: Event) {
    // document was hidden before
    if (event && !document.hidden) {
      const difference = (Date.now() - this.inactiveSince) / 60_000;
      if (difference > 4) {
        this.resetTimer();
        this.dispatchRequest();
      }
    }

    this.inactiveSince = document.hidden ? Date.now() : 0;
  }

  /**
   * Returns the delay in minutes before the next request should be dispatched.
   */
  private getNextDelay(): number {
    if (this.inactiveSince === 0) {
      return 5;
    }

    // milliseconds -> minutes
    const inactiveMinutes = ~~((Date.now() - this.inactiveSince) / 60_000);
    if (inactiveMinutes < 15) {
      return 5;
    } else if (inactiveMinutes < 30) {
      return 10;
    }

    return 15;
  }

  /**
   * Resets the request delay timer.
   */
  private resetTimer(): void {
    if (this.requestTimer) {
      window.clearTimeout(this.requestTimer);
      this.requestTimer = undefined;
    }
  }

  /**
   * Schedules the next request using a calculated delay.
   */
  private prepareNextRequest(): void {
    this.resetTimer();

    this.requestTimer = window.setTimeout(this.dispatchRequest.bind(this), this.getNextDelay() * 60_000);
  }

  /**
   * Requests new data from the server.
   */
  private dispatchRequest(): void {
    const parameters: ArbitraryObject = {};

    EventHandler.fire("com.woltlab.wcf.notification", "beforePoll", parameters);

    // this timestamp is used to determine new notifications and to avoid
    // notifications being displayed multiple times due to different origins
    // (=subdomains) used, because we cannot synchronize them in the client
    parameters.lastRequestTimestamp = this.lastRequestTimestamp;

    Ajax.api(this, {
      parameters: parameters,
    });
  }

  /**
   * Notifies subscribers for updated data received by another tab.
   */
  private onStorage(): void {
    // abort and re-schedule periodic request
    this.prepareNextRequest();

    let pollData;
    let keepAliveData;
    let abort = false;
    try {
      pollData = window.localStorage.getItem(Core.getStoragePrefix() + "notification");
      keepAliveData = window.localStorage.getItem(Core.getStoragePrefix() + "keepAliveData");

      pollData = JSON.parse(pollData);
      keepAliveData = JSON.parse(keepAliveData);
    } catch (e) {
      abort = true;
    }

    if (!abort) {
      EventHandler.fire("com.woltlab.wcf.notification", "onStorage", {
        pollData: pollData,
        keepAliveData: keepAliveData,
      });
    }
  }

  _ajaxSuccess(data: AjaxResponse): void {
    const keepAliveData = data.returnValues.keepAliveData;
    const pollData = data.returnValues.pollData;

    // forward keep alive data
    window.WCF.System.PushNotification.executeCallbacks({ returnValues: keepAliveData });

    // store response data in local storage
    let abort = false;
    try {
      window.localStorage.setItem(Core.getStoragePrefix() + "notification", JSON.stringify(pollData));
      window.localStorage.setItem(Core.getStoragePrefix() + "keepAliveData", JSON.stringify(keepAliveData));
    } catch (e) {
      // storage is unavailable, e.g. in private mode, log error and disable polling
      abort = true;

      window.console.log(e);
    }

    if (!abort) {
      this.prepareNextRequest();
    }

    this.lastRequestTimestamp = data.returnValues.lastRequestTimestamp;

    EventHandler.fire("com.woltlab.wcf.notification", "afterPoll", pollData);

    this.showNotification(pollData);
  }

  /**
   * Displays a desktop notification.
   */
  private showNotification(pollData: PollingResult): void {
    if (!this.allowNotification) {
      return;
    }

    if (typeof pollData.notification === "object" && typeof pollData.notification.message === "string") {
      const notification = new window.Notification(pollData.notification.title, {
        body: StringUtil.unescapeHTML(pollData.notification.message),
        icon: this.icon,
      });
      notification.onclick = () => {
        window.focus();
        notification.close();

        window.location.href = pollData.notification.link;
      };
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "poll",
        className: "wcf\\data\\session\\SessionAction",
      },
      ignoreError: !window.ENABLE_DEBUG_MODE,
      silent: !window.ENABLE_DEBUG_MODE,
    };
  }
}

let notificationHandler: NotificationHandler;

/**
 * Initializes the desktop notification system.
 */
export function setup(options: NotificationHandlerOptions): void {
  if (!notificationHandler) {
    notificationHandler = new NotificationHandler(options);
  }
}

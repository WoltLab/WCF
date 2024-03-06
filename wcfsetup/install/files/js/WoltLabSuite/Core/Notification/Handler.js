/**
 * Provides desktop notifications via periodic polling with an
 * increasing request delay on inactivity.
 *
 * @author      Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "tslib", "../Ajax", "../Core", "../Event/Handler", "../Ui/User/Menu/Manager"], function (require, exports, tslib_1, Ajax, Core, EventHandler, Manager_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.poll = exports.enableNotifications = exports.setup = void 0;
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    EventHandler = tslib_1.__importStar(EventHandler);
    class NotificationHandler {
        allowNotification;
        icon;
        inactiveSince = 0;
        lastRequestTimestamp = window.TIME_NOW;
        requestTimer = undefined;
        /**
         * Initializes the desktop notification system.
         */
        constructor(options) {
            options = Core.extend({
                icon: "",
            }, options);
            this.icon = options.icon;
            this.prepareNextRequest();
            document.addEventListener("visibilitychange", (ev) => this.onVisibilityChange(ev));
            window.addEventListener("storage", () => this.onStorage());
            this.onVisibilityChange();
            if ("Notification" in window && Notification.permission === "granted") {
                this.allowNotification = true;
            }
        }
        enableNotifications() {
            this.allowNotification = true;
        }
        /**
         * Detects when this window is hidden or restored.
         */
        onVisibilityChange(event) {
            // document was hidden before
            if (event && !document.hidden) {
                const difference = (Date.now() - this.inactiveSince) / 60000;
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
        getNextDelay() {
            if (this.inactiveSince === 0) {
                return 5;
            }
            // milliseconds -> minutes
            const inactiveMinutes = ~~((Date.now() - this.inactiveSince) / 60000);
            if (inactiveMinutes < 15) {
                return 5;
            }
            else if (inactiveMinutes < 30) {
                return 10;
            }
            return 15;
        }
        /**
         * Resets the request delay timer.
         */
        resetTimer() {
            if (this.requestTimer) {
                window.clearTimeout(this.requestTimer);
                this.requestTimer = undefined;
            }
        }
        /**
         * Schedules the next request using a calculated delay.
         */
        prepareNextRequest() {
            this.resetTimer();
            this.requestTimer = window.setTimeout(() => this.dispatchRequest(), this.getNextDelay() * 60000);
        }
        /**
         * Requests new data from the server.
         */
        dispatchRequest() {
            const parameters = {};
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
        onStorage() {
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
            }
            catch (e) {
                abort = true;
            }
            if (!abort) {
                EventHandler.fire("com.woltlab.wcf.notification", "onStorage", {
                    pollData,
                    keepAliveData,
                });
            }
        }
        _ajaxSuccess(data) {
            const keepAliveData = data.returnValues.keepAliveData;
            const pollData = data.returnValues.pollData;
            // forward keep alive data
            (0, Manager_1.updateCounter)("com.woltlab.wcf.notifications", keepAliveData.userNotificationCount);
            // store response data in local storage
            let abort = false;
            try {
                window.localStorage.setItem(Core.getStoragePrefix() + "notification", JSON.stringify(pollData));
                window.localStorage.setItem(Core.getStoragePrefix() + "keepAliveData", JSON.stringify(keepAliveData));
            }
            catch (e) {
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
        showNotification(pollData) {
            if (!this.allowNotification) {
                return;
            }
            if (typeof pollData.notification === "object" && typeof pollData.notification.message === "string") {
                let notification;
                const div = document.createElement("div");
                div.innerHTML = pollData.notification.message;
                div.querySelectorAll("img").forEach((img) => {
                    img.replaceWith(document.createTextNode(img.alt));
                });
                try {
                    notification = new window.Notification(pollData.notification.title, {
                        body: div.textContent,
                        icon: this.icon,
                    });
                }
                catch (e) {
                    // The `Notification` constructor is not available on Android.
                    // See https://bugs.chromium.org/p/chromium/issues/detail?id=481856
                    if (e instanceof Error) {
                        if (e.name === "TypeError") {
                            return;
                        }
                    }
                    throw e;
                }
                notification.onclick = () => {
                    window.focus();
                    notification.close();
                    window.location.href = pollData.notification.link;
                };
            }
        }
        _ajaxSetup() {
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
    let notificationHandler;
    /**
     * Initializes the desktop notification system.
     */
    function setup(options) {
        if (!notificationHandler) {
            notificationHandler = new NotificationHandler(options);
        }
    }
    exports.setup = setup;
    function enableNotifications() {
        notificationHandler.enableNotifications();
    }
    exports.enableNotifications = enableNotifications;
    function poll() {
        notificationHandler?.dispatchRequest();
    }
    exports.poll = poll;
});

/**
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 * @woltlabExcludeBundle tiny
 */
define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend"], function (require, exports, Backend_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.serviceWorkerSupported = serviceWorkerSupported;
    exports.setup = setup;
    exports.registerServiceWorker = registerServiceWorker;
    let _serviceWorker = null;
    class ServiceWorker {
        #publicKey;
        #serviceWorkerJsUrl;
        #registerUrl;
        #serviceWorkerRegistration;
        constructor(publicKey, serviceWorkerJsUrl, registerUrl) {
            this.#publicKey = publicKey;
            this.#serviceWorkerJsUrl = serviceWorkerJsUrl;
            this.#registerUrl = registerUrl;
            void window.navigator.serviceWorker.register(this.#serviceWorkerJsUrl, {
                scope: "/",
            });
            this.#serviceWorkerRegistration = window.navigator.serviceWorker.ready;
        }
        async register() {
            const currentSubscription = await (await this.#serviceWorkerRegistration).pushManager.getSubscription();
            if (currentSubscription && this.#compareApplicationServerKey(currentSubscription)) {
                return;
            }
            await this.unsubscribe(currentSubscription);
            const subscription = await (await this.#serviceWorkerRegistration).pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.#urlBase64ToUint8Array(this.#publicKey),
            });
            if (!subscription) {
                // subscription failed
                return;
            }
            await this.#sendRequest(subscription);
        }
        async unsubscribe(subscription) {
            if (subscription) {
                await this.#sendRequest(subscription, true);
                await subscription.unsubscribe();
            }
        }
        #compareApplicationServerKey(subscription) {
            let base64 = window.btoa(String.fromCharCode(...new Uint8Array(subscription.options.applicationServerKey)));
            base64 = base64.replace(/\+/g, "-").replace(/\//g, "_");
            base64 = base64.replace(/=+$/, "");
            return base64 === this.#publicKey;
        }
        async #sendRequest(subscription, remove = false) {
            const key = subscription.getKey("p256dh");
            const token = subscription.getKey("auth");
            // aes128gcm must be supported from browser
            // @see https://w3c.github.io/push-api/#dom-pushmanager-supportedcontentencodings
            const contentEncoding = (PushManager.supportedContentEncodings || ["aes128gcm"])[0];
            try {
                await (0, Backend_1.prepareRequest)(this.#registerUrl)
                    .post({
                    remove: remove,
                    endpoint: subscription.endpoint,
                    publicKey: key ? window.btoa(String.fromCharCode(...new Uint8Array(key))) : null,
                    authToken: token ? window.btoa(String.fromCharCode(...new Uint8Array(token))) : null,
                    contentEncoding: contentEncoding,
                })
                    .disableLoadingIndicator()
                    .fetchAsResponse();
            }
            catch (_) {
                // ignore registration errors
            }
        }
        //@see https://github.com/mdn/serviceworker-cookbook/blob/master/tools.js
        #urlBase64ToUint8Array(base64String) {
            const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    }
    function serviceWorkerSupported() {
        if (location.protocol !== "https:") {
            // Service workers are only available on https
            return false;
        }
        if (!("serviceWorker" in window.navigator)) {
            return false;
        }
        if (!("PushManager" in window)) {
            return false;
        }
        if (!("showNotification" in ServiceWorkerRegistration.prototype)) {
            return false;
        }
        if ("Notification" in window && Notification.permission === "denied") {
            return false;
        }
        return true;
    }
    function setup(publicKey, serviceWorkerJsUrl, registerUrl) {
        if (!serviceWorkerSupported()) {
            return;
        }
        _serviceWorker = new ServiceWorker(publicKey, serviceWorkerJsUrl, registerUrl);
        if (Notification.permission === "granted") {
            registerServiceWorker();
        }
    }
    function registerServiceWorker() {
        void _serviceWorker?.register();
    }
});

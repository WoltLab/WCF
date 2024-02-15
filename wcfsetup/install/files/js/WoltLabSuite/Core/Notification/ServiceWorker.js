define(["require", "exports", "WoltLabSuite/Core/Ajax/Backend"], function (require, exports, Backend_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.registerServiceWorker = exports.init = void 0;
    /**
     * @author      Olaf Braun
     * @copyright   2001-2024 WoltLab GmbH
     * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
     * @woltlabExcludeBundle tiny
     */
    let _serviceWorker = null;
    class ServiceWorker {
        publicKey;
        serviceWorkerJsUrl;
        registerUrl;
        constructor(publicKey, serviceWorkerJsUrl, registerUrl) {
            this.publicKey = publicKey;
            this.serviceWorkerJsUrl = serviceWorkerJsUrl;
            this.registerUrl = registerUrl;
        }
        async register() {
            try {
                await window.navigator.serviceWorker.register(this.serviceWorkerJsUrl, { scope: "/" });
                const serviceWorkerRegistration = await window.navigator.serviceWorker.ready;
                await serviceWorkerRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.#urlBase64ToUint8Array(this.publicKey),
                });
                const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
                if (!subscription) {
                    // subscription failed
                    return;
                }
                const key = subscription.getKey("p256dh");
                const token = subscription.getKey("auth");
                // aes128gcm must be supported from browser
                // @see https://w3c.github.io/push-api/#dom-pushmanager-supportedcontentencodings
                const contentEncoding = (PushManager.supportedContentEncodings || ["aes128gcm"])[0];
                try {
                    await (0, Backend_1.prepareRequest)(this.registerUrl)
                        .post({
                        endpoint: subscription.endpoint,
                        publicKey: key ? window.btoa(String.fromCharCode(...new Uint8Array(key))) : null,
                        authToken: token ? window.btoa(String.fromCharCode(...new Uint8Array(token))) : null,
                        contentEncoding,
                    })
                        .disableLoadingIndicator()
                        .fetchAsResponse();
                }
                catch (_) {
                    // ignore registration errors
                }
            }
            catch (_) {
                // Server keys has possible changed
                await this.unregister();
            }
        }
        async unregister() {
            const serviceWorkerRegistration = await window.navigator.serviceWorker.ready;
            const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
            if (subscription) {
                await subscription.unsubscribe();
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
    function init(publicKey, serviceWorkerJsUrl, registerUrl) {
        if (!serviceWorkerSupported()) {
            return;
        }
        _serviceWorker = new ServiceWorker(publicKey, serviceWorkerJsUrl, registerUrl);
        if (Notification.permission === "granted") {
            registerServiceWorker();
        }
    }
    exports.init = init;
    function registerServiceWorker() {
        void _serviceWorker?.register();
    }
    exports.registerServiceWorker = registerServiceWorker;
});

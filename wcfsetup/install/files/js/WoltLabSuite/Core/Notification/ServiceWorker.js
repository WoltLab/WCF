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
            //check if service worker is already registered
            if (navigator.serviceWorker.controller) {
                //TODO
                //return;
            }
        }
        async register() {
            await navigator.serviceWorker.register(this.serviceWorkerJsUrl, { scope: "/" });
            const serviceWorkerRegistration = await navigator.serviceWorker.ready;
            await serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.#base64ToUint8Array(this.publicKey),
            });
            const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
            if (!subscription) {
                // subscription failed
                return;
            }
            const key = subscription.getKey("p256dh");
            const token = subscription.getKey("auth");
            const contentEncoding = (PushManager.supportedContentEncodings || ["aesgcm"])[0];
            try {
                await (0, Backend_1.prepareRequest)(this.registerUrl)
                    .post({
                    endpoint: subscription.endpoint,
                    publicKey: key ? btoa(String.fromCharCode(...new Uint8Array(key))) : null,
                    authToken: token ? btoa(String.fromCharCode(...new Uint8Array(token))) : null,
                    contentEncoding,
                })
                    .disableLoadingIndicator()
                    .fetchAsResponse();
            }
            catch (_) {
                // ignore registration errors
            }
        }
        #base64ToUint8Array(base64String) {
            const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
            return Uint8Array.from(atob(base64), (c) => c.charCodeAt(0));
        }
    }
    function serviceWorkerSupported() {
        if (!("serviceWorker" in navigator)) {
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
        if (_serviceWorker === null) {
            return;
        }
        void _serviceWorker.register();
    }
    exports.registerServiceWorker = registerServiceWorker;
});

/// <reference types="grecaptcha" />
define(["require", "exports", "WoltLabSuite/Core/Controller/Captcha"], function (require, exports, Captcha_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.Recaptcha = void 0;
    let recaptchaPromise;
    function recaptchaLoaded(recaptchaType, publicKey) {
        if (recaptchaPromise === undefined) {
            recaptchaPromise = new Promise((resolve, reject) => {
                const script = document.createElement("script");
                if (recaptchaType === "v3") {
                    script.src = `https://www.recaptcha.net/recaptcha/api.js?render=${publicKey}`;
                }
                else {
                    script.src = "https://www.recaptcha.net/recaptcha/api.js?render=explicit";
                }
                script.async = true;
                script.defer = true;
                script.onload = () => resolve();
                script.onerror = () => reject(new Error("Failed to load ReCaptcha script"));
                document.head.appendChild(script);
            });
        }
        return recaptchaPromise;
    }
    class Recaptcha {
        #widgetID;
        #container;
        #publicKey;
        #captchaID;
        #recaptchaType;
        #tokenPromise;
        #tokenReject;
        #tokenResolve;
        constructor(recaptchaType, publicKey, bucketID, captchaID) {
            this.#publicKey = publicKey;
            this.#recaptchaType = recaptchaType;
            this.#captchaID = captchaID;
            this.#container = document.getElementById(bucketID);
            if (!this.#container) {
                throw new Error(`Container with ID ${bucketID} does not exist`);
            }
            this.#tokenPromise = new Promise((resolve, reject) => {
                this.#tokenResolve = resolve;
                this.#tokenReject = reject;
            });
            this.ensureRecaptchaLoaded()
                .then(() => this.#render())
                .catch((error) => {
                console.error("Failed to load ReCaptcha script:", error);
            });
        }
        async ensureRecaptchaLoaded() {
            await recaptchaLoaded(this.#recaptchaType, this.#publicKey);
            await new Promise((resolve) => {
                window.grecaptcha.ready(resolve);
            });
        }
        async execute() {
            switch (this.#recaptchaType) {
                case "v3":
                    return window.grecaptcha.execute(this.#publicKey, { action: "submit" });
                case "invisible":
                    await window.grecaptcha.execute(this.#widgetID);
                    return this.#tokenPromise;
                case "v2":
                    return window.grecaptcha.getResponse(this.#widgetID);
            }
        }
        #render() {
            if (this.#recaptchaType !== "v3") {
                this.#widgetID = window.grecaptcha.render(this.#container, this.#getParameters());
            }
            if (this.#captchaID) {
                (0, Captcha_1.add)(this.#captchaID, () => {
                    return this.#getPostParameters();
                });
            }
            else {
                const form = this.#container.closest("form");
                const submitButton = form.querySelector("input[type=submit], button[type=submit]");
                const listener = (event) => {
                    event.preventDefault();
                    submitButton.disabled = true;
                    void this.execute().then((token) => {
                        form.removeEventListener("submit", listener);
                        // reCaptcha v3 does not render a visible widget or add an input field like v2 or invisible
                        if (this.#recaptchaType === "v3") {
                            const input = document.createElement("input");
                            input.type = "hidden";
                            input.name = "g-recaptcha-response";
                            input.value = token;
                            form.appendChild(input);
                        }
                        form.submit();
                    });
                };
                form.addEventListener("submit", listener);
            }
        }
        async #getPostParameters() {
            return {
                "recaptcha-type": this.#recaptchaType,
                "g-recaptcha-response": await this.execute(),
            };
        }
        #getParameters() {
            switch (this.#recaptchaType) {
                case "v3":
                    return {
                        sitekey: this.#publicKey,
                    };
                case "v2":
                    return {
                        sitekey: this.#publicKey,
                        theme: document.documentElement.dataset.colorScheme === "dark" ? "dark" : "light",
                    };
                case "invisible":
                    return {
                        sitekey: this.#publicKey,
                        size: "invisible",
                        badge: "inline",
                        callback: this.#tokenResolve,
                        "error-callback": this.#tokenReject,
                        theme: document.documentElement.dataset.colorScheme === "dark" ? "dark" : "light",
                    };
            }
        }
    }
    exports.Recaptcha = Recaptcha;
});

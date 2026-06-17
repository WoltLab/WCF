/// <reference types="grecaptcha" />

/**
 * Handles Google reCaptcha.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */

import { add as addCaptcha } from "WoltLabSuite/Core/Controller/Captcha";

let recaptchaPromise: Promise<void> | undefined;

function recaptchaLoaded(recaptchaType: ReCaptchaType, publicKey: string): Promise<void> {
  if (recaptchaPromise === undefined) {
    recaptchaPromise = new Promise<void>((resolve, reject) => {
      const script = document.createElement("script");
      if (recaptchaType === "v3") {
        script.src = `https://www.recaptcha.net/recaptcha/api.js?render=${publicKey}`;
      } else {
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

type ReCaptchaType = "v3" | "v2" | "invisible";

type ReCaptchaPostParameters = {
  "recaptcha-type": ReCaptchaType;
  "g-recaptcha-response": string;
};

export class Recaptcha {
  #widgetID: number;
  readonly #container: HTMLElement;
  readonly #publicKey: string;
  readonly #captchaID?: string;
  readonly #recaptchaType: ReCaptchaType;
  readonly #tokenPromise: Promise<string>;
  #tokenReject!: () => void;
  #tokenResolve!: (token: string) => void;

  constructor(recaptchaType: ReCaptchaType, publicKey: string, bucketID: string, captchaID?: string) {
    this.#publicKey = publicKey;
    this.#recaptchaType = recaptchaType;
    this.#captchaID = captchaID;
    this.#container = document.getElementById(bucketID)!;
    if (!this.#container) {
      throw new Error(`Container with ID ${bucketID} does not exist`);
    }

    this.#tokenPromise = new Promise<string>((resolve, reject) => {
      this.#tokenResolve = resolve;
      this.#tokenReject = reject;
    });

    this.ensureRecaptchaLoaded()
      .then(() => this.#render())
      .catch((error) => {
        console.error("Failed to load ReCaptcha script:", error);
      });
  }

  public async ensureRecaptchaLoaded(): Promise<void> {
    await recaptchaLoaded(this.#recaptchaType, this.#publicKey);

    await new Promise<void>((resolve) => {
      window.grecaptcha.ready(resolve);
    });
  }

  public async execute(): Promise<string> {
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
      addCaptcha(this.#captchaID, () => {
        return this.#getPostParameters();
      });
    } else {
      const form = this.#container.closest("form")!;
      const submitButton = form.querySelector<HTMLInputElement>("input[type=submit]")!;

      const listener = (event: Event) => {
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

  async #getPostParameters(): Promise<ReCaptchaPostParameters> {
    return {
      "recaptcha-type": this.#recaptchaType,
      "g-recaptcha-response": await this.execute(),
    };
  }

  #getParameters(): ReCaptchaV2.Parameters {
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

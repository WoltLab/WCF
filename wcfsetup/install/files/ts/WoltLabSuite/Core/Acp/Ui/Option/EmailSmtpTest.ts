/**
 * Simple SMTP connection testing.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2018 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Option/EmailSmtpTest
 */

import * as Ajax from "../../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../../Ajax/Data";
import DomUtil from "../../../Dom/Util";
import * as Language from "../../../Language";

interface AjaxResponse {
  returnValues: {
    fieldName?: string;
    validationResult: string;
  };
}

class EmailSmtpTest implements AjaxCallbackObject {
  private readonly buttonRunTest: HTMLAnchorElement;
  private readonly container: HTMLDivElement;

  constructor() {
    let smtpCheckbox: HTMLInputElement | null = null;
    const methods = document.querySelectorAll('input[name="values[mail_send_method]"]');
    methods.forEach((checkbox: HTMLInputElement) => {
      checkbox.addEventListener("change", () => this.onChange(checkbox));

      if (checkbox.value === "smtp") {
        smtpCheckbox = checkbox;
      }
    });

    // This configuration part is unavailable when running in enterprise mode.
    if (methods.length === 0) {
      return;
    }

    if (smtpCheckbox) {
      this.onChange(smtpCheckbox);
    }

    this.container = document.createElement("div");
    this.container.innerHTML = `<dt>${Language.get("wcf.acp.email.smtp.test")}</dt>
<dd>
  <a href="#" class="button">${Language.get("wcf.acp.email.smtp.test.run")}</a>
  <small>${Language.get("wcf.acp.email.smtp.test.description")}</small>
</dd>`;

    this.buttonRunTest = this.container.querySelector("a")!;
    this.buttonRunTest.addEventListener("click", (ev) => this.onClick(ev));
  }

  private onChange(checkbox: HTMLInputElement): void {
    if (checkbox.value === "smtp" && checkbox.checked) {
      if (this.container.parentElement === null) {
        this.initUi(checkbox);
      }

      DomUtil.show(this.container);
    } else if (this.container.parentElement !== null) {
      DomUtil.hide(this.container);
    }
  }

  private initUi(checkbox: HTMLInputElement): void {
    const insertAfter = checkbox.closest("dl") as HTMLDListElement;
    insertAfter.insertAdjacentElement("afterend", this.container);
  }

  private onClick(event: MouseEvent) {
    event.preventDefault();

    this.buttonRunTest.classList.add("disabled");
    this.buttonRunTest.innerHTML = `<span class="icon icon16 fa-spinner"></span> ${Language.get("wcf.global.loading")}`;

    DomUtil.innerError(this.buttonRunTest, false);

    window.setTimeout(() => {
      const startTls = document.querySelector('input[name="values[mail_smtp_starttls]"]:checked') as HTMLInputElement;

      const host = document.getElementById("mail_smtp_host") as HTMLInputElement;
      const port = document.getElementById("mail_smtp_port") as HTMLInputElement;
      const user = document.getElementById("mail_smtp_user") as HTMLInputElement;
      const password = document.getElementById("mail_smtp_password") as HTMLInputElement;

      Ajax.api(this, {
        parameters: {
          host: host.value,
          port: port.value,
          startTls: startTls ? startTls.value : "",
          user: user.value,
          password: password.value,
        },
      });
    }, 100);
  }

  _ajaxSuccess(data: AjaxResponse): void {
    const result = data.returnValues.validationResult;
    if (result === "") {
      this.resetButton(true);
    } else {
      this.resetButton(false, result);
    }
  }

  _ajaxFailure(data: AjaxResponse): boolean {
    let result = "";
    if (data && data.returnValues && data.returnValues.fieldName) {
      result = Language.get(`wcf.acp.email.smtp.test.error.empty.${data.returnValues.fieldName}`);
    }

    this.resetButton(false, result);

    return result === "";
  }

  private resetButton(success: boolean, errorMessage?: string): void {
    this.buttonRunTest.classList.remove("disabled");

    if (success) {
      this.buttonRunTest.innerHTML = `<span class="icon icon16 fa-check green"></span> ${Language.get(
        "wcf.acp.email.smtp.test.run.success",
      )}`;
    } else {
      this.buttonRunTest.innerHTML = Language.get("wcf.acp.email.smtp.test.run");
    }

    if (errorMessage) {
      DomUtil.innerError(this.buttonRunTest, errorMessage);
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "emailSmtpTest",
        className: "wcf\\data\\option\\OptionAction",
      },
      silent: true,
    };
  }
}

let emailSmtpTest: EmailSmtpTest;

export function init(): void {
  if (!emailSmtpTest) {
    emailSmtpTest = new EmailSmtpTest();
  }
}

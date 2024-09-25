/**
 * Simple SMTP connection testing.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2018 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Dom/Util", "../../../Language"], function (require, exports, tslib_1, Ajax, Util_1, Language) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    class EmailSmtpTest {
        buttonRunTest;
        container;
        constructor() {
            let smtpCheckbox = null;
            const methods = document.querySelectorAll('input[name="values[mail_send_method]"]');
            methods.forEach((checkbox) => {
                checkbox.addEventListener("change", () => this.onChange(checkbox));
                if (checkbox.value === "smtp") {
                    smtpCheckbox = checkbox;
                }
            });
            // This configuration part is unavailable when running in enterprise mode.
            if (methods.length === 0) {
                return;
            }
            this.container = document.createElement("dl");
            this.container.innerHTML = `<dt>${Language.get("wcf.acp.email.smtp.test")}</dt>
<dd>
  <a href="#" class="button">${Language.get("wcf.acp.email.smtp.test.run")}</a>
  <small>${Language.get("wcf.acp.email.smtp.test.description")}</small>
</dd>`;
            this.buttonRunTest = this.container.querySelector("a");
            this.buttonRunTest.addEventListener("click", (ev) => this.onClick(ev));
            if (smtpCheckbox) {
                this.onChange(smtpCheckbox);
            }
        }
        onChange(checkbox) {
            if (checkbox.value === "smtp" && checkbox.checked) {
                if (this.container.parentElement === null) {
                    this.initUi(checkbox);
                }
                Util_1.default.show(this.container);
            }
            else if (this.container.parentElement !== null) {
                Util_1.default.hide(this.container);
            }
        }
        initUi(checkbox) {
            const insertAfter = checkbox.closest("dl");
            insertAfter.insertAdjacentElement("afterend", this.container);
        }
        onClick(event) {
            event.preventDefault();
            this.buttonRunTest.classList.add("disabled");
            this.buttonRunTest.innerHTML = `<fa-icon name="spinner" solid></fa-icon> ${Language.get("wcf.global.loading")}`;
            Util_1.default.innerError(this.buttonRunTest, false);
            window.setTimeout(() => {
                const startTls = document.querySelector('input[name="values[mail_smtp_starttls]"]:checked');
                const host = document.getElementById("mail_smtp_host");
                const port = document.getElementById("mail_smtp_port");
                const user = document.getElementById("mail_smtp_user");
                const password = document.getElementById("mail_smtp_password");
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
        _ajaxSuccess(data) {
            const result = data.returnValues.validationResult;
            if (result === "") {
                this.resetButton(true);
            }
            else {
                this.resetButton(false, result);
            }
        }
        _ajaxFailure(data) {
            let result = "";
            if (data && data.returnValues && data.returnValues.fieldName) {
                result = Language.get(`wcf.acp.email.smtp.test.error.empty.${data.returnValues.fieldName}`);
            }
            this.resetButton(false, result);
            return result === "";
        }
        resetButton(success, errorMessage) {
            this.buttonRunTest.classList.remove("disabled");
            if (success) {
                this.buttonRunTest.innerHTML = `<fa-icon name="check" solid></fa-icon> ${Language.get("wcf.acp.email.smtp.test.run.success")}`;
            }
            else {
                this.buttonRunTest.innerHTML = Language.get("wcf.acp.email.smtp.test.run");
            }
            if (errorMessage) {
                Util_1.default.innerError(this.buttonRunTest, errorMessage);
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "emailSmtpTest",
                    className: "wcf\\data\\option\\OptionAction",
                },
                silent: true,
            };
        }
    }
    let emailSmtpTest;
    function init() {
        if (!emailSmtpTest) {
            emailSmtpTest = new EmailSmtpTest();
        }
    }
});

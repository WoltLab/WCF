define(["require", "exports", "tslib", "../../Dom/Util", "../../Language", "../Dialog"], function (require, exports, tslib_1, Util_1, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.showDialog = void 0;
    Util_1 = tslib_1.__importDefault(Util_1);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class UiRedactorLink {
        constructor() {
            this.boundListener = false;
        }
        open(options) {
            Dialog_1.default.open(this);
            Dialog_1.default.setTitle(this, Language.get("wcf.editor.link." + (options.insert ? "add" : "edit")));
            const submitButton = document.getElementById("redactor-modal-button-action");
            submitButton.textContent = Language.get("wcf.global.button." + (options.insert ? "insert" : "save"));
            this.submitCallback = options.submitCallback;
            // Redactor might modify the button, thus we cannot bind it in the dialog's `onSetup()` callback.
            if (!this.boundListener) {
                this.boundListener = true;
                submitButton.addEventListener("click", () => this.submit());
            }
        }
        submit() {
            if (this.submitCallback()) {
                Dialog_1.default.close(this);
            }
            else {
                const url = document.getElementById("redactor-link-url");
                const errorMessage = url.value.trim() === "" ? "wcf.global.form.error.empty" : "wcf.editor.link.error.invalid";
                Util_1.default.innerError(url, Language.get(errorMessage));
            }
        }
        _dialogSetup() {
            return {
                id: "redactorDialogLink",
                options: {
                    onClose: () => {
                        const url = document.getElementById("redactor-link-url");
                        const small = url.nextElementSibling;
                        if (small && small.nodeName === "SMALL") {
                            small.remove();
                        }
                    },
                    onSetup: (content) => {
                        const submitButton = content.querySelector(".formSubmit > .buttonPrimary");
                        if (submitButton !== null) {
                            content.querySelectorAll('input[type="url"], input[type="text"]').forEach((input) => {
                                input.addEventListener("keyup", (event) => {
                                    if (event.key === "Enter") {
                                        submitButton.click();
                                    }
                                });
                            });
                        }
                    },
                    onShow: () => {
                        const url = document.getElementById("redactor-link-url");
                        url.focus();
                    },
                },
                source: `<dl>
          <dt>
            <label for="redactor-link-url">${Language.get("wcf.editor.link.url")}</label>
          </dt>
          <dd>
            <input type="url" id="redactor-link-url" class="long">
          </dd>
        </dl>
        <dl>
          <dt>
            <label for="redactor-link-url-text">${Language.get("wcf.editor.link.text")}</label>
          </dt>
          <dd>
            <input type="text" id="redactor-link-url-text" class="long">
          </dd>
        </dl>
        <div class="formSubmit">
          <button id="redactor-modal-button-action" class="buttonPrimary"></button>
        </div>`,
            };
        }
    }
    let uiRedactorLink;
    function showDialog(options) {
        if (!uiRedactorLink) {
            uiRedactorLink = new UiRedactorLink();
        }
        uiRedactorLink.open(options);
    }
    exports.showDialog = showDialog;
});

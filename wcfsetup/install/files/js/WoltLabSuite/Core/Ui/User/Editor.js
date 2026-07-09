/**
 * Simple notification overlay.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../Dom/Util", "WoltLabSuite/Core/Language", "../../StringUtil", "../Dialog", "WoltLabSuite/Core/Component/Snackbar"], function (require, exports, tslib_1, Ajax, Core, Util_1, Language_1, StringUtil, Dialog_1, Snackbar_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    StringUtil = tslib_1.__importStar(StringUtil);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class UserEditor {
        actionName = "";
        header;
        constructor() {
            this.header = document.querySelector(".userProfileHeader");
            ["ban", "disableAvatar", "disableCoverPhoto", "disableSignature", "enable"].forEach((action) => {
                const button = document.querySelector(".userProfileHeader__managementOptions .jsButtonUser" + StringUtil.ucfirst(action));
                // The button is missing if the current user lacks the permission.
                if (button) {
                    button.dataset.action = action;
                    button.addEventListener("click", (ev) => this._click(ev));
                }
            });
        }
        /**
         * Handles clicks on action buttons.
         */
        _click(event) {
            event.preventDefault();
            const target = event.currentTarget;
            const action = target.dataset.action || "";
            let actionName = "";
            switch (action) {
                case "ban":
                    if (Core.stringToBool(this.header.dataset.banned || "")) {
                        actionName = "unban";
                    }
                    break;
                case "disableAvatar":
                    if (Core.stringToBool(this.header.dataset.disableAvatar || "")) {
                        actionName = "enableAvatar";
                    }
                    break;
                case "disableCoverPhoto":
                    if (Core.stringToBool(this.header.dataset.disableCoverPhoto || "")) {
                        actionName = "enableCoverPhoto";
                    }
                    break;
                case "disableSignature":
                    if (Core.stringToBool(this.header.dataset.disableSignature || "")) {
                        actionName = "enableSignature";
                    }
                    break;
                case "enable":
                    actionName = Core.stringToBool(this.header.dataset.isDisabled || "") ? "enable" : "disable";
                    break;
            }
            if (actionName === "") {
                this.actionName = action;
                Dialog_1.default.open(this);
            }
            else {
                Ajax.api(this, {
                    actionName: actionName,
                });
            }
        }
        /**
         * Handles form submit and input validation.
         */
        _submit(event) {
            event.preventDefault();
            const label = document.getElementById("wcfUiUserEditorExpiresLabel");
            let expires = "";
            let errorMessage = "";
            const neverExpires = document.getElementById("wcfUiUserEditorNeverExpires");
            if (!neverExpires.checked) {
                const expireValue = document.getElementById("wcfUiUserEditorExpiresDatePicker");
                expires = expireValue.value;
                if (expires === "") {
                    errorMessage = (0, Language_1.getPhrase)("wcf.global.form.error.empty");
                }
            }
            Util_1.default.innerError(label, errorMessage);
            const parameters = {};
            parameters[this.actionName + "Expires"] = expires;
            const reason = document.getElementById("wcfUiUserEditorReason");
            parameters[this.actionName + "Reason"] = reason.value.trim();
            Ajax.api(this, {
                actionName: this.actionName,
                parameters: parameters,
            });
        }
        _ajaxSuccess(data) {
            let button;
            switch (data.actionName) {
                case "ban":
                case "unban": {
                    this.header.dataset.banned = data.actionName === "ban" ? "true" : "false";
                    button = document.querySelector(".userProfileHeader__managementOptions .jsButtonUserBan");
                    button.textContent = (0, Language_1.getPhrase)("wcf.user." + (data.actionName === "ban" ? "unban" : "ban"));
                    const contentTitle = this.header.querySelector(".userProfileHeader__username");
                    let banIcon = contentTitle.querySelector(".jsUserBanned");
                    if (data.actionName === "ban") {
                        banIcon = document.createElement("span");
                        banIcon.innerHTML = '<fa-icon size="16" name="lock"></fa-icon>';
                        banIcon.classList.add("jsUserBanned", "jsTooltip");
                        banIcon.title = data.returnValues;
                        contentTitle.appendChild(banIcon);
                    }
                    else if (banIcon) {
                        banIcon.remove();
                    }
                    break;
                }
                case "disableAvatar":
                case "enableAvatar":
                    this.header.dataset.disableAvatar = data.actionName === "disableAvatar" ? "true" : "false";
                    button = document.querySelector(".userProfileHeader__managementOptions .jsButtonUserDisableAvatar");
                    button.textContent = (0, Language_1.getPhrase)("wcf.user." + (data.actionName === "disableAvatar" ? "enable" : "disable") + "Avatar");
                    break;
                case "disableCoverPhoto":
                case "enableCoverPhoto":
                    this.header.dataset.disableCoverPhoto = data.actionName === "disableCoverPhoto" ? "true" : "false";
                    button = document.querySelector(".userProfileHeader__managementOptions .jsButtonUserDisableCoverPhoto");
                    button.textContent = (0, Language_1.getPhrase)("wcf.user." + (data.actionName === "disableCoverPhoto" ? "enable" : "disable") + "CoverPhoto");
                    break;
                case "disableSignature":
                case "enableSignature":
                    this.header.dataset.disableSignature = data.actionName === "disableSignature" ? "true" : "false";
                    button = document.querySelector(".userProfileHeader__managementOptions .jsButtonUserDisableSignature");
                    button.textContent = (0, Language_1.getPhrase)("wcf.user." + (data.actionName === "disableSignature" ? "enable" : "disable") + "Signature");
                    break;
                case "enable":
                case "disable":
                    this.header.dataset.isDisabled = data.actionName === "disable" ? "true" : "false";
                    button = document.querySelector(".userProfileHeader__managementOptions .jsButtonUserEnable");
                    button.textContent = (0, Language_1.getPhrase)("wcf.acp.user." + (data.actionName === "enable" ? "disable" : "enable"));
                    break;
            }
            if (["ban", "disableAvatar", "disableCoverPhoto", "disableSignature"].indexOf(data.actionName) !== -1) {
                Dialog_1.default.close(this);
            }
            (0, Snackbar_1.showDefaultSuccessSnackbar)();
        }
        _ajaxSetup() {
            return {
                data: {
                    className: "wcf\\data\\user\\UserAction",
                    objectIDs: [+this.header.dataset.objectId],
                },
            };
        }
        _dialogSetup() {
            return {
                id: "wcfUiUserEditor",
                options: {
                    onSetup: (content) => {
                        const checkbox = document.getElementById("wcfUiUserEditorNeverExpires");
                        checkbox.addEventListener("change", () => {
                            const settings = document.getElementById("wcfUiUserEditorExpiresSettings");
                            Util_1.default[checkbox.checked ? "hide" : "show"](settings);
                        });
                        const submitButton = content.querySelector("button.buttonPrimary");
                        submitButton.addEventListener("click", this._submit.bind(this));
                    },
                    onShow: (content) => {
                        Dialog_1.default.setTitle("wcfUiUserEditor", (0, Language_1.getPhrase)("wcf.user." + this.actionName + ".confirmMessage"));
                        const reason = document.getElementById("wcfUiUserEditorReason");
                        let label = reason.nextElementSibling;
                        const phrase = "wcf.user." + this.actionName + ".reason.description";
                        label.textContent = (0, Language_1.getPhrase)(phrase);
                        if (label.textContent === phrase) {
                            Util_1.default.hide(label);
                        }
                        else {
                            Util_1.default.show(label);
                        }
                        label = document.getElementById("wcfUiUserEditorNeverExpires").nextElementSibling;
                        label.textContent = (0, Language_1.getPhrase)("wcf.user." + this.actionName + ".neverExpires");
                        label = content.querySelector('label[for="wcfUiUserEditorExpires"]');
                        label.textContent = (0, Language_1.getPhrase)("wcf.user." + this.actionName + ".expires");
                        label = document.getElementById("wcfUiUserEditorExpiresLabel");
                        label.textContent = (0, Language_1.getPhrase)("wcf.user." + this.actionName + ".expires.description");
                    },
                },
                source: `<div class="section">
        <dl>
          <dt><label for="wcfUiUserEditorReason">${(0, Language_1.getPhrase)("wcf.global.reason")}</label></dt>
          <dd><textarea id="wcfUiUserEditorReason" cols="40" rows="3"></textarea><small></small></dd>
        </dl>
        <dl>
          <dt></dt>
          <dd><label><input type="checkbox" id="wcfUiUserEditorNeverExpires" checked> <span></span></label></dd>
        </dl>
        <dl id="wcfUiUserEditorExpiresSettings" style="display: none">
          <dt><label for="wcfUiUserEditorExpires"></label></dt>
          <dd>
            <input type="date" name="wcfUiUserEditorExpires" id="wcfUiUserEditorExpires" class="medium" min="${new Date(window.TIME_NOW * 1000).toISOString()}" data-ignore-timezone="true">
            <small id="wcfUiUserEditorExpiresLabel"></small>
          </dd>
        </dl>
      </div>
      <div class="formSubmit">
        <button type="button" class="button buttonPrimary">${(0, Language_1.getPhrase)("wcf.global.button.submit")}</button>
      </div>`,
            };
        }
    }
    /**
     * Initializes the user editor.
     */
    function init() {
        new UserEditor();
    }
});

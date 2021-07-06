/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler/Dialog
 * @since       5.5
 */
define(["require", "exports", "tslib", "../../../../../../Ui/Dialog", "../../../../../../Language", "../../../../../../Ajax", "../../../../../../Date/Picker"], function (require, exports, tslib_1, Dialog_1, Language, Ajax, Picker_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.BanDialog = void 0;
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Language = tslib_1.__importStar(Language);
    Ajax = tslib_1.__importStar(Ajax);
    Picker_1 = tslib_1.__importDefault(Picker_1);
    class BanDialog {
        static open(userIDs, callback) {
            if (!BanDialog.instance) {
                BanDialog.instance = new BanDialog();
            }
            BanDialog.instance.setCallback(callback);
            BanDialog.instance.setUserIDs(userIDs);
            BanDialog.instance.openDialog();
        }
        openDialog() {
            Dialog_1.default.open(this);
        }
        setCallback(callback) {
            this.banCallback = callback;
        }
        setUserIDs(userIDs) {
            this.userIDs = userIDs;
        }
        banSubmit(reason, expires) {
            Ajax.apiOnce({
                data: {
                    actionName: "ban",
                    className: "wcf\\data\\user\\UserAction",
                    objectIDs: this.userIDs,
                    parameters: {
                        banReason: reason,
                        banExpires: expires,
                    },
                },
                success: this.banCallback,
            });
        }
        cleanupDialog() {
            this.reasonInput.value = "";
            this.neverExpiresCheckbox.checked = true;
            Picker_1.default.clear("userBanExpires");
            this.userBanExpiresSettingsElement.style.setProperty("display", "none", "");
        }
        _dialogSetup() {
            return {
                id: "userBanHandler",
                options: {
                    onSetup: (content) => {
                        this.dialogContent = content;
                        this.submitElement = content.querySelector(".formSubmitButton");
                        this.reasonInput = content.querySelector("#userBanReason");
                        this.neverExpiresCheckbox = content.querySelector("#userBanNeverExpires");
                        this.userBanExpiresSettingsElement = content.querySelector("#userBanExpiresSettings");
                        this.submitElement.addEventListener("click", (event) => {
                            event.preventDefault();
                            const expires = this.neverExpiresCheckbox.checked ? "" : Picker_1.default.getValue("userBanExpires");
                            this.banSubmit(this.reasonInput.value, expires);
                            Dialog_1.default.close(this);
                            this.cleanupDialog();
                        });
                        this.neverExpiresCheckbox.addEventListener("change", (event) => {
                            const checkbox = event.currentTarget;
                            if (checkbox.checked) {
                                this.userBanExpiresSettingsElement.style.setProperty("display", "none", "");
                            }
                            else {
                                this.userBanExpiresSettingsElement.style.removeProperty("display");
                            }
                        });
                    },
                    title: Language.get("wcf.acp.user.ban.sure"),
                },
                source: `
<div class="section">
  <dl>
    <dt><label for="userBanReason">${Language.get("wcf.acp.user.banReason")}</label></dt>
    <dd>
      <textarea id="userBanReason" cols="40" rows="3" class=""></textarea>
      <small>${Language.get("wcf.acp.user.banReason.description")}</small>
    </dd>
  </dl>
  <dl>
    <dt></dt>
    <dd>
      <label for="userBanNeverExpires">
        <input type="checkbox" name="userBanNeverExpires" id="userBanNeverExpires" checked="">
        ${Language.get("wcf.acp.user.ban.neverExpires")}
      </label>
    </dd>
  </dl>
  <dl id="userBanExpiresSettings" style="display: none;">
    <dt>
      <label for="userBanExpires">${Language.get("wcf.acp.user.ban.expires")}</label>
    </dt>
    <dd>
      <div class="inputAddon">
        <input  type="date"
                name="userBanExpires"
                id="userBanExpires"
                class="medium"
                min="${new Date(window.TIME_NOW * 1000).toISOString()}"
                data-ignore-timezone="true"
        />
      </div>
      <small>${Language.get("wcf.acp.user.ban.expires.description")}</small>
    </dd>
  </dl>
</div>
<div class="formSubmit dialogFormSubmit">
  <button class="buttonPrimary formSubmitButton" accesskey="s">${Language.get("wcf.global.button.submit")}</button>
</div>`,
            };
        }
    }
    exports.BanDialog = BanDialog;
    exports.default = BanDialog;
});

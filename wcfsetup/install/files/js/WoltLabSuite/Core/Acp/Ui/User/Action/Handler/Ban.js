/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler
 * @since       5.5
 */
define(["require", "exports", "tslib", "../../../../../Language", "../../../../../Ajax", "../../../../../Ui/Dialog"], function (require, exports, tslib_1, Language, Ajax, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.BanHandler = void 0;
    Language = tslib_1.__importStar(Language);
    Ajax = tslib_1.__importStar(Ajax);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class BanHandler {
        constructor(userIDs) {
            this.userIDs = userIDs;
        }
        ban(callback) {
            // Save the callback for later usage.
            // We cannot easily give the callback to the dialog.
            this.banCallback = callback;
            Dialog_1.default.open(this);
        }
        unban(callback) {
            Ajax.api({
                _ajaxSetup: () => {
                    return {
                        data: {
                            actionName: "unban",
                            className: "wcf\\data\\user\\UserAction",
                            objectIDs: this.userIDs,
                        },
                    };
                },
                _ajaxSuccess: callback,
            });
        }
        banSubmit(reason, userBanExpires) {
            Ajax.api({
                _ajaxSetup: () => {
                    return {
                        data: {
                            actionName: "ban",
                            className: "wcf\\data\\user\\UserAction",
                            objectIDs: this.userIDs,
                            parameters: {
                                banReason: reason,
                                banExpires: userBanExpires,
                            },
                        },
                    };
                },
                _ajaxSuccess: this.banCallback,
            });
        }
        _dialogSetup() {
            return {
                id: "userBanHandler",
                options: {
                    onSetup: (content) => {
                        const submit = content.querySelector(".formSubmitButton");
                        const neverExpires = content.querySelector("#userBanNeverExpires");
                        const userBanExpiresSettings = content.querySelector("#userBanExpiresSettings");
                        submit.addEventListener("click", (event) => {
                            event.preventDefault();
                            const reason = content.querySelector("#userBanReason");
                            const neverExpires = content.querySelector("#userBanNeverExpires");
                            const userBanExpires = content.querySelector("#userBanExpiresDatePicker");
                            this.banSubmit(reason.value, neverExpires.checked ? "" : userBanExpires.value);
                            Dialog_1.default.close(this);
                            reason.value = "";
                            neverExpires.checked = true;
                            // @TODO empty userBanExpires
                            userBanExpiresSettings.style.setProperty("display", "none", "");
                        });
                        neverExpires.addEventListener("change", (event) => {
                            const checkbox = event.currentTarget;
                            if (checkbox.checked) {
                                userBanExpiresSettings.style.setProperty("display", "none", "");
                            }
                            else {
                                userBanExpiresSettings.style.removeProperty("display");
                            }
                        });
                    },
                    title: Language.get("wcf.acp.user.ban.sure"),
                },
                source: `<div class="section">
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
    exports.BanHandler = BanHandler;
    exports.default = BanHandler;
});

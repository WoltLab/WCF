import { DialogCallbackSetup } from "../../../../../Ui/Dialog/Data";
import * as Language from "../../../../../Language";
import * as Ajax from "../../../../../Ajax";
import { AjaxCallbackObject, DatabaseObjectActionResponse } from "../../../../../Ajax/Data";
import UiDialog from "../../../../../Ui/Dialog";

/**
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler
 * @since       5.5
 */
export class BanHandler {
      private userIDs: number[];
      private banCallback: () => void;

      public constructor(userIDs: number[]) {
        this.userIDs = userIDs;
      }

      ban(callback: () => void): void {
        // Save the callback for later usage.
        // We cannot easily give the callback to the dialog.
        this.banCallback = callback;

        UiDialog.open(this);
      }

      unban(callback: () => void): void {
        Ajax.api(
          {
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
          }
        );
      }

      private banSubmit(reason: string, userBanExpires: string): void {
        Ajax.api(
          {
            _ajaxSetup: () => {
              return {
                data: {
                  actionName: "ban",
                  className: "wcf\\data\\user\\UserAction",
                  objectIDs: this.userIDs,
                  parameters: {
                    'banReason': reason,
                    'banExpires': userBanExpires,
                  },
                },
              };
            },
            _ajaxSuccess: this.banCallback,
          }
        );
      }

      _dialogSetup(): ReturnType<DialogCallbackSetup> {
        return {
          id: "userBanHandler",
          options: {
            onShow: (content: HTMLElement): void => {
              const submit = content.querySelector(".formSubmitButton")! as HTMLElement;
              const neverExpires = content.querySelector("#userBanNeverExpires")! as HTMLInputElement;
              const userBanExpiresSettings = content.querySelector("#userBanExpiresSettings")! as HTMLElement;

              submit.addEventListener("click", (event) => {
                event.preventDefault();

                const reason = content.querySelector("#userBanReason")! as HTMLInputElement;
                const neverExpires = content.querySelector("#userBanNeverExpires")! as HTMLInputElement;
                const userBanExpires = content.querySelector("#userBanExpiresDatePicker")! as HTMLInputElement;

                this.banSubmit(reason.value, neverExpires.checked ? "" : userBanExpires.value);

                UiDialog.close(this);

                reason.value = "";
                neverExpires.checked = true;
                // @TODO empty userBanExpires
                userBanExpiresSettings.style.setProperty("display", "none", "");
              });

              neverExpires.addEventListener("change", (event) => {
                const checkbox = event.currentTarget as HTMLInputElement;
                if (checkbox.checked) {
                  userBanExpiresSettings.style.setProperty("display", "none", "");
                }
                else {
                  userBanExpiresSettings.style.removeProperty("display");
                }
              });
            },
            title: Language.get('wcf.acp.user.ban.sure'),
          },
          source: `<div class="section">
            <dl>
              <dt><label for="userBanReason">${Language.get('wcf.acp.user.banReason')}</label></dt>
              <dd>
                <textarea id="userBanReason" cols="40" rows="3" class=""></textarea>
                <small>${Language.get('wcf.acp.user.banReason.description')}</small>
              </dd>
            </dl>
            <dl>
              <dt></dt>
              <dd>
                <label for="userBanNeverExpires">
                  <input type="checkbox" name="userBanNeverExpires" id="userBanNeverExpires" checked="">
                  ${Language.get('wcf.acp.user.ban.neverExpires')}
                </label>
              </dd>
            </dl>
            <dl id="userBanExpiresSettings" style="display: none;">
              <dt>
                <label for="userBanExpires">${Language.get('wcf.acp.user.ban.expires')}</label>
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
                <small>${Language.get('wcf.acp.user.ban.expires.description')}</small>
              </dd>
            </dl>
          </div>
          <div class="formSubmit dialogFormSubmit">
            <button class="buttonPrimary formSubmitButton" accesskey="s">${Language.get('wcf.global.button.submit')}</button>
          </div>`,
        };
    }
}

export default BanHandler;
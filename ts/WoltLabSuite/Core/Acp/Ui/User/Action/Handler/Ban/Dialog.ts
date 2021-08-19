/**
 * Creates and handles the dialog to ban a user.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/User/Action/Handler/Ban
 * @since       5.5
 */

import UiDialog from "../../../../../../Ui/Dialog";
import { DialogCallbackSetup } from "../../../../../../Ui/Dialog/Data";
import * as Language from "../../../../../../Language";
import * as Ajax from "../../../../../../Ajax";
import DatePicker from "../../../../../../Date/Picker";

type Callback = () => void;

export class BanDialog {
  private static instance: BanDialog;

  private banCallback: Callback;
  private userIDs: number[];
  private submitElement: HTMLElement;
  private neverExpiresCheckbox: HTMLInputElement;
  private reasonInput: HTMLInputElement;
  private userBanExpiresSettingsElement: HTMLElement;
  private dialogContent: HTMLElement;

  public static open(userIDs: number[], callback: Callback): void {
    if (!BanDialog.instance) {
      BanDialog.instance = new BanDialog();
    }

    BanDialog.instance.setCallback(callback);
    BanDialog.instance.setUserIDs(userIDs);
    BanDialog.instance.openDialog();
  }

  private openDialog(): void {
    UiDialog.open(this);
  }

  private setCallback(callback: Callback): void {
    this.banCallback = callback;
  }

  private setUserIDs(userIDs: number[]): void {
    this.userIDs = userIDs;
  }

  private banSubmit(reason: string, expires: string): void {
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

  private cleanupDialog(): void {
    this.reasonInput.value = "";
    this.neverExpiresCheckbox.checked = true;
    DatePicker.clear("userBanExpires");
    this.userBanExpiresSettingsElement.style.setProperty("display", "none", "");
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "userBanHandler",
      options: {
        onSetup: (content: HTMLElement): void => {
          this.dialogContent = content;
          this.submitElement = content.querySelector(".formSubmitButton")! as HTMLElement;
          this.reasonInput = content.querySelector("#userBanReason")! as HTMLInputElement;
          this.neverExpiresCheckbox = content.querySelector("#userBanNeverExpires")! as HTMLInputElement;
          this.userBanExpiresSettingsElement = content.querySelector("#userBanExpiresSettings")! as HTMLElement;

          this.submitElement.addEventListener("click", (event) => {
            event.preventDefault();

            const expires = this.neverExpiresCheckbox.checked ? "" : DatePicker.getValue("userBanExpires");
            this.banSubmit(this.reasonInput.value, expires);

            UiDialog.close(this);

            this.cleanupDialog();
          });

          this.neverExpiresCheckbox.addEventListener("change", (event) => {
            const checkbox = event.currentTarget as HTMLInputElement;
            if (checkbox.checked) {
              this.userBanExpiresSettingsElement.style.setProperty("display", "none", "");
            } else {
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

export default BanDialog;

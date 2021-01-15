/**
 * Provides a dialog to copy an existing template group.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Template/Group/Copy
 */

import * as Ajax from "../../../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../../../Ajax/Data";
import { DialogCallbackObject, DialogCallbackSetup } from "../../../../Ui/Dialog/Data";
import * as Language from "../../../../Language";
import UiDialog from "../../../../Ui/Dialog";
import * as UiNotification from "../../../../Ui/Notification";
import DomUtil from "../../../../Dom/Util";

interface AjaxResponse {
  returnValues: {
    redirectURL: string;
  };
}

interface AjaxResponseError {
  returnValues?: {
    fieldName?: string;
    errorType?: string;
  };
}

class AcpUiTemplateGroupCopy implements AjaxCallbackObject, DialogCallbackObject {
  private folderName?: HTMLInputElement = undefined;
  private name?: HTMLInputElement = undefined;
  private readonly templateGroupId: number;

  constructor(templateGroupId: number) {
    this.templateGroupId = templateGroupId;

    const button = document.querySelector(".jsButtonCopy") as HTMLAnchorElement;
    button.addEventListener("click", (ev) => this.click(ev));
  }

  private click(event: MouseEvent): void {
    event.preventDefault();

    UiDialog.open(this);
  }

  _dialogSubmit(): void {
    Ajax.api(this, {
      parameters: {
        templateGroupName: this.name!.value,
        templateGroupFolderName: this.folderName!.value,
      },
    });
  }

  _ajaxSuccess(data: AjaxResponse): void {
    UiDialog.close(this);

    UiNotification.show(undefined, () => {
      window.location.href = data.returnValues.redirectURL;
    });
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "templateGroupCopy",
      options: {
        onSetup: () => {
          ["Name", "FolderName"].forEach((type) => {
            const input = document.getElementById("copyTemplateGroup" + type) as HTMLInputElement;
            input.value = (document.getElementById("templateGroup" + type) as HTMLInputElement).value;

            if (type === "Name") {
              this.name = input;
            } else {
              this.folderName = input;
            }
          });
        },
        title: Language.get("wcf.acp.template.group.copy"),
      },
      source: `<dl>
  <dt>
    <label for="copyTemplateGroupName">${Language.get("wcf.global.name")}</label>
  </dt>
  <dd>
    <input type="text" id="copyTemplateGroupName" class="long" data-dialog-submit-on-enter="true" required>
  </dd>
</dl>
<dl>
  <dt>
    <label for="copyTemplateGroupFolderName">${Language.get("wcf.acp.template.group.folderName")}</label>
  </dt>
  <dd>
    <input type="text" id="copyTemplateGroupFolderName" class="long" data-dialog-submit-on-enter="true" required>
  </dd>
</dl>
<div class="formSubmit">
  <button class="buttonPrimary" data-type="submit">${Language.get("wcf.global.button.submit")}</button>
</div>`,
    };
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "copy",
        className: "wcf\\data\\template\\group\\TemplateGroupAction",
        objectIDs: [this.templateGroupId],
      },
      failure: (data: AjaxResponseError) => {
        if (data && data.returnValues && data.returnValues.fieldName && data.returnValues.errorType) {
          if (data.returnValues.fieldName === "templateGroupName") {
            DomUtil.innerError(
              this.name!,
              Language.get(`wcf.acp.template.group.name.error.${data.returnValues.errorType}`),
            );
          } else {
            DomUtil.innerError(
              this.folderName!,
              Language.get(`wcf.acp.template.group.folderName.error.${data.returnValues.errorType}`),
            );
          }

          return false;
        }

        return true;
      },
    };
  }
}

let acpUiTemplateGroupCopy: AcpUiTemplateGroupCopy;

export function init(templateGroupId: number): void {
  if (!acpUiTemplateGroupCopy) {
    acpUiTemplateGroupCopy = new AcpUiTemplateGroupCopy(templateGroupId);
  }
}

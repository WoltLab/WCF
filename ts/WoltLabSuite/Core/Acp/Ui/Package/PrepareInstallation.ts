/**
 * Attempts to download the requested package from the file and prompts for the
 * authentication credentials on rejection.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Acp/Ui/Package/PrepareInstallation
 */

import * as Ajax from "../../../Ajax";
import { AjaxCallbackSetup } from "../../../Ajax/Data";
import * as Core from "../../../Core";
import { DialogCallbackSetup } from "../../../Ui/Dialog/Data";
import * as Language from "../../../Language";
import UiDialog from "../../../Ui/Dialog";
import DomUtil from "../../../Dom/Util";

interface AjaxResponse {
  returnValues: {
    queueID?: number;
    template?: string;
  };
}

class AcpUiPackagePrepareInstallation {
  private identifier = "";
  private version = "";

  start(identifier: string, version: string): void {
    this.identifier = identifier;
    this.version = version;

    this.prepare({});
  }

  private prepare(authData: ArbitraryObject): void {
    const packages = {};
    packages[this.identifier] = this.version;

    Ajax.api(this, {
      parameters: {
        authData: authData,
        packages: packages,
      },
    });
  }

  private submit(packageUpdateServerId: number): void {
    const usernameInput = document.getElementById("packageUpdateServerUsername") as HTMLInputElement;
    const passwordInput = document.getElementById("packageUpdateServerPassword") as HTMLInputElement;

    DomUtil.innerError(usernameInput, false);
    DomUtil.innerError(passwordInput, false);

    const username = usernameInput.value.trim();
    if (username === "") {
      DomUtil.innerError(usernameInput, Language.get("wcf.global.form.error.empty"));
    } else {
      const password = passwordInput.value.trim();
      if (password === "") {
        DomUtil.innerError(passwordInput, Language.get("wcf.global.form.error.empty"));
      } else {
        const saveCredentials = document.getElementById("packageUpdateServerSaveCredentials") as HTMLInputElement;

        this.prepare({
          packageUpdateServerID: packageUpdateServerId,
          password,
          saveCredentials: saveCredentials.checked,
          username,
        });
      }
    }
  }

  _ajaxSuccess(data: AjaxResponse): void {
    if (data.returnValues.queueID) {
      if (UiDialog.isOpen(this)) {
        UiDialog.close(this);
      }

      const installation = new window.WCF.ACP.Package.Installation(data.returnValues.queueID, undefined, false);
      installation.prepareInstallation();
    } else if (data.returnValues.template) {
      UiDialog.open(this, data.returnValues.template);
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "prepareInstallation",
        className: "wcf\\data\\package\\update\\PackageUpdateAction",
      },
    };
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "packageDownloadAuthorization",
      options: {
        onSetup: (content) => {
          const button = content.querySelector(".formSubmit > button") as HTMLButtonElement;
          button.addEventListener("click", (event) => {
            event.preventDefault();

            const packageUpdateServerId = ~~button.dataset.packageUpdateServerId!;
            this.submit(packageUpdateServerId);
          });
        },
        title: Language.get("wcf.acp.package.update.unauthorized"),
      },
      source: null,
    };
  }
}

Core.enableLegacyInheritance(AcpUiPackagePrepareInstallation);

export = AcpUiPackagePrepareInstallation;

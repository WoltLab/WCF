/**
 * Allows the selection of packages for an update and handles any possible
 * conflicts. Prompts for credentials if necessary.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2022 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Acp/Ui/Package/Update/Manager
 */

import { dboAction } from "../../../../Ajax";
import { DialogCallbackObject, DialogCallbackSetup } from "../../../../Ui/Dialog/Data";
import * as Language from "../../../../Language";
import * as UiDialog from "../../../../Ui/Dialog";
import DomUtil from "../../../../Dom/Util";

const enum ResponseType {
  AuthorizationRequired = "authorizationRequired",
  Conflict = "conflict",
  Queue = "queue",
}

type ResponsePrepareUpdate =
  | {
      template: string;
      type: ResponseType.AuthorizationRequired;
    }
  | {
      template: string;
      type: ResponseType.Conflict;
    }
  | {
      queueID: number;
      type: ResponseType.Queue;
    };

type AuthData = {
  packageUpdateServerID: number;
  password: string;
  saveCredentials: boolean;
  username: string;
};

type Payload = {
  authData?: AuthData;
  packages: Record<string, string>;
};

class AcpUiPackageUpdateManager implements DialogCallbackObject {
  private readonly packages = new Map<HTMLElement, HTMLInputElement | null>();
  private readonly submitButton: HTMLButtonElement;

  constructor() {
    this.submitButton = document.getElementById("packageUpdateSubmitButton") as HTMLButtonElement;

    this.submitButton.addEventListener("click", (event) => {
      event.preventDefault();

      void this.prepareUpdate();
    });

    this.initPackages();
  }

  private initPackages(): void {
    document.querySelectorAll(".packageUpdate").forEach((element: HTMLElement) => {
      const checkbox = element.querySelector<HTMLInputElement>('input[type="checkbox"]');
      checkbox?.addEventListener("change", () => this.updateSubmitButtonState());

      this.packages.set(element, checkbox);
    });
  }

  private updateSubmitButtonState(): void {
    let disableButton = true;
    this.packages.forEach((checkbox) => {
      if (checkbox === null || checkbox.checked) {
        disableButton = false;
      }
    });

    this.submitButton.disabled = disableButton;
  }

  private getSelectedPackages(): Record<string, string> {
    const packages = {};
    this.packages.forEach((checkbox, element) => {
      if (checkbox === null || checkbox.checked) {
        packages[element.dataset.package!] = element.dataset.version!;
      }
    });

    return packages;
  }

  private async prepareUpdate(authData?: AuthData): Promise<void> {
    const packages = this.getSelectedPackages();
    if (Object.keys(packages).length === 0) {
      return;
    }

    const payload: Payload = {
      packages,
    };

    if (authData) {
      payload.authData = authData;
    }

    let response: ResponsePrepareUpdate;
    try {
      this.submitButton.disabled = true;

      response = (await dboAction("prepareUpdate", "wcf\\data\\package\\update\\PackageUpdateAction")
        .payload(payload)
        .dispatch()) as ResponsePrepareUpdate;
    } finally {
      this.submitButton.disabled = false;
    }

    switch (response.type) {
      case ResponseType.AuthorizationRequired:
        this.promptCredentials(response.template);
        break;

      case ResponseType.Conflict:
        this.showConflict(response.template);
        break;

      case ResponseType.Queue:
        this.startInstallation(response.queueID);
        break;

      default:
        throw new Error("Unexpected response type");
    }
  }

  private promptCredentials(template: string): void {
    UiDialog.open(this, template);
    UiDialog.setTitle(this, Language.get("wcf.acp.package.update.unauthorized"));
  }

  private showConflict(template: string): void {
    UiDialog.open(this, template);
    UiDialog.setTitle(this, Language.get("wcf.acp.package.update.excludedPackages"));
  }

  private startInstallation(queueId: number): void {
    if (UiDialog.isOpen(this)) {
      UiDialog.close(this);
    }

    const installation = new window.WCF.ACP.Package.Installation(queueId, undefined, false, true);
    installation.prepareInstallation();
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "acpUiPackageUpdateManager",
      options: {
        title: Language.get("wcf.acp.package.update.title"),
      },
      source: null,
    };
  }

  _dialogSubmit(): void {
    let isValid = true;

    const usernameField = document.getElementById("packageUpdateServerUsername") as HTMLInputElement;
    const username = usernameField.value.trim();
    if (username === "") {
      DomUtil.innerError(usernameField, Language.get("wcf.global.form.error.empty"));
      isValid = false;
    } else {
      DomUtil.innerError(usernameField, false);
    }

    const passwordField = document.getElementById("packageUpdateServerPassword") as HTMLInputElement;
    const password = passwordField.value.trim();
    if (password === "") {
      DomUtil.innerError(passwordField, Language.get("wcf.global.form.error.empty"));
      isValid = false;
    } else {
      DomUtil.innerError(passwordField, false);
    }

    if (isValid) {
      const packageUpdateServerID = +this.submitButton.dataset.packageUpdateServerId!;
      const saveCredentials = (document.getElementById("packageUpdateServerSaveCredentials") as HTMLInputElement)
        .checked;

      void this.prepareUpdate({
        packageUpdateServerID,
        password,
        saveCredentials,
        username,
      });
    }
  }
}

let acpUiPackageUpdateManager: AcpUiPackageUpdateManager;
export function setup(): void {
  if (acpUiPackageUpdateManager === undefined) {
    acpUiPackageUpdateManager = new AcpUiPackageUpdateManager();
  }
}

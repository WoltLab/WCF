/**
 * Initiates a package installation based on the StoreCode provided in the
 * package installation screen.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { dboAction } from "../../../Ajax";
import * as AjaxStatus from "../../../Ajax/Status";
import { isPlainObject } from "../../../Core";
import * as Language from "../../../Language";
import { innerError } from "../../../Dom/Util";
import UiDialog from "../../../Ui/Dialog";
import { StatusNotOk } from "WoltLabSuite/Core/Ajax/Error";

let codeInput: HTMLInputElement;

type InstallationCode = {
  package: string;
  password: string;
  username: string;
};

type Response =
  | {
      queueID: number | null;
      type: "queue";
    }
  | {
      template: string;
      type: string;
    };

function detectCode(versionNumber: string): void {
  const value = codeInput.value.trim();
  if (value === "") {
    innerError(codeInput, false);
    return;
  }

  let isValid = false;
  if (value.startsWith("WoltLab_StoreCode_Do_Not_Share_")) {
    const decodedValue = window.atob(value.replace(/^WoltLab_StoreCode_Do_Not_Share_/, ""));

    let maybeJson: unknown;
    try {
      maybeJson = JSON.parse(decodedValue);
    } catch {
      // Skip invalid values.
    }

    if (isPlainObject(maybeJson)) {
      const json = maybeJson as InstallationCode;
      if (json.package && json.password && json.username) {
        isValid = true;

        void prepareInstallation(json, versionNumber);
      }
    }
  }

  if (isValid) {
    innerError(codeInput, false);
  } else {
    innerError(codeInput, Language.get("wcf.acp.package.quickInstallation.code.error.invalid"));
  }
}

let refreshedPackageDatabase: Promise<unknown> | undefined = undefined;
function refreshPackageDatabase() {
  if (refreshedPackageDatabase === undefined) {
    refreshedPackageDatabase = dboAction("refreshDatabase", "wcf\\data\\package\\update\\PackageUpdateAction")
      .disableLoadingIndicator()
      .dispatch();
  }

  return refreshedPackageDatabase;
}

async function prepareInstallation(data: InstallationCode, versionNumber: string): Promise<void> {
  try {
    AjaxStatus.show();
    await refreshPackageDatabase();
  } finally {
    AjaxStatus.hide();
  }

  let response: Response;
  try {
    response = (await dboAction("prepareInstallation", "wcf\\data\\package\\update\\PackageUpdateAction")
      .payload({
        packages: {
          [data.package]: "",
        },
        authData: {
          username: data.username,
          password: data.password,
          saveCredentials: false,
          isStoreCode: true,
        },
      })
      .dispatch()) as Response;
  } catch (e) {
    if (e instanceof StatusNotOk) {
      try {
        const json = await e.response.clone().json();
        if (typeof json.message === "string" && json.message.startsWith("Cannot find the package '")) {
          codeInput.value = "";
          innerError(
            codeInput,
            Language.getPhrase("wcf.acp.package.error.incompatibleStoreProduct", { versionNumber }),
          );

          return;
        }
      } catch {
        throw e;
      }
    }

    throw e;
  }

  if ("queueID" in response) {
    if (response.queueID === null) {
      codeInput.value = "";

      innerError(codeInput, Language.get("wcf.acp.package.error.uniqueAlreadyInstalled"));
    } else {
      const installation = new window.WCF.ACP.Package.Installation(response.queueID, undefined, false);
      installation.prepareInstallation();
    }
  } else if ("template" in response) {
    UiDialog.open(
      {
        _dialogSetup() {
          return {
            id: "quickInstallationError",
            options: {
              title: Language.get("wcf.global.error.title"),
            },
            source: null,
          };
        },
      },
      response.template,
    );
  } else {
    throw new Error("Unreachable");
  }
}

export function setup(versionNumber: string): void {
  codeInput = document.getElementById("quickInstallationCode") as HTMLInputElement;

  codeInput.addEventListener("focus", () => {
    // Refresh the package database when focusing the input to hide the latency of the package
    // server querying from the user, because the refresh runs, while the user is busy with
    // pasting the StoreCode into the input.
    void refreshPackageDatabase();
  });

  codeInput.addEventListener("input", () => {
    detectCode(versionNumber);
  });
}

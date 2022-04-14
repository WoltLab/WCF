/**
 * Initiates a package installation based on the StoreCode provided in the
 * package installation screen.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Acp/Ui/Package/QuickInstallation
 */

import { dboAction } from "../../../Ajax";
import { isPlainObject } from "../../../Core";
import * as Language from "../../../Language";
import { innerError } from "../../../Dom/Util";
import UiDialog from "../../../Ui/Dialog";

let codeInput: HTMLInputElement;

type InstallationCode = {
  package: string;
  password: string;
  username: string;
};

type Response =
  | {
      queueID: number;
      type: "queue";
    }
  | {
      template: string;
      type: string;
    };

function detectCode(): void {
  const value = codeInput.value.trim();

  let isValid = false;
  if (value.length > 0) {
    const decodedValue = window.atob(value);

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

        void prepareInstallation(json);
      }
    }
  }

  if (isValid) {
    innerError(codeInput, false);
  } else {
    innerError(codeInput, Language.get("wcf.acp.package.quickInstallation.code.error.invalid"));
  }
}

async function prepareInstallation(data: InstallationCode): Promise<void> {
  const response = (await dboAction("prepareInstallation", "wcf\\data\\package\\update\\PackageUpdateAction")
    .payload({
      packages: {
        [data.package]: "",
      },
      authData: {
        username: data.username,
        password: data.password,
        saveCredentials: false,
      },
    })
    .dispatch()) as Response;

  if ("queueID" in response) {
    const installation = new window.WCF.ACP.Package.Installation(response.queueID, undefined, false);
    installation.prepareInstallation();
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

export function setup(): void {
  codeInput = document.getElementById("quickInstallationCode") as HTMLInputElement;

  codeInput.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
      event.preventDefault();

      detectCode();
    }
  });

  codeInput.addEventListener("paste", (event) => {
    event.preventDefault();

    const value = event.clipboardData!.getData("text/plain");
    codeInput.value = value;

    detectCode();
  });
}

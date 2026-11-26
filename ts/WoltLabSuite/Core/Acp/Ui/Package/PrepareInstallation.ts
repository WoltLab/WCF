/**
 * Prepares the installation of a package and starts it. Prompts for the
 * authentication credentials on rejection and reports conflicting packages.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

import { dboAction } from "WoltLabSuite/Core/Ajax";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import DomUtil from "WoltLabSuite/Core/Dom/Util";
import type WoltlabCoreDialogElement from "WoltLabSuite/Core/Element/woltlab-core-dialog";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { startInstallation } from "WoltLabSuite/Core/Acp/Component/Package/Installation";

const enum ResponseType {
  AuthorizationRequired = "authorizationRequired",
  Conflict = "conflict",
  Queue = "queue",
}

type ResponsePrepareInstallation =
  | {
      template: string;
      type: ResponseType.AuthorizationRequired;
    }
  | {
      template: string;
      type: ResponseType.Conflict;
    }
  | {
      queueID: number | null;
      type: ResponseType.Queue;
    };

type AuthData = {
  packageUpdateServerID: number;
  password: string;
  saveCredentials: boolean;
  username: string;
};

type Credentials = {
  authData: AuthData;
  dialog: WoltlabCoreDialogElement;
};

type RedirectLocation = "license";

class AcpUiPackagePrepareInstallation {
  #pending = false;

  async start(identifier: string, version: string, redirectLocation?: RedirectLocation): Promise<void> {
    await this.#install({ [identifier]: version }, redirectLocation);
  }

  /**
   * Resolves with `true` once the installation has been started.
   */
  async #install(packages: Record<string, string>, redirectLocation?: RedirectLocation): Promise<boolean> {
    if (this.#pending) {
      throw new Error("There is already a pending installation.");
    }

    this.#pending = true;

    let authData: AuthData | undefined = undefined;
    let credentialsDialog: WoltlabCoreDialogElement | undefined = undefined;
    try {
      while (true) {
        let response: ResponsePrepareInstallation | undefined;
        try {
          response = await this.#prepare(packages, authData);
        } finally {
          credentialsDialog?.close();
          credentialsDialog = undefined;
        }

        // The request was aborted by leaving the page.
        if (response === undefined) {
          return false;
        }

        switch (response.type) {
          case ResponseType.AuthorizationRequired: {
            const credentials = await this.#promptCredentials(response.template);
            if (credentials === undefined) {
              return false;
            }

            authData = credentials.authData;
            credentialsDialog = credentials.dialog;
            break;
          }

          case ResponseType.Conflict:
            await this.#showConflict(response.template);
            return false;

          case ResponseType.Queue:
            if (response.queueID === null) {
              return false;
            }

            void startInstallation(response.queueID, { redirectLocation });
            return true;

          default:
            throw new Error("Unexpected response type");
        }
      }
    } finally {
      this.#pending = false;
    }
  }

  async #prepare(
    packages: Record<string, string>,
    authData: AuthData | undefined,
  ): Promise<ResponsePrepareInstallation | undefined> {
    const payload: Record<string, unknown> = { packages };
    if (authData !== undefined) {
      payload.authData = authData;
    }

    return (await dboAction("prepareInstallation", "wcf\\data\\package\\update\\PackageUpdateAction")
      .payload(payload)
      .dispatch()) as ResponsePrepareInstallation | undefined;
  }

  /**
   * Resolves with the submitted credentials and their dialog, which stays open
   * until the caller closes it, or with `undefined` if the dialog was closed.
   */
  #promptCredentials(template: string): Promise<Credentials | undefined> {
    const dialog = dialogFactory().fromHtml(template).withoutControls();

    return new Promise((resolve) => {
      dialog.addEventListener("afterClose", () => resolve(undefined));

      // The templates for a paid upgrade or a required purchase do not offer a form.
      const submitButton = dialog.content.querySelector<HTMLButtonElement>('.formSubmit > button[data-type="submit"]');
      submitButton?.addEventListener("click", () => {
        const authData = this.#getAuthData(dialog, parseInt(submitButton.dataset.packageUpdateServerId!, 10));
        if (authData === undefined) {
          return;
        }

        submitButton.disabled = true;
        dialog.addEventListener("close", (event) => event.preventDefault());

        resolve({ authData, dialog });
      });

      dialog.show(getPhrase("wcf.acp.package.update.unauthorized"));
    });
  }

  #getAuthData(dialog: WoltlabCoreDialogElement, packageUpdateServerID: number): AuthData | undefined {
    const usernameInput = dialog.content.querySelector<HTMLInputElement>("#packageUpdateServerUsername")!;
    const passwordInput = dialog.content.querySelector<HTMLInputElement>("#packageUpdateServerPassword")!;
    const saveCredentials = dialog.content.querySelector<HTMLInputElement>("#packageUpdateServerSaveCredentials")!;

    DomUtil.innerError(usernameInput, false);
    DomUtil.innerError(passwordInput, false);

    const username = usernameInput.value.trim();
    if (username === "") {
      DomUtil.innerError(usernameInput, getPhrase("wcf.global.form.error.empty"));
      return undefined;
    }

    const password = passwordInput.value.trim();
    if (password === "") {
      DomUtil.innerError(passwordInput, getPhrase("wcf.global.form.error.empty"));
      return undefined;
    }

    return {
      packageUpdateServerID,
      password,
      saveCredentials: saveCredentials.checked,
      username,
    };
  }

  #showConflict(template: string): Promise<void> {
    const dialog = dialogFactory().fromHtml(template).asAlert();

    return new Promise((resolve) => {
      dialog.addEventListener("afterClose", () => resolve());

      dialog.show(getPhrase("wcf.acp.package.update.excludedPackages"));
    });
  }
}

export = AcpUiPackagePrepareInstallation;

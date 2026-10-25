/**
 * Processes the installation, update or uninstallation of packages step by step.
 *
 * @author Alexander Ebert
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */

import { prepareRequest } from "WoltLabSuite/Core/Ajax/Backend";
import { confirmationFactory } from "WoltLabSuite/Core/Component/Confirmation";
import { dialogFactory } from "WoltLabSuite/Core/Component/Dialog";
import type WoltlabCoreDialogElement from "WoltLabSuite/Core/Element/woltlab-core-dialog";
import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { getPhrase } from "WoltLabSuite/Core/Language";

type ActionName = "DevtoolsInstallPackage" | "InstallPackage" | "UninstallPackage";

type Response = {
  currentAction?: string;
  innerTemplate?: string;
  node?: string;
  packageID?: number;
  progress?: number;
  queueID?: number | null;
  redirectLocation?: string;
  step: "install" | "rollback" | "success" | "uninstall";
  template?: string;
};

type Parameters = Record<string, number | string>;

type InstallationOptions = {
  allowRollback: boolean;
  isUpdate: boolean;
  redirectLocation: "license";
};

declare global {
  interface Window {
    // Used internally for automation purposes.
    _trackPackageStep?: (actionName: ActionName, data: Response) => void;
  }
}

const controllers: Record<ActionName, string> = {
  DevtoolsInstallPackage: "devtools-install-package",
  InstallPackage: "install-package",
  UninstallPackage: "uninstall-package",
};

class PackageInstallation {
  readonly #actionName: ActionName;
  readonly #additionalParameters: Parameters;
  readonly #allowRollback: boolean;
  #dialog?: WoltlabCoreDialogElement;
  #queueId = 0;
  readonly #title: string;

  constructor(actionName: ActionName, title: string, allowRollback: boolean, additionalParameters: Parameters) {
    this.#actionName = actionName;
    this.#additionalParameters = additionalParameters;
    this.#allowRollback = allowRollback;
    this.#title = title;
  }

  async start(parameters: Parameters): Promise<void> {
    await this.#run({ ...parameters, step: "prepare" });
  }

  async #run(parameters: Parameters, formData?: FormData): Promise<void> {
    let nextParameters: Parameters | undefined = parameters;
    while (nextParameters !== undefined) {
      const response = await this.#request(nextParameters, formData);
      if (response === undefined) {
        return;
      }

      formData = undefined;
      nextParameters = this.#handleResponse(response);
    }
  }

  async #request(parameters: Parameters, formData?: FormData): Promise<Response | undefined> {
    const query = new URLSearchParams(
      Object.entries(parameters).map(([key, value]) => [key, value.toString()]),
    ).toString();

    // The controller name must not be encoded, which `URL.searchParams` would do.
    const request = prepareRequest(`${window.WSC_API_URL}index.php?${controllers[this.#actionName]}/&${query}`).post(
      formData,
    );

    // The dialog shows the progress once it is open.
    if (this.#dialog !== undefined) {
      request.disableLoadingIndicator();
    }

    try {
      return (await request.fetchAsJson()) as Response | undefined;
    } catch (error) {
      this.#failure();

      throw error;
    }
  }

  #handleResponse(data: Response): Parameters | undefined {
    if (typeof window._trackPackageStep === "function") {
      window._trackPackageStep(this.#actionName, data);
    }

    const dialog = this.#getDialog(data);
    this.#setIcon("spinner");

    if (data.step === "rollback") {
      dialog.close();

      void startUninstallation(data.packageID!);

      return undefined;
    }

    if (data.queueID) {
      this.#queueId = data.queueID;
    }

    const progress = dialog.content.querySelector<HTMLProgressElement>("#packageInstallationProgress")!;
    if (data.progress !== undefined) {
      progress.value = data.progress;
      progress.textContent = `${data.progress}%`;
      dialog.content.querySelector("#packageInstallationProgressLabel")!.textContent = `${data.progress}%`;
    }

    if (data.currentAction) {
      dialog.content.querySelector("#packageInstallationAction")!.innerHTML = data.currentAction;
    }

    if (data.step === "success") {
      this.#setIcon("check");

      this.#clearInnerContent();
      const button = this.#appendButton(getPhrase("wcf.global.button.next"), () => {
        window.location.href = data.redirectLocation!;
      });
      this.#showInnerContent();
      button.focus();

      return undefined;
    }

    if (data.innerTemplate !== undefined) {
      this.#showForm(data.innerTemplate, data.step, data.node!);

      return undefined;
    }

    this.#clearInnerContent();

    if (data.node) {
      return this.#getStepParameters(data.step, data.node);
    }

    return undefined;
  }

  #getDialog(data: Response): WoltlabCoreDialogElement {
    if (this.#dialog === undefined) {
      this.#dialog = dialogFactory().fromHtml(data.template!).withoutControls();

      // The installation cannot be interrupted, the role suppresses the close button.
      this.#dialog.dialog.setAttribute("role", "alertdialog");
      this.#dialog.addEventListener("close", (event) => {
        event.preventDefault();
      });

      this.#dialog.show(getPhrase(this.#title));
    }

    return this.#dialog;
  }

  #showForm(html: string, step: string, node: string): void {
    const content = this.#getInnerContent();
    content.innerHTML = html;

    const submit = () => {
      button.disabled = true;
      this.#setIcon("spinner");

      void this.#run(this.#getStepParameters(step, node), this.#getFormData(content));
    };

    content.querySelectorAll("input").forEach((input) => {
      input.addEventListener("keyup", (event) => {
        if (event.key === "Enter" && !button.disabled) {
          submit();
        }
      });
    });

    this.#dialog!.content.querySelector("#packageInstallationProgress")!.removeAttribute("value");
    this.#setIcon("question");

    const button = this.#appendButton(getPhrase("wcf.global.button.next"), () => submit());
    this.#showInnerContent();
  }

  #getFormData(content: HTMLElement): FormData {
    const formData = new FormData();
    content.querySelectorAll("input").forEach((input) => {
      if ((input.type !== "checkbox" && input.type !== "radio") || input.checked) {
        formData.append(input.name, input.value);
      }
    });

    return formData;
  }

  #failure(): void {
    if (this.#dialog === undefined) {
      return;
    }

    this.#dialog.content.querySelector("#packageInstallationProgress")!.removeAttribute("value");
    this.#setIcon("xmark");

    if (!this.#allowRollback) {
      return;
    }

    this.#clearInnerContent();
    this.#appendButton(getPhrase("wcf.acp.package.installation.rollback"), () => {
      this.#setIcon("spinner");

      void this.#run(this.#getStepParameters("rollback"));
    });
    this.#showInnerContent();
  }

  #getStepParameters(step: string, node?: string): Parameters {
    const parameters: Parameters = {
      ...this.#additionalParameters,
      queueID: this.#queueId,
      step,
    };
    if (node !== undefined) {
      parameters.node = node;
    }

    return parameters;
  }

  #appendButton(label: string, callback: () => void): HTMLButtonElement {
    const button = document.createElement("button");
    button.type = "button";
    button.classList.add("button", "buttonPrimary");
    button.textContent = label;
    button.addEventListener("click", () => {
      button.disabled = true;

      callback();
    });

    const formSubmit = document.createElement("div");
    formSubmit.classList.add("formSubmit");
    formSubmit.append(button);

    this.#getInnerContent().append(formSubmit);

    return button;
  }

  #getInnerContent(): HTMLElement {
    return this.#dialog!.content.querySelector("#packageInstallationInnerContent")!;
  }

  #clearInnerContent(): void {
    this.#dialog!.content.querySelector<HTMLElement>("#packageInstallationInnerContentContainer")!.hidden = true;
    this.#getInnerContent().innerHTML = "";
  }

  #showInnerContent(): void {
    this.#dialog!.content.querySelector<HTMLElement>("#packageInstallationInnerContentContainer")!.hidden = false;
  }

  #setIcon(name: string): void {
    this.#dialog?.content.querySelector<FaIcon>(".jsPackageInstallationStatus fa-icon")?.setIcon(name);
  }
}

/**
 * Installs or updates the packages of the queue.
 */
export async function startInstallation(queueId: number, options: Partial<InstallationOptions> = {}): Promise<void> {
  const additionalParameters: Parameters = {};
  if (options.redirectLocation !== undefined) {
    additionalParameters.redirectLocation = options.redirectLocation;
  }

  const installation = new PackageInstallation(
    "InstallPackage",
    options.isUpdate ? "wcf.acp.package.update.title" : "wcf.acp.package.install.title",
    options.allowRollback === true,
    additionalParameters,
  );

  await installation.start({ ...additionalParameters, queueID: queueId });
}

/**
 * Installs a devtools project as a package.
 */
export async function startDevtoolsInstallation(queueId: number, projectId: number): Promise<void> {
  const additionalParameters: Parameters = { projectID: projectId };
  const installation = new PackageInstallation(
    "DevtoolsInstallPackage",
    "wcf.acp.package.install.title",
    false,
    additionalParameters,
  );

  await installation.start({ ...additionalParameters, queueID: queueId });
}

/**
 * Uninstalls the package.
 */
export async function startUninstallation(packageId: number): Promise<void> {
  const uninstallation = new PackageInstallation("UninstallPackage", "wcf.acp.package.uninstallation.title", false, {});

  await uninstallation.start({ packageID: packageId });
}

/**
 * Handles the buttons on the confirmation page of a package installation.
 */
export function setupConfirmation(
  queueId: number,
  cancelEndpoint: string,
  options: Pick<InstallationOptions, "allowRollback" | "isUpdate">,
): void {
  const submitButton = document.getElementById("submitButton") as HTMLInputElement | null;
  submitButton?.addEventListener("click", () => {
    submitButton.disabled = true;

    void startInstallation(queueId, options);
  });

  const backButton = document.getElementById("backButton") as HTMLInputElement;
  backButton.addEventListener("click", () => {
    backButton.disabled = true;

    void prepareRequest(cancelEndpoint)
      .post()
      .fetchAsJson()
      .then((response) => {
        window.location.href = (response as { redirectUrl: string }).redirectUrl;
      });
  });
}

/**
 * Handles the buttons to uninstall a package.
 */
export function setupUninstallButtons(): void {
  wheneverFirstSeen(".jsUninstallButton", (button: HTMLButtonElement) => {
    button.addEventListener("click", () => {
      // The message is a phrase that contains HTML.
      const message = new DOMParser().parseFromString(button.dataset.confirmMessage!, "text/html");

      void confirmationFactory()
        .custom(message.body.textContent)
        .withoutMessage()
        .then((result) => {
          if (result) {
            void startUninstallation(parseInt(button.dataset.objectId!, 10));
          }
        });
    });
  });
}

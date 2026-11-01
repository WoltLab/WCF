import * as Ajax from "../../../../Ajax";
import { getPhrase } from "WoltLabSuite/Core/Language";
import { AjaxCallbackSetup, AjaxResponseException } from "../../../../Ajax/Data";
import { DialogCallbackSetup } from "../../../../Ui/Dialog/Data";
import { dialogFactory } from "../../../../Component/Dialog";
import { showDefaultSuccessSnackbar } from "WoltLabSuite/Core/Component/Snackbar";
import { syncVersion } from "WoltLabSuite/Core/Api/Devtools/Projects/SyncVersion";

type PendingPip = [string, string];

interface AjaxResponse {
  returnValues: {
    invokeAgain: boolean;
    pluginName: string;
    target: string;
    timeElapsed: string;
  };
}

interface RequestData {
  parameters: {
    allowUnsafe: boolean;
    pluginName: string;
    target: string;
  };
}

class AcpUiDevtoolsProjectSync {
  private readonly buttons = new Map<string, HTMLButtonElement>();
  private readonly buttonStatus = new Map<string, HTMLElement>();
  private readonly buttonSyncAll: HTMLButtonElement = document.getElementById("devtoolsSyncAll") as HTMLButtonElement;
  private readonly container = document.getElementById("syncPipMatches")!;
  readonly #fullSyncQueue: PendingPip[];
  private readonly projectId: number;
  private queue: PendingPip[] = [];
  #allowUnsafe = false;
  #syncVersionAfterCompletion = false;

  constructor(projectId: number, fullSyncQueue: PendingPip[]) {
    this.projectId = projectId;
    this.#fullSyncQueue = fullSyncQueue;

    const restrictedSync = document.getElementById("syncShowOnlyMatches") as HTMLInputElement;
    restrictedSync.addEventListener("change", () => {
      this.container.classList.toggle("jsShowOnlyMatches");
    });

    const showUnsafePips = document.getElementById("syncShowUnsafePips") as HTMLInputElement;
    const updateUnsafePips = () => {
      this.#allowUnsafe = showUnsafePips.checked;
      this.container.classList.toggle("jsHideUnsafePips", !showUnsafePips.checked);
    };
    showUnsafePips.addEventListener("change", () => updateUnsafePips());
    // Browsers may restore the checked state on reload.
    updateUnsafePips();

    this.container
      .querySelectorAll(".jsHasPipTargets:not(.jsSkipTargetDetection)")
      .forEach((pip: HTMLTableRowElement) => {
        const pluginName = pip.dataset.pluginName!;

        this.container
          .querySelectorAll(`.jsHasPipTargets[data-plugin-name="${pluginName}"] .jsInvokePip`)
          .forEach((button: HTMLButtonElement) => {
            const target = button.dataset.target!;

            button.addEventListener("click", (event) => {
              event.preventDefault();

              if (this.queue.length > 0) {
                return;
              }

              this.sync(pluginName, target);
            });

            const identifier = this.getButtonIdentifier(pluginName, target);
            this.buttons.set(identifier, button);
            this.buttonStatus.set(
              identifier,
              this.container.querySelector(
                `.jsHasPipTargets[data-plugin-name="${pluginName}"] .jsInvokePipResult[data-target="${target}"]`,
              ) as HTMLElement,
            );
          });
      });

    this.buttonSyncAll.addEventListener("click", () => this.syncAll());
  }

  private sync(pluginName: string, target: string): void {
    const identifier = this.getButtonIdentifier(pluginName, target);
    this.buttons.get(identifier)!.disabled = true;
    this.buttonStatus.get(identifier)!.innerHTML = '<fa-icon name="spinner" solid></fa-icon>';

    Ajax.api(this, {
      parameters: {
        allowUnsafe: this.#allowUnsafe,
        pluginName,
        target,
      },
    });
  }

  private syncAll(): void {
    if (this.buttonSyncAll.classList.contains("disabled")) {
      return;
    }

    this.buttonSyncAll.classList.add("disabled");
    this.#syncVersionAfterCompletion = true;

    this.queue = [...this.#fullSyncQueue];
    this.syncNext();
  }

  private syncNext(): void {
    if (this.queue.length === 0) {
      const showSuccess = () => {
        this.buttonSyncAll.classList.remove("disabled");

        showDefaultSuccessSnackbar();
      };

      if (this.#syncVersionAfterCompletion) {
        void this.#syncPackageVersion()
          .then(() => {
            showSuccess();
          })
          .finally(() => {
            this.#syncVersionAfterCompletion = false;
          });
      } else {
        showSuccess();
      }

      return;
    }

    const next = this.queue.shift()!;
    this.sync(next[0], next[1]);
  }

  private getButtonIdentifier(pluginName: string, target: string): string {
    return `${pluginName}-${target}`;
  }

  async #syncPackageVersion(): Promise<void> {
    await syncVersion(this.projectId);
  }

  _ajaxSuccess(data: AjaxResponse): void {
    const identifier = this.getButtonIdentifier(data.returnValues.pluginName, data.returnValues.target);
    this.buttons.get(identifier)!.disabled = false;
    this.buttonStatus.get(identifier)!.innerHTML = data.returnValues.timeElapsed;

    if (data.returnValues.invokeAgain) {
      this.sync(data.returnValues.pluginName, data.returnValues.target);
    } else {
      this.syncNext();
    }
  }

  _ajaxFailure(
    data: AjaxResponseException,
    responseText: string,
    xhr: XMLHttpRequest,
    requestData: RequestData,
  ): boolean {
    const identifier = this.getButtonIdentifier(requestData.parameters.pluginName, requestData.parameters.target);
    this.buttons.get(identifier)!.disabled = false;

    const buttonStatus = this.buttonStatus.get(identifier)!;
    buttonStatus.innerHTML = '<a href="#">' + getPhrase("wcf.acp.devtools.sync.status.failure") + "</a>";
    buttonStatus.children[0].addEventListener("click", (event) => {
      event.preventDefault();

      const html = Ajax.getRequestObject(this).getErrorHtml(data, xhr);
      if (html instanceof HTMLIFrameElement) {
        const dialog = dialogFactory()
          .fromHtml(`<div class="dialog__iframeContainer">${html.outerHTML}</div>`)
          .asAlert();
        dialog.show(getPhrase("wcf.global.error.title"));
        dialog.querySelector("dialog")!.classList.add("dialog--iframe");
      } else if (html) {
        const dialog = dialogFactory().fromHtml(html).asAlert();
        dialog.show(getPhrase("wcf.global.error.title"));
      }
    });

    this.buttonSyncAll.classList.remove("disabled");

    return true;
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "invoke",
        className: "wcf\\data\\package\\installation\\plugin\\PackageInstallationPluginAction",
        parameters: {
          projectID: this.projectId,
        },
      },
    };
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "devtoolsProjectSyncPipError",
      options: {
        title: getPhrase("wcf.global.error.title"),
      },
      source: null,
    };
  }
}

let acpUiDevtoolsProjectSync: AcpUiDevtoolsProjectSync;

export function init(projectId: number, fullSyncQueue: PendingPip[]): void {
  if (!acpUiDevtoolsProjectSync) {
    acpUiDevtoolsProjectSync = new AcpUiDevtoolsProjectSync(projectId, fullSyncQueue);
  }
}

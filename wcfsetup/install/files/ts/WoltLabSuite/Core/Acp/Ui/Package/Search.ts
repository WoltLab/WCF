/**
 * Search interface for the package server lists.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Acp/Ui/Package/Search
 */

import AcpUiPackagePrepareInstallation from "./PrepareInstallation";
import * as Ajax from "../../../Ajax";
import AjaxRequest from "../../../Ajax/Request";
import { AjaxCallbackObject, AjaxCallbackSetup } from "../../../Ajax/Data";
import * as Core from "../../../Core";

interface AjaxResponse {
  actionName: string;
  returnValues: {
    count: number;
    template: string;
  };
}

interface SearchOptions {
  delay: number;
  minLength: number;
}

class AcpUiPackageSearch implements AjaxCallbackObject {
  private readonly input: HTMLInputElement;
  private readonly installation: AcpUiPackagePrepareInstallation;
  private isBusy = false;
  private isFirstRequest = true;
  private lastValue = "";
  private options: SearchOptions;
  private request?: AjaxRequest = undefined;
  private readonly resultList: HTMLElement;
  private readonly resultListContainer: HTMLElement;
  private readonly resultCounter: HTMLElement;
  private timerDelay?: number = undefined;

  constructor() {
    this.input = document.getElementById("packageSearchInput") as HTMLInputElement;
    this.installation = new AcpUiPackagePrepareInstallation();
    this.options = {
      delay: 300,
      minLength: 3,
    };
    this.resultList = document.getElementById("packageSearchResultList")!;
    this.resultListContainer = document.getElementById("packageSearchResultContainer")!;
    this.resultCounter = document.getElementById("packageSearchResultCounter")!;

    this.input.addEventListener("keyup", () => this.keyup());
  }

  private keyup(): void {
    const value = this.input.value.trim();
    if (this.lastValue === value) {
      return;
    }

    this.lastValue = value;

    if (value.length < this.options.minLength) {
      this.setStatus("idle");
      return;
    }

    if (this.isFirstRequest) {
      if (!this.isBusy) {
        this.isBusy = true;

        this.setStatus("refreshDatabase");

        Ajax.api(this, {
          actionName: "refreshDatabase",
        });
      }

      return;
    }

    if (this.timerDelay !== null) {
      window.clearTimeout(this.timerDelay);
    }

    this.timerDelay = window.setTimeout(() => {
      this.setStatus("loading");
      this.search(value);
    }, this.options.delay);
  }

  private search(value: string): void {
    if (this.request) {
      this.request.abortPrevious();
    }

    this.request = Ajax.api(this, {
      parameters: {
        searchString: value,
      },
    });
  }

  private setStatus(status: string): void {
    this.resultListContainer.dataset.status = status;
  }

  _ajaxSuccess(data: AjaxResponse): void {
    switch (data.actionName) {
      case "refreshDatabase":
        this.isFirstRequest = false;

        this.lastValue = "";
        this.keyup();
        break;

      case "search":
        if (data.returnValues.count > 0) {
          this.resultList.innerHTML = data.returnValues.template;
          this.resultCounter.textContent = data.returnValues.count.toString();

          this.setStatus("showResults");

          this.resultList.querySelectorAll(".jsInstallPackage").forEach((button: HTMLAnchorElement) => {
            button.addEventListener("click", (event) => {
              event.preventDefault();
              button.blur();

              this.installation.start(button.dataset.package!, button.dataset.packageVersion!);
            });
          });
        } else {
          this.setStatus("noResults");
        }
        break;
    }
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "search",
        className: "wcf\\data\\package\\update\\PackageUpdateAction",
      },
      silent: true,
    };
  }
}

Core.enableLegacyInheritance(AcpUiPackageSearch);

export = AcpUiPackageSearch;

/**
 * Automatic URL rewrite support testing.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Option/RewriteTest
 */

import AjaxRequest from "../../../Ajax/Request";
import * as Language from "../../../Language";
import UiDialog from "../../../Ui/Dialog";
import { DialogCallbackSetup } from "../../../Ui/Dialog/Data";
import DomUtil from "../../../Dom/Util";

interface TestResult {
  app: string;
  pass: boolean;
}

class RewriteTest {
  private readonly apps: Map<string, string>;
  private readonly buttonStartTest = document.getElementById("rewriteTestStart") as HTMLAnchorElement;
  private readonly callbackChange: (ev: MouseEvent) => void;
  private passed = false;
  private readonly urlOmitIndexPhp: HTMLInputElement;

  /**
   * Initializes the rewrite test, but aborts early if URL rewriting was
   * enabled at page init.
   */
  constructor(apps: Map<string, string>) {
    const urlOmitIndexPhp = document.getElementById("url_omit_index_php") as HTMLInputElement;

    // This configuration part is unavailable when running in enterprise mode.
    if (urlOmitIndexPhp === null) {
      return;
    }

    this.urlOmitIndexPhp = urlOmitIndexPhp;
    if (this.urlOmitIndexPhp.checked) {
      // option is already enabled, ignore it
      return;
    }

    this.callbackChange = (ev) => this.onChange(ev);
    this.urlOmitIndexPhp.addEventListener("change", this.callbackChange);
    this.apps = apps;
  }

  /**
   * Forces the rewrite test when attempting to enable the URL rewriting.
   */
  private onChange(event: Event): void {
    event.preventDefault();

    UiDialog.open(this);
  }

  /**
   * Runs the actual rewrite test.
   */
  private async runTest(event?: MouseEvent): Promise<void> {
    if (event instanceof Event) {
      event.preventDefault();
    }

    if (this.buttonStartTest.classList.contains("disabled")) {
      return;
    }

    this.buttonStartTest.classList.add("disabled");
    this.setStatus("running");

    const tests: Promise<TestResult>[] = Array.from(this.apps).map(([app, url]) => {
      return new Promise((resolve, reject) => {
        const request = new AjaxRequest({
          ignoreError: true,
          // bypass the LinkHandler, because rewrites aren't enabled yet
          url: url,
          type: "GET",
          includeRequestedWith: false,
          success: (data) => {
            if (
              !Object.prototype.hasOwnProperty.call(data, "core_rewrite_test") ||
              data.core_rewrite_test !== "passed"
            ) {
              reject({ app, pass: false });
            } else {
              resolve({ app, pass: true });
            }
          },
          failure: () => {
            reject({ app, pass: false });

            return true;
          },
        });

        request.sendRequest(false);
      });
    });

    const results: TestResult[] = await Promise.all(tests.map((test) => test.catch((result) => result)));

    const passed = !results.some((result) => !result.pass);

    // Delay the status update to prevent UI flicker.
    await new Promise((resolve) => window.setTimeout(resolve, 500));

    if (passed) {
      this.passed = true;

      this.setStatus("success");

      this.urlOmitIndexPhp.removeEventListener("change", this.callbackChange);

      await new Promise((resolve) => window.setTimeout(resolve, 1000));

      if (UiDialog.isOpen(this)) {
        UiDialog.close(this);
      }
    } else {
      this.buttonStartTest.classList.remove("disabled");

      const testFailureResults = document.getElementById("dialogRewriteTestFailureResults")!;
      testFailureResults.innerHTML = results
        .map((result) => {
          return `<li><span class="badge label ${result.pass ? "green" : "red"}">${Language.get(
            "wcf.acp.option.url_omit_index_php.test.status." + (result.pass ? "success" : "failure"),
          )}</span> ${result.app}</li>`;
        })
        .join("");

      this.setStatus("failure");
    }
  }

  /**
   * Displays the appropriate dialog message.
   */
  private setStatus(status: string): void {
    const containers = [
      document.getElementById("dialogRewriteTestRunning")!,
      document.getElementById("dialogRewriteTestSuccess")!,
      document.getElementById("dialogRewriteTestFailure")!,
    ];

    containers.forEach((element) => DomUtil.hide(element));

    let i = 0;
    if (status === "success") {
      i = 1;
    } else if (status === "failure") {
      i = 2;
    }

    DomUtil.show(containers[i]);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "dialogRewriteTest",
      options: {
        onClose: () => {
          if (!this.passed) {
            const urlOmitIndexPhpNo = document.getElementById("url_omit_index_php_no") as HTMLInputElement;
            urlOmitIndexPhpNo.checked = true;
          }
        },
        onSetup: () => {
          this.buttonStartTest.addEventListener("click", (ev) => {
            void this.runTest(ev);
          });
        },
        onShow: () => this.runTest(),
        title: Language.get("wcf.acp.option.url_omit_index_php"),
      },
    };
  }
}

let rewriteTest: RewriteTest;

export function init(apps: Map<string, string>): void {
  if (!rewriteTest) {
    rewriteTest = new RewriteTest(apps);
  }
}

/**
 * Automatic URL rewrite rule generation.
 *
 * @author  Florian Gail
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Option/RewriteGenerator
 */

import * as Ajax from "../../../Ajax";
import { AjaxCallbackObject, AjaxCallbackSetup, ResponseData } from "../../../Ajax/Data";
import { DialogCallbackObject, DialogCallbackSetup } from "../../../Ui/Dialog/Data";
import * as Language from "../../../Language";
import UiDialog from "../../../Ui/Dialog";

class RewriteGenerator implements AjaxCallbackObject, DialogCallbackObject {
  private readonly buttonGenerate: HTMLAnchorElement;
  private readonly container: HTMLDListElement;

  /**
   * Initializes the generator for rewrite rules
   */
  constructor() {
    const urlOmitIndexPhp = document.getElementById("url_omit_index_php");

    // This configuration part is unavailable when running in enterprise mode.
    if (urlOmitIndexPhp === null) {
      return;
    }

    this.container = document.createElement("dl");
    const dt = document.createElement("dt");
    dt.classList.add("jsOnly");
    const dd = document.createElement("dd");

    this.buttonGenerate = document.createElement("a");
    this.buttonGenerate.className = "button";
    this.buttonGenerate.href = "#";
    this.buttonGenerate.textContent = Language.get("wcf.acp.rewrite.generate");
    this.buttonGenerate.addEventListener("click", (ev) => this._onClick(ev));
    dd.appendChild(this.buttonGenerate);

    const description = document.createElement("small");
    description.textContent = Language.get("wcf.acp.rewrite.description");
    dd.appendChild(description);

    this.container.appendChild(dt);
    this.container.appendChild(dd);

    const insertAfter = urlOmitIndexPhp.closest("dl")!;
    insertAfter.insertAdjacentElement("afterend", this.container);
  }

  /**
   * Fires an AJAX request and opens the dialog
   */
  _onClick(event: MouseEvent): void {
    event.preventDefault();

    Ajax.api(this);
  }

  _dialogSetup(): ReturnType<DialogCallbackSetup> {
    return {
      id: "dialogRewriteRules",
      source: null,
      options: {
        title: Language.get("wcf.acp.rewrite"),
      },
    };
  }

  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "generateRewriteRules",
        className: "wcf\\data\\option\\OptionAction",
      },
    };
  }

  _ajaxSuccess(data: ResponseData): void {
    UiDialog.open(this, data.returnValues);
  }
}

let rewriteGenerator: RewriteGenerator;

export function init(): void {
  if (!rewriteGenerator) {
    rewriteGenerator = new RewriteGenerator();
  }
}

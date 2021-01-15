/**
 * Handles the JavaScript part of the devtools project pip entry list.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Devtools/Project/Pip/Entry/List
 */

import * as Ajax from "../../../../../../Ajax";
import * as Core from "../../../../../../Core";
import * as Language from "../../../../../../Language";
import { ConfirmationCallbackParameters, show as showConfirmation } from "../../../../../../Ui/Confirmation";
import * as UiNotification from "../../../../../../Ui/Notification";
import { AjaxCallbackSetup } from "../../../../../../Ajax/Data";

interface AjaxResponse {
  returnValues: {
    identifier: string;
  };
}

class DevtoolsProjectPipEntryList {
  private readonly entryType: string;
  private readonly pip: string;
  private readonly projectId: number;
  private readonly supportsDeleteInstruction: boolean;
  private readonly table: HTMLTableElement;

  /**
   * Initializes the devtools project pip entry list handler.
   */
  constructor(tableId: string, projectId: number, pip: string, entryType: string, supportsDeleteInstruction: boolean) {
    const table = document.getElementById(tableId);
    if (table === null) {
      throw new Error(`Unknown element with id '${tableId}'.`);
    } else if (!(table instanceof HTMLTableElement)) {
      throw new Error(`Element with id '${tableId}' is no table.`);
    }
    this.table = table;

    this.projectId = projectId;
    this.pip = pip;
    this.entryType = entryType;
    this.supportsDeleteInstruction = supportsDeleteInstruction;

    this.table.querySelectorAll(".jsDeleteButton").forEach((button: HTMLElement) => {
      button.addEventListener("click", (ev) => this._confirmDeletePipEntry(ev));
    });
  }

  /**
   * Returns the data used to setup the AJAX request object.
   */
  _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
    return {
      data: {
        actionName: "deletePipEntry",
        className: "wcf\\data\\devtools\\project\\DevtoolsProjectAction",
      },
    };
  }

  /**
   * Handles successful AJAX request.
   */
  _ajaxSuccess(data: AjaxResponse): void {
    UiNotification.show();

    this.table.querySelectorAll("tbody > tr").forEach((pipEntry: HTMLTableRowElement) => {
      if (pipEntry.dataset.identifier === data.returnValues.identifier) {
        pipEntry.remove();
      }
    });

    // Reload page if the table is now empty.
    if (this.table.querySelector("tbody > tr") === null) {
      window.location.reload();
    }
  }

  /**
   * Shows the confirmation dialog when deleting a pip entry.
   */
  private _confirmDeletePipEntry(event: MouseEvent): void {
    event.preventDefault();

    const button = event.currentTarget as HTMLElement;
    const pipEntry = button.closest("tr")!;

    let template = "";
    if (this.supportsDeleteInstruction) {
      template = `
<dl>
  <dt></dt>
  <dd>
    <label>
      <input type="checkbox" name="addDeleteInstruction" checked> ${Language.get(
        "wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction",
      )}
    </label>
    <small>${Language.get("wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction.description")}</small>
  </dd>
</dl>`;
    }

    showConfirmation({
      confirm: (parameters, content) => this.deletePipEntry(parameters, content),
      message: Language.get("wcf.acp.devtools.project.pip.entry.delete.confirmMessage"),
      template,
      parameters: {
        pipEntry: pipEntry,
      },
    });
  }

  /**
   * Sends the AJAX request to delete a pip entry.
   */
  private deletePipEntry(parameters: ConfirmationCallbackParameters, content: HTMLElement): void {
    let addDeleteInstruction = false;
    if (this.supportsDeleteInstruction) {
      const input = content.querySelector("input[name=addDeleteInstruction]") as HTMLInputElement;
      addDeleteInstruction = input.checked;
    }

    const pipEntry = parameters.pipEntry as HTMLTableRowElement;
    Ajax.api(this, {
      objectIDs: [this.projectId],
      parameters: {
        addDeleteInstruction,
        entryType: this.entryType,
        identifier: pipEntry.dataset.identifier,
        pip: this.pip,
      },
    });
  }
}

Core.enableLegacyInheritance(DevtoolsProjectPipEntryList);

export = DevtoolsProjectPipEntryList;

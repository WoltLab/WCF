/**
 * Handles the JavaScript part of the devtools project pip entry list.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Devtools/Project/Pip/Entry/List
 */
define(["require", "exports", "tslib", "../../../../../../Ajax", "../../../../../../Core", "../../../../../../Language", "../../../../../../Ui/Confirmation", "../../../../../../Ui/Notification"], function (require, exports, tslib_1, Ajax, Core, Language, Confirmation_1, UiNotification) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Language = tslib_1.__importStar(Language);
    UiNotification = tslib_1.__importStar(UiNotification);
    class DevtoolsProjectPipEntryList {
        /**
         * Initializes the devtools project pip entry list handler.
         */
        constructor(tableId, projectId, pip, entryType, supportsDeleteInstruction) {
            const table = document.getElementById(tableId);
            if (table === null) {
                throw new Error(`Unknown element with id '${tableId}'.`);
            }
            else if (!(table instanceof HTMLTableElement)) {
                throw new Error(`Element with id '${tableId}' is no table.`);
            }
            this.table = table;
            this.projectId = projectId;
            this.pip = pip;
            this.entryType = entryType;
            this.supportsDeleteInstruction = supportsDeleteInstruction;
            this.table.querySelectorAll(".jsDeleteButton").forEach((button) => {
                button.addEventListener("click", (ev) => this._confirmDeletePipEntry(ev));
            });
        }
        /**
         * Returns the data used to setup the AJAX request object.
         */
        _ajaxSetup() {
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
        _ajaxSuccess(data) {
            UiNotification.show();
            this.table.querySelectorAll("tbody > tr").forEach((pipEntry) => {
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
        _confirmDeletePipEntry(event) {
            event.preventDefault();
            const button = event.currentTarget;
            const pipEntry = button.closest("tr");
            let template = "";
            if (this.supportsDeleteInstruction) {
                template = `
<dl>
  <dt></dt>
  <dd>
    <label>
      <input type="checkbox" name="addDeleteInstruction" checked> ${Language.get("wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction")}
    </label>
    <small>${Language.get("wcf.acp.devtools.project.pip.entry.delete.addDeleteInstruction.description")}</small>
  </dd>
</dl>`;
            }
            Confirmation_1.show({
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
        deletePipEntry(parameters, content) {
            let addDeleteInstruction = false;
            if (this.supportsDeleteInstruction) {
                const input = content.querySelector("input[name=addDeleteInstruction]");
                addDeleteInstruction = input.checked;
            }
            const pipEntry = parameters.pipEntry;
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
    return DevtoolsProjectPipEntryList;
});

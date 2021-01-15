/**
 * Handles installing a project as a package.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Devtools/Project/Installation/Confirmation
 */

import * as Ajax from "../../../../../Ajax";
import * as Language from "../../../../../Language";
import * as UiConfirmation from "../../../../../Ui/Confirmation";

let _projectId: number;
let _projectName: string;

/**
 * Starts the package installation.
 */
function installPackage(): void {
  Ajax.apiOnce({
    data: {
      actionName: "installPackage",
      className: "wcf\\data\\devtools\\project\\DevtoolsProjectAction",
      objectIDs: [_projectId],
    },
    success: (data) => {
      const packageInstallation = new window.WCF.ACP.Package.Installation(
        data.returnValues.queueID,
        "DevtoolsInstallPackage",
        data.returnValues.isApplication,
        false,
        { projectID: _projectId },
      );

      packageInstallation.prepareInstallation();
    },
  });
}

/**
 * Shows the confirmation to start package installation.
 */
function showConfirmation(event: Event): void {
  event.preventDefault();

  UiConfirmation.show({
    confirm: () => installPackage(),
    message: Language.get("wcf.acp.devtools.project.installPackage.confirmMessage", {
      packageIdentifier: _projectName,
    }),
    messageIsHtml: true,
  });
}

/**
 * Initializes the confirmation to install a project as a package.
 */
export function init(projectId: number, projectName: string): void {
  _projectId = projectId;
  _projectName = projectName;

  document.querySelectorAll(".jsDevtoolsInstallPackage").forEach((element: HTMLElement) => {
    element.addEventListener("click", (ev) => showConfirmation(ev));
  });
}

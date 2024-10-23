/**
 * Handles installing a project as a package.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../../../Ajax", "../../../../../Language", "../../../../../Ui/Confirmation"], function (require, exports, tslib_1, Ajax, Language, UiConfirmation) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    let _projectId;
    let _projectName;
    /**
     * Starts the package installation.
     */
    function installPackage() {
        Ajax.apiOnce({
            data: {
                actionName: "installPackage",
                className: "wcf\\data\\devtools\\project\\DevtoolsProjectAction",
                objectIDs: [_projectId],
            },
            success: (data) => {
                const packageInstallation = new window.WCF.ACP.Package.Installation(data.returnValues.queueID, "DevtoolsInstallPackage", data.returnValues.isApplication, false, { projectID: _projectId });
                packageInstallation.prepareInstallation();
            },
        });
    }
    /**
     * Shows the confirmation to start package installation.
     */
    function showConfirmation(event) {
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
    function init(projectId, projectName) {
        _projectId = projectId;
        _projectName = projectName;
        document.querySelectorAll(".jsDevtoolsInstallPackage").forEach((element) => {
            element.addEventListener("click", (ev) => showConfirmation(ev));
        });
    }
});

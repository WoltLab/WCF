/**
 * Provides a dialog to copy an existing template group.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../../Ajax", "../../../../Language", "../../../../Ui/Dialog", "../../../../Ui/Notification", "../../../../Dom/Util"], function (require, exports, tslib_1, Ajax, Language, Dialog_1, UiNotification, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.init = init;
    Ajax = tslib_1.__importStar(Ajax);
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    UiNotification = tslib_1.__importStar(UiNotification);
    Util_1 = tslib_1.__importDefault(Util_1);
    class AcpUiTemplateGroupCopy {
        folderName = undefined;
        name = undefined;
        templateGroupId;
        constructor(templateGroupId) {
            this.templateGroupId = templateGroupId;
            const button = document.querySelector(".jsButtonCopy");
            button.addEventListener("click", (ev) => this.click(ev));
        }
        click(event) {
            event.preventDefault();
            Dialog_1.default.open(this);
        }
        _dialogSubmit() {
            Ajax.api(this, {
                parameters: {
                    templateGroupName: this.name.value,
                    templateGroupFolderName: this.folderName.value,
                },
            });
        }
        _ajaxSuccess(data) {
            Dialog_1.default.close(this);
            UiNotification.show(undefined, () => {
                window.location.href = data.returnValues.redirectURL;
            });
        }
        _dialogSetup() {
            return {
                id: "templateGroupCopy",
                options: {
                    onSetup: () => {
                        ["Name", "FolderName"].forEach((type) => {
                            const input = document.getElementById("copyTemplateGroup" + type);
                            input.value = document.getElementById("templateGroup" + type).value;
                            if (type === "Name") {
                                this.name = input;
                            }
                            else {
                                this.folderName = input;
                            }
                        });
                    },
                    title: Language.get("wcf.acp.template.group.copy"),
                },
                source: `<dl>
  <dt>
    <label for="copyTemplateGroupName">${Language.get("wcf.global.name")}</label>
  </dt>
  <dd>
    <input type="text" id="copyTemplateGroupName" class="long" data-dialog-submit-on-enter="true" required>
  </dd>
</dl>
<dl>
  <dt>
    <label for="copyTemplateGroupFolderName">${Language.get("wcf.acp.template.group.folderName")}</label>
  </dt>
  <dd>
    <input type="text" id="copyTemplateGroupFolderName" class="long" data-dialog-submit-on-enter="true" required>
  </dd>
</dl>
<div class="formSubmit">
  <button type="button" class="button buttonPrimary" data-type="submit">${Language.get("wcf.global.button.submit")}</button>
</div>`,
            };
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "copy",
                    className: "wcf\\data\\template\\group\\TemplateGroupAction",
                    objectIDs: [this.templateGroupId],
                },
                failure: (data) => {
                    if (data && data.returnValues && data.returnValues.fieldName && data.returnValues.errorType) {
                        if (data.returnValues.fieldName === "templateGroupName") {
                            Util_1.default.innerError(this.name, Language.get(`wcf.acp.template.group.name.error.${data.returnValues.errorType}`));
                        }
                        else {
                            Util_1.default.innerError(this.folderName, Language.get(`wcf.acp.template.group.folderName.error.${data.returnValues.errorType}`));
                        }
                        return false;
                    }
                    return true;
                },
            };
        }
    }
    let acpUiTemplateGroupCopy;
    function init(templateGroupId) {
        if (!acpUiTemplateGroupCopy) {
            acpUiTemplateGroupCopy = new AcpUiTemplateGroupCopy(templateGroupId);
        }
    }
});

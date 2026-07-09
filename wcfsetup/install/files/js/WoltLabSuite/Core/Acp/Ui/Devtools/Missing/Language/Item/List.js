/**
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 5.4
 */
define(["require", "exports", "tslib", "../../../../../../Ui/Confirmation", "../../../../../../Ajax", "WoltLabSuite/Core/Language", "../../../../../../Component/Dialog"], function (require, exports, tslib_1, UiConfirmation, Ajax, Language_1, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.List = void 0;
    UiConfirmation = tslib_1.__importStar(UiConfirmation);
    Ajax = tslib_1.__importStar(Ajax);
    class List {
        clearExistingLogButton;
        clearLogButton;
        constructor() {
            this.clearLogButton = document.getElementById("clearMissingLanguageItemLog");
            this.clearLogButton.addEventListener("click", () => this.clearLog());
            this.clearExistingLogButton = document.getElementById("clearExisingMissingLanguageItemLog");
            this.clearExistingLogButton.addEventListener("click", () => this.clearExistingLog());
            document.querySelectorAll(".jsStackTraceButton").forEach((button) => {
                button.addEventListener("click", (ev) => this.showStackTrace(ev));
            });
        }
        _ajaxSetup() {
            return {
                data: {
                    className: "wcf\\data\\devtools\\missing\\language\\item\\DevtoolsMissingLanguageItemAction",
                },
            };
        }
        _ajaxSuccess() {
            window.location.reload();
        }
        clearLog() {
            UiConfirmation.show({
                confirm: () => {
                    Ajax.api(this, {
                        actionName: "clearLog",
                    });
                },
                message: (0, Language_1.getPhrase)("wcf.acp.devtools.missingLanguageItem.clearLog.confirmMessage"),
            });
        }
        clearExistingLog() {
            UiConfirmation.show({
                confirm: () => {
                    Ajax.api(this, {
                        actionName: "clearExistingLog",
                    });
                },
                message: (0, Language_1.getPhrase)("wcf.acp.devtools.missingLanguageItem.clearExistingLog.confirmMessage"),
            });
        }
        showStackTrace(event) {
            const target = event.currentTarget;
            const dialog = (0, Dialog_1.dialogFactory)().fromHtml(target.dataset.stackTrace).withoutControls();
            dialog.show((0, Language_1.getPhrase)("wcf.acp.devtools.missingLanguageItem.stackTrace"));
        }
    }
    exports.List = List;
    exports.default = List;
});

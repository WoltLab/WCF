/**
 * Provides the dialog overlay to add a new box.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Box/Add
 */
define(["require", "exports", "tslib", "../../../Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.openDialog = exports.init = void 0;
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class AcpUiBoxAdd {
        constructor() {
            this.supportsI18n = false;
            this.link = "";
        }
        /**
         * Initializes the box add handler.
         */
        init(link, supportsI18n) {
            this.link = link;
            this.supportsI18n = supportsI18n;
            document.querySelectorAll(".jsButtonBoxAdd").forEach((button) => {
                button.addEventListener("click", (ev) => this.openDialog(ev));
            });
        }
        /**
         * Opens the 'Add Box' dialog.
         */
        openDialog(event) {
            if (event instanceof Event) {
                event.preventDefault();
            }
            Dialog_1.default.open(this);
        }
        _dialogSetup() {
            return {
                id: "boxAddDialog",
                options: {
                    onSetup: (content) => {
                        content.querySelector("button").addEventListener("click", (event) => {
                            event.preventDefault();
                            const boxTypeSelection = content.querySelector('input[name="boxType"]:checked');
                            const boxType = boxTypeSelection.value;
                            let isMultilingual = "0";
                            if (boxType !== "system" && this.supportsI18n) {
                                const i18nSelection = content.querySelector('input[name="isMultilingual"]:checked');
                                isMultilingual = i18nSelection.value;
                            }
                            window.location.href = this.link
                                .replace("{$boxType}", boxType)
                                .replace("{$isMultilingual}", isMultilingual);
                        });
                        content.querySelectorAll('input[type="radio"][name="boxType"]').forEach((boxType) => {
                            boxType.addEventListener("change", () => {
                                content
                                    .querySelectorAll('input[type="radio"][name="isMultilingual"]')
                                    .forEach((i18nSelection) => {
                                    i18nSelection.disabled = boxType.value === "system";
                                });
                            });
                        });
                    },
                    title: Language.get("wcf.acp.box.add"),
                },
            };
        }
    }
    let acpUiDialogAdd;
    function getAcpUiDialogAdd() {
        if (!acpUiDialogAdd) {
            acpUiDialogAdd = new AcpUiBoxAdd();
        }
        return acpUiDialogAdd;
    }
    /**
     * Initializes the box add handler.
     */
    function init(link, availableLanguages) {
        getAcpUiDialogAdd().init(link, availableLanguages > 1);
    }
    exports.init = init;
    /**
     * Opens the 'Add Box' dialog.
     */
    function openDialog(event) {
        getAcpUiDialogAdd().openDialog(event);
    }
    exports.openDialog = openDialog;
});

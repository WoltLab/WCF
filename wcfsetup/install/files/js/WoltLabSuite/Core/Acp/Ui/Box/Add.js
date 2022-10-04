/**
 * Provides the dialog overlay to add a new box.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Acp/Ui/Box/Add
 */
define(["require", "exports", "tslib", "../../../Language", "../../../Component/Dialog"], function (require, exports, tslib_1, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.AcpUiBoxAdd = void 0;
    Language = tslib_1.__importStar(Language);
    class AcpUiBoxAdd {
        #supportsI18n;
        #link;
        #dialog;
        /**
         * Initializes the box add handler.
         */
        constructor(link, supportsI18n) {
            this.#link = link;
            this.#supportsI18n = supportsI18n;
            document.querySelectorAll(".jsButtonBoxAdd").forEach((button) => {
                button.addEventListener("click", () => this.show());
            });
        }
        /**
         * Opens the 'Add Box' dialog.
         */
        show() {
            if (!this.#dialog) {
                this.#dialog = this.#createDialog();
            }
            this.#dialog.show(Language.get("wcf.acp.box.add"));
        }
        #createDialog() {
            const dialog = (0, Dialog_1.dialogFactory)().fromId("boxAddDialog").asPrompt();
            const content = dialog.content;
            content.querySelectorAll('input[type="radio"][name="boxType"]').forEach((boxType) => {
                boxType.addEventListener("change", () => {
                    content
                        .querySelectorAll('input[type="radio"][name="isMultilingual"]')
                        .forEach((i18nSelection) => {
                        i18nSelection.disabled = boxType.value === "system";
                    });
                });
            });
            dialog.addEventListener("primary", () => {
                const boxTypeSelection = content.querySelector('input[name="boxType"]:checked');
                const boxType = boxTypeSelection.value;
                let isMultilingual = "0";
                if (boxType !== "system" && this.#supportsI18n) {
                    const i18nSelection = content.querySelector('input[name="isMultilingual"]:checked');
                    isMultilingual = i18nSelection.value;
                }
                window.location.href = this.#link.replace("{$boxType}", boxType).replace("{$isMultilingual}", isMultilingual);
            });
            return dialog;
        }
    }
    exports.AcpUiBoxAdd = AcpUiBoxAdd;
    exports.default = AcpUiBoxAdd;
});

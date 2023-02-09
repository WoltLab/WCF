/**
 * Provides the dialog overlay to add a new page.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports", "tslib", "../../../Language", "../../../Component/Dialog"], function (require, exports, tslib_1, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.AcpUiPageAdd = void 0;
    Language = tslib_1.__importStar(Language);
    class AcpUiPageAdd {
        #supportsI18n;
        #link;
        #dialog;
        constructor(link, supportsI18n) {
            this.#link = link;
            this.#supportsI18n = supportsI18n;
            document.querySelectorAll(".jsButtonPageAdd").forEach((button) => {
                button.addEventListener("click", () => this.show());
            });
        }
        /**
         * Opens the 'Add Page' dialog.
         */
        show() {
            if (!this.#dialog) {
                this.#dialog = this.#createDialog();
            }
            this.#dialog.show(Language.get("wcf.acp.page.add"));
        }
        #createDialog() {
            const dialog = (0, Dialog_1.dialogFactory)().fromId("pageAddDialog").asPrompt();
            const content = dialog.content;
            dialog.addEventListener("primary", () => {
                const pageTypeSelection = content.querySelector('input[name="pageType"]:checked');
                const pageType = pageTypeSelection.value;
                let isMultilingual = "0";
                if (this.#supportsI18n) {
                    const i18nSelection = content.querySelector('input[name="isMultilingual"]:checked');
                    isMultilingual = i18nSelection.value;
                }
                window.location.href = this.#link.replace("{$pageType}", pageType).replace("{$isMultilingual}", isMultilingual);
            });
            return dialog;
        }
    }
    exports.AcpUiPageAdd = AcpUiPageAdd;
    exports.default = AcpUiPageAdd;
});

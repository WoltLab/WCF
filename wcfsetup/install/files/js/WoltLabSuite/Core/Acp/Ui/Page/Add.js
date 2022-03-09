/**
 * Provides the dialog overlay to add a new page.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Acp/Ui/Page/Add
 */
define(["require", "exports", "tslib", "../../../Language", "../../../Ui/Dialog"], function (require, exports, tslib_1, Language, Dialog_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.openDialog = exports.init = void 0;
    Language = tslib_1.__importStar(Language);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    class AcpUiPageAdd {
        constructor(link, isI18n) {
            this.link = link;
            this.isI18n = isI18n;
            document.querySelectorAll(".jsButtonPageAdd").forEach((button) => {
                button.addEventListener("click", (ev) => this.openDialog(ev));
            });
        }
        /**
         * Opens the 'Add Page' dialog.
         */
        openDialog(event) {
            if (event instanceof Event) {
                event.preventDefault();
            }
            Dialog_1.default.open(this);
        }
        _dialogSetup() {
            return {
                id: "pageAddDialog",
                options: {
                    onSetup: (content) => {
                        const button = content.querySelector("button");
                        button.addEventListener("click", (event) => {
                            event.preventDefault();
                            const pageType = content.querySelector('input[name="pageType"]:checked').value;
                            let isMultilingual = "0";
                            if (this.isI18n) {
                                isMultilingual = content.querySelector('input[name="isMultilingual"]:checked')
                                    .value;
                            }
                            window.location.href = this.link
                                .replace("{$pageType}", pageType)
                                .replace("{$isMultilingual}", isMultilingual);
                        });
                    },
                    title: Language.get("wcf.acp.page.add"),
                },
            };
        }
    }
    let acpUiPageAdd;
    /**
     * Initializes the page add handler.
     */
    function init(link, languages) {
        if (!acpUiPageAdd) {
            acpUiPageAdd = new AcpUiPageAdd(link, languages > 1);
        }
    }
    exports.init = init;
    /**
     * Opens the 'Add Page' dialog.
     */
    function openDialog() {
        acpUiPageAdd.openDialog();
    }
    exports.openDialog = openDialog;
});

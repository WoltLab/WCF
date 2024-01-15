/**
 * Object-based user list.
 *
 * @author Marcel Werk
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Ui/Pagination", "../Dialog", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, Ajax_1, Pagination_1, Dialog_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UserList = void 0;
    Pagination_1 = tslib_1.__importDefault(Pagination_1);
    Util_1 = tslib_1.__importDefault(Util_1);
    class UserList {
        #options;
        #dialogTitle;
        #pageNo = 1;
        #pageCount = 0;
        #dialog;
        constructor(options, dialogTitle) {
            this.#options = options;
            this.#dialogTitle = dialogTitle;
        }
        open() {
            this.#pageNo = 1;
            void this.#loadPage(this.#pageNo);
        }
        #showPage(pageNo, template) {
            if (pageNo) {
                this.#pageNo = pageNo;
            }
            const dialog = this.#getDialog();
            Util_1.default.setInnerHtml(dialog.content, template);
            dialog.show(this.#dialogTitle);
            if (this.#pageCount > 1) {
                const element = dialog.content.querySelector(".jsPagination");
                if (element !== null) {
                    new Pagination_1.default(element, {
                        activePage: this.#pageNo,
                        maxPage: this.#pageCount,
                        callbackSwitch: (pageNo) => {
                            void this.#loadPage(pageNo);
                        },
                    });
                }
            }
        }
        async #loadPage(pageNo) {
            if (this.#pageCount !== 0 && (pageNo < 1 || pageNo > this.#pageCount)) {
                throw new RangeError(`pageNo must be between 1 and ${this.#pageCount} (${pageNo} given).`);
            }
            this.#options.parameters.pageNo = pageNo;
            const response = (await (0, Ajax_1.dboAction)("getGroupedUserList", this.#options.className)
                .payload(this.#options.parameters)
                .dispatch());
            if (response.pageCount) {
                this.#pageCount = response.pageCount;
            }
            this.#showPage(pageNo, response.template);
        }
        #getDialog() {
            if (this.#dialog === undefined) {
                this.#dialog = (0, Dialog_1.dialogFactory)().withoutContent().withoutControls();
            }
            return this.#dialog;
        }
    }
    exports.UserList = UserList;
});

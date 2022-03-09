/**
 * Object-based user list.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/List
 */
define(["require", "exports", "tslib", "../../Ajax", "../../Core", "../../Dom/Util", "../Dialog", "../Pagination"], function (require, exports, tslib_1, Ajax, Core, Util_1, Dialog_1, Pagination_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    Util_1 = tslib_1.__importDefault(Util_1);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Pagination_1 = tslib_1.__importDefault(Pagination_1);
    /**
     * @constructor
     */
    class UiUserList {
        /**
         * Initializes the user list.
         *
         * @param  {object}  options    list of initialization options
         */
        constructor(options) {
            this.cache = new Map();
            this.pageCount = 0;
            this.pageNo = 1;
            this.options = Core.extend({
                className: "",
                dialogTitle: "",
                parameters: {},
            }, options);
        }
        /**
         * Opens the user list.
         */
        open() {
            this.pageNo = 1;
            this.showPage();
        }
        /**
         * Shows the current or given page.
         */
        showPage(pageNo) {
            if (typeof pageNo === "number") {
                this.pageNo = +pageNo;
            }
            if (this.pageCount !== 0 && (this.pageNo < 1 || this.pageNo > this.pageCount)) {
                throw new RangeError(`pageNo must be between 1 and ${this.pageCount} (${this.pageNo} given).`);
            }
            if (this.cache.has(this.pageNo)) {
                const dialog = Dialog_1.default.open(this, this.cache.get(this.pageNo));
                if (this.pageCount > 1) {
                    const element = dialog.content.querySelector(".jsPagination");
                    if (element !== null) {
                        new Pagination_1.default(element, {
                            activePage: this.pageNo,
                            maxPage: this.pageCount,
                            callbackSwitch: this.showPage.bind(this),
                        });
                    }
                    // scroll to the list start
                    const container = dialog.content.parentElement;
                    if (container.scrollTop > 0) {
                        container.scrollTop = 0;
                    }
                }
            }
            else {
                this.options.parameters.pageNo = this.pageNo;
                Ajax.api(this, {
                    parameters: this.options.parameters,
                });
            }
        }
        _ajaxSuccess(data) {
            if (data.returnValues.pageCount !== undefined) {
                this.pageCount = ~~data.returnValues.pageCount;
            }
            this.cache.set(this.pageNo, data.returnValues.template);
            this.showPage();
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "getGroupedUserList",
                    className: this.options.className,
                    interfaceName: "wcf\\data\\IGroupedUserListAction",
                },
            };
        }
        _dialogSetup() {
            return {
                id: Util_1.default.getUniqueId(),
                options: {
                    title: this.options.dialogTitle,
                },
                source: null,
            };
        }
    }
    Core.enableLegacyInheritance(UiUserList);
    return UiUserList;
});

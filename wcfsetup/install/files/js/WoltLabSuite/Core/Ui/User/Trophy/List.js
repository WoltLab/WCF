/**
 * Handles the user trophy dialog.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @woltlabExcludeBundle all
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../Pagination", "WoltLabSuite/Core/Helper/Selector", "WoltLabSuite/Core/Component/Dialog", "WoltLabSuite/Core/Dom/Util"], function (require, exports, tslib_1, Ajax, Pagination_1, Selector_1, Dialog_1, Util_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Pagination_1 = tslib_1.__importDefault(Pagination_1);
    class CacheData {
        pageCount;
        title;
        cache = new Map();
        constructor(pageCount, title) {
            this.pageCount = pageCount;
            this.title = title;
        }
        has(pageNo) {
            return this.cache.has(pageNo);
        }
        get(pageNo) {
            return this.cache.get(pageNo);
        }
        set(pageNo, template) {
            this.cache.set(pageNo, template);
        }
    }
    class UiUserTrophyList {
        cache = new Map();
        currentPageNo = 0;
        currentUser = 0;
        #dialog = undefined;
        /**
         * Initializes the user trophy list.
         */
        constructor() {
            (0, Selector_1.wheneverFirstSeen)(".userTrophyOverlayList", (element) => {
                element.addEventListener("click", (event) => {
                    this.open(element, event);
                });
            });
        }
        /**
         * Opens the user trophy list for a specific user.
         */
        open(element, event) {
            event.preventDefault();
            this.currentPageNo = 1;
            this.currentUser = +element.dataset.userId;
            this.showPage();
        }
        /**
         * Shows the current or given page.
         */
        showPage(pageNo) {
            if (pageNo !== undefined) {
                this.currentPageNo = pageNo;
            }
            const data = this.cache.get(this.currentUser);
            if (data) {
                // validate pageNo
                if (data.pageCount !== 0 && (this.currentPageNo < 1 || this.currentPageNo > data.pageCount)) {
                    throw new RangeError(`pageNo must be between 1 and ${data.pageCount} (${this.currentPageNo} given).`);
                }
            }
            if (data && data.has(this.currentPageNo)) {
                if (this.#dialog === undefined) {
                    this.#dialog = (0, Dialog_1.dialogFactory)().withoutContent().withoutControls();
                }
                (0, Util_1.setInnerHtml)(this.#dialog.content, data.get(this.currentPageNo));
                if (!this.#dialog.open) {
                    this.#dialog.show(data.title);
                }
                if (data.pageCount > 1) {
                    const element = this.#dialog.content.querySelector(".jsPagination");
                    if (element !== null) {
                        new Pagination_1.default(element, {
                            activePage: this.currentPageNo,
                            maxPage: data.pageCount,
                            callbackSwitch: this.showPage.bind(this),
                        });
                    }
                }
            }
            else {
                Ajax.api(this, {
                    parameters: {
                        pageNo: this.currentPageNo,
                        userID: this.currentUser,
                    },
                });
            }
        }
        _ajaxSuccess(data) {
            let cache;
            if (data.returnValues.pageCount !== undefined) {
                cache = new CacheData(+data.returnValues.pageCount, data.returnValues.title);
                this.cache.set(this.currentUser, cache);
            }
            else {
                cache = this.cache.get(this.currentUser);
            }
            cache.set(this.currentPageNo, data.returnValues.template);
            this.showPage();
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "getGroupedUserTrophyList",
                    className: "wcf\\data\\user\\trophy\\UserTrophyAction",
                },
            };
        }
    }
    return UiUserTrophyList;
});

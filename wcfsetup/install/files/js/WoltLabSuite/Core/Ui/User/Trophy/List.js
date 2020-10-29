/**
 * Handles the user trophy dialog.
 *
 * @author  Joshua Ruesweg
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Trophy/List
 */
define(["require", "exports", "tslib", "../../../Ajax", "../../../Dom/Change/Listener", "../../Dialog", "../../Pagination"], function (require, exports, tslib_1, Ajax, Listener_1, Dialog_1, Pagination_1) {
    "use strict";
    Ajax = tslib_1.__importStar(Ajax);
    Listener_1 = tslib_1.__importDefault(Listener_1);
    Dialog_1 = tslib_1.__importDefault(Dialog_1);
    Pagination_1 = tslib_1.__importDefault(Pagination_1);
    class CacheData {
        constructor(pageCount, title) {
            this.pageCount = pageCount;
            this.title = title;
            this.cache = new Map();
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
        /**
         * Initializes the user trophy list.
         */
        constructor() {
            this.cache = new Map();
            this.currentPageNo = 0;
            this.currentUser = 0;
            this.knownElements = new WeakSet();
            Listener_1.default.add('WoltLabSuite/Core/Ui/User/Trophy/List', this.rebuild.bind(this));
            this.rebuild();
        }
        /**
         * Adds event userTrophyOverlayList elements.
         */
        rebuild() {
            document.querySelectorAll('.userTrophyOverlayList').forEach((element) => {
                if (!this.knownElements.has(element)) {
                    element.addEventListener('click', (ev) => this.open(element, ev));
                    this.knownElements.add(element);
                }
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
                    throw new RangeError("pageNo must be between 1 and " + data.pageCount + " (" + this.currentPageNo + " given).");
                }
            }
            if (data && data.has(this.currentPageNo)) {
                const dialog = Dialog_1.default.open(this, data.get(this.currentPageNo));
                Dialog_1.default.setTitle('userTrophyListOverlay', data.title);
                if (data.pageCount > 1) {
                    const element = dialog.content.querySelector('.jsPagination');
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
                    actionName: 'getGroupedUserTrophyList',
                    className: 'wcf\\data\\user\\trophy\\UserTrophyAction',
                },
            };
        }
        _dialogSetup() {
            return {
                id: 'userTrophyListOverlay',
                options: {
                    title: "",
                },
                source: null,
            };
        }
    }
    return UiUserTrophyList;
});

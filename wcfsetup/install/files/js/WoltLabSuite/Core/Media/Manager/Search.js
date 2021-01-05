/**
 * Provides the media search for the media manager.
 *
 * @author  Matthias Schmidt
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Media/Manager/Search
 */
define(["require", "exports", "tslib", "../../Dom/Traverse", "../../Language", "../../Ajax", "../../Core"], function (require, exports, tslib_1, DomTraverse, Language, Ajax, Core) {
    "use strict";
    DomTraverse = tslib_1.__importStar(DomTraverse);
    Language = tslib_1.__importStar(Language);
    Ajax = tslib_1.__importStar(Ajax);
    Core = tslib_1.__importStar(Core);
    class MediaManagerSearch {
        constructor(mediaManager) {
            this._searchMode = false;
            this._mediaManager = mediaManager;
            const dialog = mediaManager.getDialog();
            this._searchContainer = dialog.querySelector(".mediaManagerSearch");
            this._input = dialog.querySelector(".mediaManagerSearchField");
            this._input.addEventListener("keypress", (ev) => this._keyPress(ev));
            this._cancelButton = dialog.querySelector(".mediaManagerSearchCancelButton");
            this._cancelButton.addEventListener("click", () => this._cancelSearch());
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "getSearchResultList",
                    className: "wcf\\data\\media\\MediaAction",
                    interfaceName: "wcf\\data\\ISearchAction",
                },
            };
        }
        _ajaxSuccess(data) {
            this._mediaManager.setMedia(data.returnValues.media || {}, data.returnValues.template || "", {
                pageCount: data.returnValues.pageCount || 0,
                pageNo: data.returnValues.pageNo || 0,
            });
            this._mediaManager.getDialog().querySelector(".dialogContent").scrollTop = 0;
        }
        /**
         * Cancels the search after clicking on the cancel search button.
         */
        _cancelSearch() {
            if (this._searchMode) {
                this._searchMode = false;
                this.resetSearch();
                this._mediaManager.resetMedia();
            }
        }
        /**
         * Hides the search string threshold error.
         */
        _hideStringThresholdError() {
            const innerInfo = DomTraverse.childByClass(this._input.parentNode.parentNode, "innerInfo");
            if (innerInfo) {
                innerInfo.style.display = "none";
            }
        }
        /**
         * Handles the `[ENTER]` key to submit the form.
         */
        _keyPress(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                if (this._input.value.length >= this._mediaManager.getOption("minSearchLength")) {
                    this._hideStringThresholdError();
                    this.search();
                }
                else {
                    this._showStringThresholdError();
                }
            }
        }
        /**
         * Shows the search string threshold error.
         */
        _showStringThresholdError() {
            let innerInfo = DomTraverse.childByClass(this._input.parentNode.parentNode, "innerInfo");
            if (innerInfo) {
                innerInfo.style.display = "block";
            }
            else {
                innerInfo = document.createElement("p");
                innerInfo.className = "innerInfo";
                innerInfo.textContent = Language.get("wcf.media.search.info.searchStringThreshold", {
                    minSearchLength: this._mediaManager.getOption("minSearchLength"),
                });
                this._input.parentNode.insertAdjacentElement("afterend", innerInfo);
            }
        }
        /**
         * Hides the media search.
         */
        hideSearch() {
            this._searchContainer.style.display = "none";
        }
        /**
         * Resets the media search.
         */
        resetSearch() {
            this._input.value = "";
        }
        /**
         * Shows the media search.
         */
        showSearch() {
            this._searchContainer.style.display = "block";
        }
        /**
         * Sends an AJAX request to fetch search results.
         */
        search(pageNo) {
            if (typeof pageNo !== "number") {
                pageNo = 1;
            }
            let searchString = this._input.value;
            if (searchString && this._input.value.length < this._mediaManager.getOption("minSearchLength")) {
                this._showStringThresholdError();
                searchString = "";
            }
            else {
                this._hideStringThresholdError();
            }
            this._searchMode = true;
            Ajax.api(this, {
                parameters: {
                    categoryID: this._mediaManager.getCategoryId(),
                    imagesOnly: this._mediaManager.getOption("imagesOnly"),
                    mode: this._mediaManager.getMode(),
                    pageNo: pageNo,
                    searchString: searchString,
                },
            });
        }
    }
    Core.enableLegacyInheritance(MediaManagerSearch);
    return MediaManagerSearch;
});

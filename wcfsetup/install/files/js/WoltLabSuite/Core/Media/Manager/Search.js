/**
 * Provides the media search for the media manager.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Media/Manager/Search
 */
define(['Ajax', 'Core', 'Dom/Traverse', 'Dom/Util', 'EventKey', 'Language', 'Ui/SimpleDropdown'], function (Ajax, Core, DomTraverse, DomUtil, EventKey, Language, UiSimpleDropdown) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            _ajaxSetup: function () { },
            _ajaxSuccess: function () { },
            _cancelSearch: function () { },
            _keyPress: function () { },
            _search: function () { },
            hideSearch: function () { },
            resetSearch: function () { },
            showSearch: function () { }
        };
        return Fake;
    }
    /**
     * @constructor
     */
    function MediaManagerSearch(mediaManager) {
        this._mediaManager = mediaManager;
        this._searchMode = false;
        this._searchContainer = elByClass('mediaManagerSearch', mediaManager.getDialog())[0];
        this._input = elByClass('mediaManagerSearchField', mediaManager.getDialog())[0];
        this._input.addEventListener('keypress', this._keyPress.bind(this));
        this._cancelButton = elByClass('mediaManagerSearchCancelButton', mediaManager.getDialog())[0];
        this._cancelButton.addEventListener(WCF_CLICK_EVENT, this._cancelSearch.bind(this));
    }
    MediaManagerSearch.prototype = {
        /**
         * Returns the data for Ajax to setup the Ajax/Request object.
         *
         * @return	{object}	setup data for Ajax/Request object
         */
        _ajaxSetup: function () {
            return {
                data: {
                    actionName: 'getSearchResultList',
                    className: 'wcf\\data\\media\\MediaAction',
                    interfaceName: 'wcf\\data\\ISearchAction'
                }
            };
        },
        /**
         * Handles successful AJAX requests.
         *
         * @param	{object}	data	response data
         */
        _ajaxSuccess: function (data) {
            this._mediaManager.setMedia(data.returnValues.media || {}, data.returnValues.template || '', {
                pageCount: data.returnValues.pageCount || 0,
                pageNo: data.returnValues.pageNo || 0
            });
            elByClass('dialogContent', this._mediaManager.getDialog())[0].scrollTop = 0;
        },
        /**
         * Cancels the search after clicking on the cancel search button.
         */
        _cancelSearch: function () {
            if (this._searchMode) {
                this._searchMode = false;
                this.resetSearch();
                this._mediaManager.resetMedia();
            }
        },
        /**
         * Hides the search string threshold error.
         */
        _hideStringThresholdError: function () {
            var innerInfo = DomTraverse.childByClass(this._input.parentNode.parentNode, 'innerInfo');
            if (innerInfo) {
                elHide(innerInfo);
            }
        },
        /**
         * Handles the `[ENTER]` key to submit the form.
         *
         * @param	{Event}		event		event object
         */
        _keyPress: function (event) {
            if (EventKey.Enter(event)) {
                event.preventDefault();
                if (this._input.value.length >= this._mediaManager.getOption('minSearchLength')) {
                    this._hideStringThresholdError();
                    this.search();
                }
                else {
                    this._showStringThresholdError();
                }
            }
        },
        /**
         * Shows the search string threshold error.
         */
        _showStringThresholdError: function () {
            var innerInfo = DomTraverse.childByClass(this._input.parentNode.parentNode, 'innerInfo');
            if (innerInfo) {
                elShow(innerInfo);
            }
            else {
                innerInfo = elCreate('p');
                innerInfo.className = 'innerInfo';
                innerInfo.textContent = Language.get('wcf.media.search.info.searchStringThreshold', {
                    minSearchLength: this._mediaManager.getOption('minSearchLength')
                });
                DomUtil.insertAfter(innerInfo, this._input.parentNode);
            }
        },
        /**
         * Hides the media search.
         */
        hideSearch: function () {
            elHide(this._searchContainer);
        },
        /**
         * Resets the media search.
         */
        resetSearch: function () {
            this._input.value = '';
        },
        /**
         * Shows the media search.
         */
        showSearch: function () {
            elShow(this._searchContainer);
        },
        /**
         * Sends an AJAX request to fetch search results.
         *
         * @param	{integer}	pageNo
         */
        search: function (pageNo) {
            if (typeof pageNo !== "number") {
                pageNo = 1;
            }
            var searchString = this._input.value;
            if (searchString && this._input.value.length < this._mediaManager.getOption('minSearchLength')) {
                this._showStringThresholdError();
                searchString = '';
            }
            else {
                this._hideStringThresholdError();
            }
            this._searchMode = true;
            Ajax.api(this, {
                parameters: {
                    categoryID: this._mediaManager.getCategoryId(),
                    imagesOnly: this._mediaManager.getOption('imagesOnly'),
                    mode: this._mediaManager.getMode(),
                    pageNo: pageNo,
                    searchString: searchString
                }
            });
        },
    };
    return MediaManagerSearch;
});

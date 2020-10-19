define(['Ajax', 'EventKey', 'Language', 'StringUtil', 'Dom/Util', 'Ui/Dialog'], function (Ajax, EventKey, Language, StringUtil, DomUtil, UiDialog) {
    "use strict";
    if (!COMPILER_TARGET_DEFAULT) {
        var Fake = function () { };
        Fake.prototype = {
            open: function () { },
            _search: function () { },
            _click: function () { },
            _ajaxSuccess: function () { },
            _ajaxSetup: function () { },
            _dialogSetup: function () { }
        };
        return Fake;
    }
    var _callbackSelect, _resultContainer, _resultList, _searchInput = null;
    return {
        open: function (callbackSelect) {
            _callbackSelect = callbackSelect;
            UiDialog.open(this);
        },
        _search: function (event) {
            event.preventDefault();
            var inputContainer = _searchInput.parentNode;
            var value = _searchInput.value.trim();
            if (value.length < 3) {
                elInnerError(inputContainer, Language.get('wcf.page.search.error.tooShort'));
                return;
            }
            else {
                elInnerError(inputContainer, false);
            }
            Ajax.api(this, {
                parameters: {
                    searchString: value
                }
            });
        },
        _click: function (event) {
            event.preventDefault();
            var page = event.currentTarget;
            var pageTitle = elBySel('h3', page).textContent.replace(/['"]/g, '');
            _callbackSelect(elData(page, 'page-id') + '#' + pageTitle);
            UiDialog.close(this);
        },
        _ajaxSuccess: function (data) {
            var html = '', page;
            //noinspection JSUnresolvedVariable
            for (var i = 0, length = data.returnValues.length; i < length; i++) {
                //noinspection JSUnresolvedVariable
                page = data.returnValues[i];
                html += '<li>'
                    + '<div class="containerHeadline pointer" data-page-id="' + page.pageID + '">'
                    + '<h3>' + StringUtil.escapeHTML(page.name) + '</h3>'
                    + '<small>' + StringUtil.escapeHTML(page.displayLink) + '</small>'
                    + '</div>'
                    + '</li>';
            }
            _resultList.innerHTML = html;
            window[html ? 'elShow' : 'elHide'](_resultContainer);
            if (html) {
                elBySelAll('.containerHeadline', _resultList, (function (item) {
                    item.addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
                }).bind(this));
            }
            else {
                elInnerError(_searchInput.parentNode, Language.get('wcf.page.search.error.noResults'));
            }
        },
        _ajaxSetup: function () {
            return {
                data: {
                    actionName: 'search',
                    className: 'wcf\\data\\page\\PageAction'
                }
            };
        },
        _dialogSetup: function () {
            return {
                id: 'wcfUiPageSearch',
                options: {
                    onSetup: (function () {
                        var callbackSearch = this._search.bind(this);
                        _searchInput = elById('wcfUiPageSearchInput');
                        _searchInput.addEventListener('keydown', function (event) {
                            if (EventKey.Enter(event)) {
                                callbackSearch(event);
                            }
                        });
                        _searchInput.nextElementSibling.addEventListener(WCF_CLICK_EVENT, callbackSearch);
                        _resultContainer = elById('wcfUiPageSearchResultContainer');
                        _resultList = elById('wcfUiPageSearchResultList');
                    }).bind(this),
                    onShow: function () {
                        _searchInput.focus();
                    },
                    title: Language.get('wcf.page.search')
                },
                source: '<div class="section">'
                    + '<dl>'
                    + '<dt><label for="wcfUiPageSearchInput">' + Language.get('wcf.page.search.name') + '</label></dt>'
                    + '<dd>'
                    + '<div class="inputAddon">'
                    + '<input type="text" id="wcfUiPageSearchInput" class="long">'
                    + '<a href="#" class="inputSuffix"><span class="icon icon16 fa-search"></span></a>'
                    + '</div>'
                    + '</dd>'
                    + '</dl>'
                    + '</div>'
                    + '<section id="wcfUiPageSearchResultContainer" class="section" style="display: none;">'
                    + '<header class="sectionHeader">'
                    + '<h2 class="sectionTitle">' + Language.get('wcf.page.search.results') + '</h2>'
                    + '</header>'
                    + '<ol id="wcfUiPageSearchResultList" class="containerList"></ol>'
                    + '</section>'
            };
        }
    };
});

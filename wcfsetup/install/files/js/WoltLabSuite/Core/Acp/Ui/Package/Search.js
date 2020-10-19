/**
 * Search interface for the package server lists.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module      WoltLabSuite/Core/Acp/Ui/Package/Search
 */
define(['Ajax', 'WoltLabSuite/Core/Acp/Ui/Package/PrepareInstallation'], function (Ajax, AcpUiPackagePrepareInstallation) {
    'use strict';
    function AcpUiPackageSearch() { this.init(); }
    AcpUiPackageSearch.prototype = {
        init: function () {
            this._input = elById('packageSearchInput');
            this._installation = new AcpUiPackagePrepareInstallation();
            this._isBusy = false;
            this._isFirstRequest = true;
            this._lastValue = '';
            this._options = {
                delay: 300,
                minLength: 3
            };
            this._request = null;
            this._resultList = elById('packageSearchResultList');
            this._resultListContainer = elById('packageSearchResultContainer');
            this._resultCounter = elById('packageSearchResultCounter');
            this._timerDelay = null;
            this._input.addEventListener('keyup', this._keyup.bind(this));
        },
        _keyup: function () {
            var value = this._input.value.trim();
            if (this._lastValue === value) {
                return;
            }
            this._lastValue = value;
            if (value.length < this._options.minLength) {
                this._setStatus('idle');
                return;
            }
            if (this._isFirstRequest) {
                if (!this._isBusy) {
                    this._isBusy = true;
                    this._setStatus('refreshDatabase');
                    Ajax.api(this, {
                        actionName: 'refreshDatabase'
                    });
                }
                return;
            }
            if (this._timerDelay !== null) {
                window.clearTimeout(this._timerDelay);
            }
            this._timerDelay = window.setTimeout((function () {
                this._setStatus('loading');
                this._search(value);
            }).bind(this), this._options.delay);
        },
        _search: function (value) {
            if (this._request) {
                this._request.abortPrevious();
            }
            this._request = Ajax.api(this, {
                parameters: {
                    searchString: value
                }
            });
        },
        _setStatus: function (status) {
            elData(this._resultListContainer, 'status', status);
        },
        _ajaxSuccess: function (data) {
            switch (data.actionName) {
                case 'refreshDatabase':
                    this._isFirstRequest = false;
                    this._lastValue = '';
                    this._keyup();
                    break;
                case 'search':
                    if (data.returnValues.count > 0) {
                        this._resultList.innerHTML = data.returnValues.template;
                        this._resultCounter.textContent = data.returnValues.count;
                        this._setStatus('showResults');
                        elBySelAll('.jsInstallPackage', this._resultList, (function (button) {
                            button.addEventListener(WCF_CLICK_EVENT, (function (event) {
                                event.preventDefault();
                                button.blur();
                                this._installation.start(elData(button, 'package'), elData(button, 'package-version'));
                            }).bind(this));
                        }).bind(this));
                    }
                    else {
                        this._setStatus('noResults');
                    }
                    break;
            }
        },
        _ajaxSetup: function () {
            return {
                data: {
                    actionName: 'search',
                    className: 'wcf\\data\\package\\update\\PackageUpdateAction'
                },
                silent: true
            };
        }
    };
    return AcpUiPackageSearch;
});

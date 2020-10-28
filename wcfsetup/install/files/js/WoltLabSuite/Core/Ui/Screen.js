/**
 * Provides consistent support for media queries and body scrolling.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/Screen (alias)
 * @module  WoltLabSuite/Core/Ui/Screen
 */
define(["require", "exports", "tslib", "../Core", "../Environment"], function (require, exports, tslib_1, Core, Environment) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setDialogContainer = exports.pageOverlayIsActive = exports.pageOverlayClose = exports.pageOverlayOpen = exports.scrollEnable = exports.scrollDisable = exports.is = exports.remove = exports.on = void 0;
    Core = tslib_1.__importStar(Core);
    Environment = tslib_1.__importStar(Environment);
    const _mql = new Map();
    let _dialogContainer;
    let _scrollDisableCounter = 0;
    let _scrollOffsetFrom;
    let _scrollTop = 0;
    let _pageOverlayCounter = 0;
    const _mqMap = new Map(Object.entries({
        'screen-xs': '(max-width: 544px)',
        'screen-sm': '(min-width: 545px) and (max-width: 768px)',
        'screen-sm-down': '(max-width: 768px)',
        'screen-sm-up': '(min-width: 545px)',
        'screen-sm-md': '(min-width: 545px) and (max-width: 1024px)',
        'screen-md': '(min-width: 769px) and (max-width: 1024px)',
        'screen-md-down': '(max-width: 1024px)',
        'screen-md-up': '(min-width: 769px)',
        'screen-lg': '(min-width: 1025px)',
        'screen-lg-only': '(min-width: 1025px) and (max-width: 1280px)',
        'screen-lg-down': '(max-width: 1280px)',
        'screen-xl': '(min-width: 1281px)',
    }));
    // Microsoft Edge rewrites the media queries to whatever it
    // pleases, causing the input and output query to mismatch
    const _mqMapEdge = new Map();
    /**
     * Registers event listeners for media query match/unmatch.
     *
     * The `callbacks` object may contain the following keys:
     *  - `match`, triggered when media query matches
     *  - `unmatch`, triggered when media query no longer matches
     *  - `setup`, invoked when media query first matches
     *
     * Returns a UUID that is used to internal identify the callbacks, can be used
     * to remove binding by calling the `remove` method.
     */
    function on(query, callbacks) {
        const uuid = Core.getUuid(), queryObject = _getQueryObject(query);
        if (typeof callbacks.match === 'function') {
            queryObject.callbacksMatch.set(uuid, callbacks.match);
        }
        if (typeof callbacks.unmatch === 'function') {
            queryObject.callbacksUnmatch.set(uuid, callbacks.unmatch);
        }
        if (typeof callbacks.setup === 'function') {
            if (queryObject.mql.matches) {
                callbacks.setup();
            }
            else {
                queryObject.callbacksSetup.set(uuid, callbacks.setup);
            }
        }
        return uuid;
    }
    exports.on = on;
    /**
     * Removes all listeners identified by their common UUID.
     */
    function remove(query, uuid) {
        const queryObject = _getQueryObject(query);
        queryObject.callbacksMatch.delete(uuid);
        queryObject.callbacksUnmatch.delete(uuid);
        queryObject.callbacksSetup.delete(uuid);
    }
    exports.remove = remove;
    /**
     * Returns a boolean value if a media query expression currently matches.
     */
    function is(query) {
        return _getQueryObject(query).mql.matches;
    }
    exports.is = is;
    /**
     * Disables scrolling of body element.
     */
    function scrollDisable() {
        if (_scrollDisableCounter === 0) {
            _scrollTop = document.body.scrollTop;
            _scrollOffsetFrom = 'body';
            if (!_scrollTop) {
                _scrollTop = document.documentElement.scrollTop;
                _scrollOffsetFrom = 'documentElement';
            }
            const pageContainer = document.getElementById('pageContainer');
            // setting translateY causes Mobile Safari to snap
            if (Environment.platform() === 'ios') {
                pageContainer.style.setProperty('position', 'relative', '');
                pageContainer.style.setProperty('top', '-' + _scrollTop + 'px', '');
            }
            else {
                pageContainer.style.setProperty('margin-top', '-' + _scrollTop + 'px', '');
            }
            document.documentElement.classList.add('disableScrolling');
        }
        _scrollDisableCounter++;
    }
    exports.scrollDisable = scrollDisable;
    /**
     * Re-enables scrolling of body element.
     */
    function scrollEnable() {
        if (_scrollDisableCounter) {
            _scrollDisableCounter--;
            if (_scrollDisableCounter === 0) {
                document.documentElement.classList.remove('disableScrolling');
                const pageContainer = document.getElementById('pageContainer');
                if (Environment.platform() === 'ios') {
                    pageContainer.style.removeProperty('position');
                    pageContainer.style.removeProperty('top');
                }
                else {
                    pageContainer.style.removeProperty('margin-top');
                }
                if (_scrollTop) {
                    document[_scrollOffsetFrom].scrollTop = ~~_scrollTop;
                }
            }
        }
    }
    exports.scrollEnable = scrollEnable;
    /**
     * Indicates that at least one page overlay is currently open.
     */
    function pageOverlayOpen() {
        if (_pageOverlayCounter === 0) {
            document.documentElement.classList.add('pageOverlayActive');
        }
        _pageOverlayCounter++;
    }
    exports.pageOverlayOpen = pageOverlayOpen;
    /**
     * Marks one page overlay as closed.
     */
    function pageOverlayClose() {
        if (_pageOverlayCounter) {
            _pageOverlayCounter--;
            if (_pageOverlayCounter === 0) {
                document.documentElement.classList.remove('pageOverlayActive');
            }
        }
    }
    exports.pageOverlayClose = pageOverlayClose;
    /**
     * Returns true if at least one page overlay is currently open.
     *
     * @returns {boolean}
     */
    function pageOverlayIsActive() {
        return _pageOverlayCounter > 0;
    }
    exports.pageOverlayIsActive = pageOverlayIsActive;
    /**
     * Sets the dialog container element. This method is used to
     * circumvent a possible circular dependency, due to `Ui/Dialog`
     * requiring the `Ui/Screen` module itself.
     */
    function setDialogContainer(container) {
        _dialogContainer = container;
    }
    exports.setDialogContainer = setDialogContainer;
    function _getQueryObject(query) {
        if (typeof query !== 'string' || query.trim() === '') {
            throw new TypeError('Expected a non-empty string for parameter \'query\'.');
        }
        // Microsoft Edge rewrites the media queries to whatever it
        // pleases, causing the input and output query to mismatch
        if (_mqMapEdge.has(query))
            query = _mqMapEdge.get(query);
        if (_mqMap.has(query))
            query = _mqMap.get(query);
        let queryObject = _mql.get(query);
        if (!queryObject) {
            queryObject = {
                callbacksMatch: new Map(),
                callbacksUnmatch: new Map(),
                callbacksSetup: new Map(),
                mql: window.matchMedia(query),
            };
            //noinspection JSDeprecatedSymbols
            queryObject.mql.addListener(_mqlChange);
            _mql.set(query, queryObject);
            if (query !== queryObject.mql.media) {
                _mqMapEdge.set(queryObject.mql.media, query);
            }
        }
        return queryObject;
    }
    /**
     * Triggered whenever a registered media query now matches or no longer matches.
     */
    function _mqlChange(event) {
        const queryObject = _getQueryObject(event.media);
        if (event.matches) {
            if (queryObject.callbacksSetup.size) {
                queryObject.callbacksSetup.forEach(function (callback) {
                    callback();
                });
                // discard all setup callbacks after execution
                queryObject.callbacksSetup = new Map();
            }
            else {
                queryObject.callbacksMatch.forEach(function (callback) {
                    callback();
                });
            }
        }
        else {
            queryObject.callbacksUnmatch.forEach(function (callback) {
                callback();
            });
        }
    }
});

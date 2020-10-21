var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "../Dom/Util"], function (require, exports, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.element = void 0;
    Util_1 = __importDefault(Util_1);
    let _callback = null;
    let _offset = null;
    let _timeoutScroll = null;
    /**
     * Monitors scroll event to only execute the callback once scrolling has ended.
     */
    function onScroll() {
        if (_timeoutScroll !== null) {
            window.clearTimeout(_timeoutScroll);
        }
        _timeoutScroll = window.setTimeout(() => {
            if (_callback !== null) {
                _callback();
            }
            window.removeEventListener('scroll', onScroll);
            _callback = null;
            _timeoutScroll = null;
        }, 100);
    }
    /**
     * Scrolls to target element, optionally invoking the provided callback once scrolling has ended.
     *
     * @param       {Element}       element         target element
     * @param       {function=}     callback        callback invoked once scrolling has ended
     */
    function element(element, callback) {
        if (!(element instanceof HTMLElement)) {
            throw new TypeError("Expected a valid DOM element.");
        }
        else if (callback !== undefined && typeof callback !== 'function') {
            throw new TypeError("Expected a valid callback function.");
        }
        else if (!document.body.contains(element)) {
            throw new Error("Element must be part of the visible DOM.");
        }
        else if (_callback !== null) {
            throw new Error("Cannot scroll to element, a concurrent request is running.");
        }
        if (callback) {
            _callback = callback;
            window.addEventListener('scroll', onScroll);
        }
        let y = Util_1.default.offset(element).top;
        if (_offset === null) {
            _offset = 50;
            const pageHeader = document.getElementById('pageHeaderPanel');
            if (pageHeader !== null) {
                const position = window.getComputedStyle(pageHeader).position;
                if (position === 'fixed' || position === 'static') {
                    _offset = pageHeader.offsetHeight;
                }
                else {
                    _offset = 0;
                }
            }
        }
        if (_offset > 0) {
            if (y <= _offset) {
                y = 0;
            }
            else {
                // add an offset to account for a sticky header
                y -= _offset;
            }
        }
        const offset = window.pageYOffset;
        window.scrollTo({
            left: 0,
            top: y,
            behavior: 'smooth',
        });
        window.setTimeout(() => {
            // no scrolling took place
            if (offset === window.pageYOffset) {
                onScroll();
            }
        }, 100);
    }
    exports.element = element;
});

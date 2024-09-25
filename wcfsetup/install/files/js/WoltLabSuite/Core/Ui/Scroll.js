define(["require", "exports", "tslib", "../Dom/Util"], function (require, exports, tslib_1, Util_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.element = element;
    Util_1 = tslib_1.__importDefault(Util_1);
    let _callbacks = [];
    let _offset = null;
    let _targetElement = undefined;
    let _timeoutScroll = null;
    /**
     * Monitors scroll event to only execute the callback once scrolling has ended.
     */
    function onScroll() {
        if (_timeoutScroll !== null) {
            window.clearTimeout(_timeoutScroll);
        }
        _timeoutScroll = window.setTimeout(() => {
            for (const callback of _callbacks) {
                callback();
            }
            window.removeEventListener("scroll", onScroll);
            _callbacks = [];
            _targetElement = undefined;
            _timeoutScroll = null;
        }, 100);
    }
    /**
     * Scrolls to target element, optionally invoking the provided callback once scrolling has ended.
     *
     * @param       {Element}       element         target element
     * @param       {function=}     callback        callback invoked once scrolling has ended
     */
    function element(element, callback, behavior = "smooth") {
        if (!(element instanceof HTMLElement)) {
            throw new TypeError("Expected a valid DOM element.");
        }
        else if (callback !== undefined && typeof callback !== "function") {
            throw new TypeError("Expected a valid callback function.");
        }
        else if (!document.body.contains(element)) {
            throw new Error("Element must be part of the visible DOM.");
        }
        else if (_callbacks.length > 0) {
            if (element !== _targetElement) {
                throw new Error("Cannot scroll to element, a concurrent request is running.");
            }
        }
        if (callback) {
            _callbacks.push(callback);
        }
        if (_targetElement !== undefined) {
            return;
        }
        _targetElement = element;
        window.addEventListener("scroll", onScroll);
        let y = Util_1.default.offset(element).top;
        if (_offset === null) {
            _offset = 50;
            const pageHeader = document.getElementById("pageHeaderPanel");
            if (pageHeader !== null) {
                const position = window.getComputedStyle(pageHeader).position;
                if (position === "fixed" || position === "static") {
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
            behavior,
        });
        window.setTimeout(() => {
            // no scrolling took place
            if (offset === window.pageYOffset) {
                onScroll();
            }
        }, 100);
    }
});

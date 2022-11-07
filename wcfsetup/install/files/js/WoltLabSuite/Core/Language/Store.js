/**
 * @deprecated 6.0 Access window.WoltLabLanguageStore directly.
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.add = exports.get = void 0;
    function get(key, parameters = {}) {
        return window.WoltLabLanguageStore.get(key, parameters);
    }
    exports.get = get;
    function add(key, value) {
        window.WoltLabLanguageStore.add(key, value);
    }
    exports.add = add;
});

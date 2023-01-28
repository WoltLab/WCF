define(["require", "exports", "../../Core"], function (require, exports, Core_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.removeExpiredDrafts = exports.saveDraft = void 0;
    function getLocalStorageKey(identifier) {
        return `${(0, Core_1.getStoragePrefix)()}ckeditor5-${identifier}`;
    }
    function saveDraft(identifier, html) {
        const payload = {
            html,
            timestamp: Date.now(),
        };
        try {
            window.localStorage.setItem(getLocalStorageKey(identifier), JSON.stringify(payload));
        }
        catch (e) {
            console.warn("Unable to write to the local storage.", e);
        }
    }
    exports.saveDraft = saveDraft;
    function removeExpiredDrafts() {
        const oneWeekAgo = Date.now() - 7 * 86400;
        Object.keys(localStorage)
            .filter((key) => key.startsWith(`ckeditor5-`))
            .forEach((key) => {
            let value;
            try {
                value = window.localStorage.getItem(key);
            }
            catch {
                // Nothing we can do, forget it.
                return;
            }
            if (value === null) {
                // The value is no longer available.
                return;
            }
            let payload = undefined;
            try {
                payload = JSON.parse(value);
            }
            catch {
                // `payload` remains set to `undefined`.
            }
            if (payload === undefined || payload.timestamp < oneWeekAgo) {
                try {
                    localStorage.removeItem(key);
                }
                catch {
                    // Nothing we can do, forget it.
                }
            }
        });
    }
    exports.removeExpiredDrafts = removeExpiredDrafts;
});

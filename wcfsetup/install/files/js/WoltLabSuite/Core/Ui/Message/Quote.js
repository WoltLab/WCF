/**
 * @woltlabExcludeBundle tiny
 *
 * @deprecated 6.2 use `WoltLabSuite/Core/Component/Quote/Message` instead
 */
define(["require", "exports", "WoltLabSuite/Core/Component/Quote/Message"], function (require, exports, Message_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.UiMessageQuote = void 0;
    /**
     * @deprecated 6.2 Use `registerContainer()` without the className parameter.
     */
    class UiMessageQuote {
        /**
         * Initializes the quote handler for given object type.
         */
        constructor(_quoteManager, className, objectType, containerSelector, messageBodySelector, _messageContentSelector, _supportDirectInsert) {
            (0, Message_1.registerContainer)(containerSelector, messageBodySelector, objectType, className);
        }
    }
    exports.UiMessageQuote = UiMessageQuote;
    exports.default = UiMessageQuote;
});

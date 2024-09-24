define(["require", "exports", "WoltLabSuite/Core/Helper/Selector"], function (require, exports, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    (0, Selector_1.wheneverFirstSeen)("emoji-picker", (emojiPicker) => {
        emojiPicker.locale = window.LANGUAGE_CODE;
        emojiPicker.dataSource = `${window.WSC_API_URL}emoji/index.php?l=${window.LANGUAGE_CODE}`;
        if (window.EmojiPickerLocales[window.LANGUAGE_CODE] !== undefined) {
            emojiPicker.i18n = window.EmojiPickerLocales[window.LANGUAGE_CODE];
        }
    });
});

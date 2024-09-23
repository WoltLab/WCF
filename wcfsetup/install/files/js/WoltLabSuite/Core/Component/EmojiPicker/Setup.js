define(["require", "exports", "WoltLabSuite/Core/Helper/Selector"], function (require, exports, Selector_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    (0, Selector_1.wheneverFirstSeen)("emoji-picker", (emojiPicker) => {
        emojiPicker.locale = window.LANGUAGE_CODE;
        // TODO host local emoji data
        emojiPicker.dataSource = `https://cdn.jsdelivr.net/npm/emoji-picker-element-data@^1/${window.LANGUAGE_CODE}/cldr-native/data.json`;
        if (window.EmojiPickerLocales[window.LANGUAGE_CODE] !== undefined) {
            emojiPicker.i18n = window.EmojiPickerLocales[window.LANGUAGE_CODE];
        }
    });
});

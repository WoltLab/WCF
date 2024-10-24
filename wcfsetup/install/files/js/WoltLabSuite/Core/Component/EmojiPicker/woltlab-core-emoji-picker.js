define(["require", "exports", "emoji-picker-element", "WoltLabSuite/Core/Component/EmojiPicker/Localization", "emoji-picker-element"], function (require, exports, emoji_picker_element_1, Localization_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreEmojiPicker = exports.DATABASE_FOR_AUTO_COMPLETE = void 0;
    exports.DATABASE_FOR_AUTO_COMPLETE = new emoji_picker_element_1.Database({
        dataSource: (0, Localization_1.getDataSource)("en"),
        locale: "en",
    });
    class WoltlabCoreEmojiPicker extends emoji_picker_element_1.Picker {
        constructor(props) {
            const locale = (props && props.locale) || document.documentElement.lang;
            super({
                locale: locale,
                ...(props || {}),
                dataSource: (0, Localization_1.getDataSource)(locale),
                i18n: (0, Localization_1.getLocalizationData)(locale),
            });
        }
        static get observedAttributes() {
            return [];
        }
        focus() {
            this.shadowRoot.querySelector(".search").focus();
        }
    }
    exports.WoltlabCoreEmojiPicker = WoltlabCoreEmojiPicker;
    customElements.define("woltlab-core-emoji-picker", WoltlabCoreEmojiPicker);
});

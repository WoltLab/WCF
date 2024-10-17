define(["require", "exports", "emoji-picker-element", "WoltLabSuite/Core/Component/EmojiPicker/Localization", "emoji-picker-element"], function (require, exports, emoji_picker_element_1, Localization_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreEmojiPicker = void 0;
    function getDataSource(locale) {
        return `${window.WSC_API_URL}emoji/${locale}.json`;
    }
    class WoltlabCoreEmojiPicker extends emoji_picker_element_1.Picker {
        constructor(props) {
            const locale = (props && props.locale) || document.documentElement.lang;
            super({
                locale: locale,
                ...(props || {}),
                dataSource: getDataSource(locale),
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
    void customElements.whenDefined("emoji-picker").then(() => {
        customElements.define("woltlab-core-emoji-picker", WoltlabCoreEmojiPicker, {
            extends: "emoji-picker",
        });
    });
});

define(["require", "exports", "tslib", "emoji-picker-element/i18n/de", "emoji-picker-element/i18n/en", "emoji-picker-element/i18n/es", "emoji-picker-element/i18n/fr", "emoji-picker-element/i18n/it", "emoji-picker-element/i18n/nl", "emoji-picker-element/i18n/pl", "emoji-picker-element/i18n/pt_BR", "emoji-picker-element/i18n/pt_PT", "emoji-picker-element/i18n/ru_RU", "emoji-picker-element", "emoji-picker-element"], function (require, exports, tslib_1, de_1, en_1, es_1, fr_1, it_1, nl_1, pl_1, pt_BR_1, pt_PT_1, ru_RU_1, emoji_picker_element_1) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.WoltlabCoreEmojiPicker = void 0;
    de_1 = tslib_1.__importDefault(de_1);
    en_1 = tslib_1.__importDefault(en_1);
    es_1 = tslib_1.__importDefault(es_1);
    fr_1 = tslib_1.__importDefault(fr_1);
    it_1 = tslib_1.__importDefault(it_1);
    nl_1 = tslib_1.__importDefault(nl_1);
    pl_1 = tslib_1.__importDefault(pl_1);
    pt_BR_1 = tslib_1.__importDefault(pt_BR_1);
    pt_PT_1 = tslib_1.__importDefault(pt_PT_1);
    ru_RU_1 = tslib_1.__importDefault(ru_RU_1);
    const EmojiPickerLocales = {
        de: de_1.default,
        en: en_1.default,
        es: es_1.default,
        fr: fr_1.default,
        it: it_1.default,
        nl: nl_1.default,
        pl: pl_1.default,
        "pt-br": pt_BR_1.default,
        "pt-pt": pt_PT_1.default,
        "ru-ru": ru_RU_1.default,
    };
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
                ...(Object.hasOwn(EmojiPickerLocales, locale) ? { i18n: EmojiPickerLocales[locale] } : {}),
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

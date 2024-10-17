import de from "emoji-picker-element/i18n/de";
import en from "emoji-picker-element/i18n/en";
import es from "emoji-picker-element/i18n/es";
import fr from "emoji-picker-element/i18n/fr";
import it from "emoji-picker-element/i18n/it";
import nl from "emoji-picker-element/i18n/nl";
import pl from "emoji-picker-element/i18n/pl";
import pt_BR from "emoji-picker-element/i18n/pt_BR";
import pt_PT from "emoji-picker-element/i18n/pt_PT";
import ru_RU from "emoji-picker-element/i18n/ru_RU";
import "emoji-picker-element";
import { PickerConstructorOptions, I18n } from "emoji-picker-element/shared";
import { Picker } from "emoji-picker-element";

const EmojiPickerLocales: { [key: string]: I18n } = {
  de,
  en,
  es,
  fr,
  it,
  nl,
  pl,
  "pt-br": pt_BR,
  "pt-pt": pt_PT,
  "ru-ru": ru_RU,
};

function getDataSource(locale: string): string {
  return `${window.WSC_API_URL}emoji/${locale}.json`;
}

export class WoltlabCoreEmojiPicker extends Picker {
  constructor(props: PickerConstructorOptions | null | undefined) {
    const locale = (props && props.locale) || document.documentElement.lang;

    super({
      locale: locale,
      ...(props || {}),
      dataSource: getDataSource(locale),
      ...(Object.hasOwn(EmojiPickerLocales, locale) ? { i18n: EmojiPickerLocales[locale] } : {}),
    });
  }

  static get observedAttributes(): string[] {
    return [];
  }

  focus() {
    this.shadowRoot!.querySelector<HTMLInputElement>(".search")!.focus();
  }
}

void customElements.whenDefined("emoji-picker").then(() => {
  customElements.define("woltlab-core-emoji-picker", WoltlabCoreEmojiPicker, {
    extends: "emoji-picker",
  });
});

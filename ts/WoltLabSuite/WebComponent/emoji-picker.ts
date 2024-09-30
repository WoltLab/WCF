import ar from "emoji-picker-element/i18n/ar";
import de from "emoji-picker-element/i18n/de";
import en from "emoji-picker-element/i18n/en";
import es from "emoji-picker-element/i18n/es";
import fr from "emoji-picker-element/i18n/fr";
import hi from "emoji-picker-element/i18n/hi";
import id from "emoji-picker-element/i18n/id";
import it from "emoji-picker-element/i18n/it";
import ms_MY from "emoji-picker-element/i18n/ms_MY";
import nl from "emoji-picker-element/i18n/nl";
import pl from "emoji-picker-element/i18n/pl";
import pt_BR from "emoji-picker-element/i18n/pt_BR";
import pt_PT from "emoji-picker-element/i18n/pt_PT";
import ru_RU from "emoji-picker-element/i18n/ru_RU";
import tr from "emoji-picker-element/i18n/tr";
import zh_CN from "emoji-picker-element/i18n/zh_CN";
import "emoji-picker-element";
import { PickerConstructorOptions, I18n, CustomEmoji } from "emoji-picker-element/shared";
import { Picker } from "emoji-picker-element";

const EmojiPickerLocales: { [key: string]: I18n } = {
  ar,
  de,
  en,
  es,
  fr,
  hi,
  id,
  it,
  ms_MY,
  nl,
  pl,
  pt_BR,
  pt_PT,
  ru_RU,
  tr,
  zh_CN,
};

function getDataSource(locale: string): string {
  return `${window.WSC_API_URL}emoji/index.php?l=${locale}`;
}

declare module "emoji-picker-element" {
  interface Picker {
    _ctx: {
      locale: string;
      dataSource: string;
      customEmoji?: CustomEmoji[];
      i18n: I18n;
    };
    _dbFlush: () => void;
  }
}

void customElements.whenDefined("emoji-picker").then(() => {
  class WoltlabCoreEmojiPicker extends Picker {
    constructor(props: PickerConstructorOptions | null | undefined) {
      const locale = (props && props.locale) || window.LANGUAGE_CODE;

      super({
        locale: locale,
        ...(props || {}),
        dataSource: getDataSource(locale),
        ...(Object.hasOwn(EmojiPickerLocales, locale) ? { i18n: EmojiPickerLocales[locale] } : {}),
      });
    }

    static get observedAttributes(): string[] {
      return ["locale"];
    }

    attributeChangedCallback(attrName: string, oldVal: string | null, newVal: string | null) {
      if (attrName !== "locale") return;
      if (!newVal) return;

      if (oldVal !== newVal) {
        this._ctx["locale"] = newVal;
        this._ctx["dataSource"] = getDataSource(newVal);
        if (Object.hasOwn(EmojiPickerLocales, newVal)) {
          this._ctx["i18n"] = EmojiPickerLocales[newVal];
        }
        this._dbFlush();
      }
    }

    focus() {
      this.shadowRoot!.querySelector<HTMLInputElement>(".search")!.focus();
    }
  }

  customElements.define("woltlab-core-emoji-picker", WoltlabCoreEmojiPicker, {
    extends: "emoji-picker",
  });
});

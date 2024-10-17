import "emoji-picker-element";
import { PickerConstructorOptions } from "emoji-picker-element/shared";
import { Picker } from "emoji-picker-element";
import { getLocalizationData } from "WoltLabSuite/Core/Component/EmojiPicker/Localization";

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
      i18n: getLocalizationData(locale),
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

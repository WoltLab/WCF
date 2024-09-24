import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { Picker } from "emoji-picker-element";

wheneverFirstSeen("emoji-picker", (emojiPicker: Picker) => {
  emojiPicker.locale = window.LANGUAGE_CODE;
  emojiPicker.dataSource = `${window.WSC_API_URL}emoji/index.php?l=${window.LANGUAGE_CODE}`;

  if (Object.hasOwn(window.EmojiPickerLocales, window.LANGUAGE_CODE)) {
    emojiPicker.i18n = window.EmojiPickerLocales[window.LANGUAGE_CODE];
  }
});

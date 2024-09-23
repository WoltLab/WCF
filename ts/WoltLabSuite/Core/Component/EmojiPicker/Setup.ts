import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { Picker } from "emoji-picker-element";

wheneverFirstSeen("emoji-picker", (emojiPicker: Picker) => {
  emojiPicker.locale = window.LANGUAGE_CODE;

  if (window.EmojiPickerLocales[window.LANGUAGE_CODE] !== undefined) {
    emojiPicker.i18n = window.EmojiPickerLocales[window.LANGUAGE_CODE];
  }
});

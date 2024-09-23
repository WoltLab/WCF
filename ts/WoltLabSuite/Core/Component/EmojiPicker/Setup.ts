import { wheneverFirstSeen } from "WoltLabSuite/Core/Helper/Selector";
import { Picker } from "emoji-picker-element";

wheneverFirstSeen("emoji-picker", (emojiPicker: Picker) => {
  emojiPicker.locale = window.LANGUAGE_CODE;
  // TODO host local emoji data
  emojiPicker.dataSource = `https://cdn.jsdelivr.net/npm/emoji-picker-element-data@^1/${window.LANGUAGE_CODE}/cldr-native/data.json`;

  if (window.EmojiPickerLocales[window.LANGUAGE_CODE] !== undefined) {
    emojiPicker.i18n = window.EmojiPickerLocales[window.LANGUAGE_CODE];
  }
});

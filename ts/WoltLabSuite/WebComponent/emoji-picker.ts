import { wheneverSeen } from "WoltLabSuite/Core/Helper/Selector";
import { Picker } from "emoji-picker-element";

import "emoji-picker-element";

void import("emoji-picker-element/i18n/de").then((emojiLanguage) => {
  wheneverSeen("emoji-picker", (emojiPicker: Picker) => {
    emojiPicker.locale = window.LANGUAGE_CODE;
    emojiPicker.i18n = emojiLanguage.default;
  });
});

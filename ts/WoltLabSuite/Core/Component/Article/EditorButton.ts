import { getPhrase } from "../../Language";
import { open as searchArticle } from "../../Ui/Article/Search";

import type { CKEditor, CkeditorConfigurationEvent, CkeditorReadyEvent } from "../Ckeditor";

function setupBbcode(editor: CKEditor): void {
  editor.sourceElement.addEventListener("bbcode", (evt: CustomEvent<string>) => {
    const bbcode = evt.detail;
    if (bbcode === "wsa") {
      evt.preventDefault();

      searchArticle((articleId) => {
        editor.insertText(`[wsa='${articleId}'][/wsa]`);
      });
    }
  });
}

export function setup(element: HTMLElement) {
  element.addEventListener(
    "ckeditor5:configuration",
    (event: CkeditorConfigurationEvent) => {
      const { configuration } = event.detail;
      (configuration as any).woltlabBbcode.push({
        icon: "file-word;false",
        name: "wsa",
        label: getPhrase("wcf.editor.button.article"),
      });
    },
    { once: true },
  );

  element.addEventListener(
    "ckeditor5:ready",
    (event: CkeditorReadyEvent) => {
      setupBbcode(event.detail);
    },
    { once: true },
  );
}

import { getPhrase } from "../../Language";
import { open as searchArticle } from "../../Ui/Article/Search";
import { listenToCkeditor } from "../Ckeditor/Event";

import type { CKEditor, CkeditorConfigurationEvent } from "../Ckeditor";

function setupBbcode(ckeditor: CKEditor): void {
  ckeditor.sourceElement.addEventListener("bbcode", (evt: CustomEvent<string>) => {
    const bbcode = evt.detail;
    if (bbcode === "wsa") {
      evt.preventDefault();

      searchArticle((articleId) => {
        ckeditor.insertText(`[wsa='${articleId}'][/wsa]`);
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

  listenToCkeditor(element).ready((ckeditor) => {
    setupBbcode(ckeditor);
  });
}

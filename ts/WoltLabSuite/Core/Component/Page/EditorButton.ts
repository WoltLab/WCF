import { getPhrase } from "../../Language";
import { open as searchPage } from "../../Ui/Page/Search";

import type { CKEditor, CkeditorConfigurationEvent, CkeditorReadyEvent } from "../Ckeditor";

function setupBbcode(editor: CKEditor): void {
  editor.sourceElement.addEventListener("bbcode", (evt: CustomEvent<string>) => {
    const bbcode = evt.detail;
    if (bbcode === "wsp") {
      evt.preventDefault();

      searchPage((articleId) => {
        editor.insertText(`[wsp='${articleId}'][/wsp]`);
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
        icon: "file-lines;false",
        name: "wsp",
        label: getPhrase("wcf.editor.button.page"),
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

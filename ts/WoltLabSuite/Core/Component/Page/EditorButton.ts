import { getPhrase } from "../../Language";
import { open as searchPage } from "../../Ui/Page/Search";
import { listenToCkeditor } from "../Ckeditor/Event";

import type { CKEditor, CkeditorConfigurationEvent } from "../Ckeditor";

function setupBbcode(ckeditor: CKEditor): void {
  ckeditor.sourceElement.addEventListener("bbcode", (evt: CustomEvent<string>) => {
    const bbcode = evt.detail;
    if (bbcode === "wsp") {
      evt.preventDefault();

      searchPage((articleId) => {
        ckeditor.insertText(`[wsp='${articleId}'][/wsp]`);
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

  listenToCkeditor(element).ready((ckeditor) => {
    setupBbcode(ckeditor);
  });
}

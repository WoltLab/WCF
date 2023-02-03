import { getPhrase } from "../../Language";
import { open as searchPage } from "../../Ui/Page/Search";
import { listenToCkeditor } from "../Ckeditor/Event";

import type { CKEditor } from "../Ckeditor";

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
  listenToCkeditor(element).configuration(({ configuration }) => {
    (configuration as any).woltlabBbcode.push({
      icon: "file-lines;false",
      name: "wsp",
      label: getPhrase("wcf.editor.button.page"),
    });
  });

  listenToCkeditor(element).ready((ckeditor) => {
    setupBbcode(ckeditor);
  });
}

import { getPhrase } from "../../Language";
import { open as searchPage } from "../../Ui/Page/Search";
import { listenToCkeditor } from "../Ckeditor/Event";

import type { CKEditor } from "../Ckeditor";

function setupBbcode(ckeditor: CKEditor): void {
  listenToCkeditor(ckeditor.sourceElement).bbcode(({ bbcode }) => {
    if (bbcode !== "wsp") {
      return false;
    }

    searchPage((articleId) => {
      ckeditor.insertText(`[wsp='${articleId}'][/wsp]`);
    });

    return true;
  });
}

export function setup(element: HTMLElement) {
  listenToCkeditor(element).setupConfiguration(({ configuration }) => {
    (configuration as any).woltlabBbcode.push({
      icon: "file-lines;false",
      name: "wsp",
      label: getPhrase("wcf.editor.button.page"),
    });
  });

  listenToCkeditor(element).ready(({ ckeditor }) => {
    setupBbcode(ckeditor);
  });
}

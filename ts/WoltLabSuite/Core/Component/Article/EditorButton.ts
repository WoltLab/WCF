import { getPhrase } from "../../Language";
import { open as searchArticle } from "../../Ui/Article/Search";
import { listenToCkeditor } from "../Ckeditor/Event";

import type { CKEditor } from "../Ckeditor";

function setupBbcode(ckeditor: CKEditor): void {
  listenToCkeditor(ckeditor.sourceElement).bbcode(({ bbcode }) => {
    if (bbcode !== "wsa") {
      return false;
    }

    searchArticle((articleId) => {
      ckeditor.insertText(`[wsa='${articleId}'][/wsa]`);
    });

    return true;
  });
}

export function setup(element: HTMLElement) {
  listenToCkeditor(element).setupConfiguration(({ configuration }) => {
    (configuration as any).woltlabBbcode.push({
      icon: "file-word;false",
      name: "wsa",
      label: getPhrase("wcf.editor.button.article"),
    });
  });

  listenToCkeditor(element).ready(({ ckeditor }) => {
    setupBbcode(ckeditor);
  });
}

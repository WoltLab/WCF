import type { CKEditor } from "../Ckeditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import { getPhrase } from "../../Language";
import { open as searchArticle } from "../../Ui/Article/Search";

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
    (event: CustomEvent<EditorConfig>) => {
      (event.detail as any).woltlabBbcode.push({
        icon: "file-word;false",
        name: "wsa",
        label: getPhrase("wcf.editor.button.article"),
      });
    },
    { once: true },
  );

  element.addEventListener(
    "ckeditor5:ready",
    (event: CustomEvent<CKEditor>) => {
      setupBbcode(event.detail);
    },
    { once: true },
  );
}

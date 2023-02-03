import type { CKEditor } from "../Ckeditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import { getPhrase } from "../../Language";
import { open as searchPage } from "../../Ui/Page/Search";

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
    (event: CustomEvent<EditorConfig>) => {
      (event.detail as any).woltlabBbcode.push({
        icon: "file-lines;false",
        name: "wsp",
        label: getPhrase("wcf.editor.button.page"),
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

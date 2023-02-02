import type { CKEditor } from "../Ckeditor";
import type { EditorConfig } from "@ckeditor/ckeditor5-core/src/editor/editorconfig";
import { getPhrase } from "../../Language";

function setupBbcode(editor: CKEditor): void {
  editor.sourceElement.addEventListener("bbcode", (evt: CustomEvent<string>) => {
    const bbcode = evt.detail;
    if (bbcode === "wsa") {
      evt.preventDefault();

      console.log("wsa!");
    }
  });
}

export function setup(element: HTMLElement) {
  element.addEventListener(
    "ckeditor5:config",
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

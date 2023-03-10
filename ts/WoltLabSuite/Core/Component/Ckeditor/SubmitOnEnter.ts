import { dispatchToCkeditor } from "./Event";

import type { CKEditor } from "../Ckeditor";
import type ClassicEditor from "@ckeditor/ckeditor5-editor-classic/src/classiceditor";

export function setup(editor: ClassicEditor, ckeditor: CKEditor): void {
  editor.editing.view.document.on(
    "enter",
    (evt, data) => {
      // Shift+Enter is allowed to create line breaks.
      if (data.isSoft) {
        return;
      }

      data.preventDefault();
      evt.stop();

      const html = ckeditor.getHtml();
      if (html !== "") {
        dispatchToCkeditor(ckeditor.sourceElement).submitOnEnter({
          ckeditor,
          html,
        });
      }
    },
    { priority: "high" },
  );
}

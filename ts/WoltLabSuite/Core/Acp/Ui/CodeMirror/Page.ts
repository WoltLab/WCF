import * as UiPageSearch from "../../../Ui/Page/Search";
import { insertText } from "../../../Component/CodeMirror";

class AcpUiCodeMirrorPage {
  private element: HTMLTextAreaElement;

  constructor(elementId: string) {
    this.element = document.getElementById(elementId) as HTMLTextAreaElement;

    const insertButton = document.getElementById(`codemirror-${elementId}-page`)!;
    insertButton.addEventListener("click", () => this._click());
  }

  private _click(): void {
    UiPageSearch.open((pageID) => this._insert(pageID));
  }

  _insert(pageID: string): void {
    insertText(this.element, `{{ page="${pageID}" }}`);
  }
}

export = AcpUiCodeMirrorPage;

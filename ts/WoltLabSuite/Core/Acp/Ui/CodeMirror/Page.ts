import * as UiPageSearch from "../../../Ui/Page/Search";

class AcpUiCodeMirrorPage {
  private element: HTMLElement;

  constructor(elementId: string) {
    this.element = document.getElementById(elementId)!;

    const insertButton = document.getElementById(`codemirror-${elementId}-page`)!;
    insertButton.addEventListener("click", () => this._click());
  }

  private _click(): void {
    UiPageSearch.open((pageID) => this._insert(pageID));
  }

  _insert(pageID: string): void {
    (this.element as any).codemirror.replaceSelection(`{{ page="${pageID}" }}`);
  }
}

export = AcpUiCodeMirrorPage;

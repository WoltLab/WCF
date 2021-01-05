import * as Core from "../../../Core";
import * as UiPageSearch from "../../../Ui/Page/Search";

class AcpUiCodeMirrorPage {
  private element: HTMLElement;

  constructor(elementId: string) {
    this.element = document.getElementById(elementId)!;

    const insertButton = document.getElementById(`codemirror-${elementId}-page`)!;
    insertButton.addEventListener("click", (ev) => this._click(ev));
  }

  private _click(event: MouseEvent): void {
    event.preventDefault();

    UiPageSearch.open((pageID) => this._insert(pageID));
  }

  _insert(pageID: string): void {
    (this.element as any).codemirror.replaceSelection(`{{ page="${pageID}" }}`);
  }
}

Core.enableLegacyInheritance(AcpUiCodeMirrorPage);

export = AcpUiCodeMirrorPage;

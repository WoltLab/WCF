import DialogControls from "./Controls";

export class DialogSetup {
  fromElement(element: HTMLElement | DocumentFragment): DialogControls {
    if (!(element instanceof HTMLElement) && !(element instanceof DocumentFragment)) {
      throw new TypeError("Expected an HTML element or a document fragment.");
    }

    const dialog = document.createElement("woltlab-core-dialog");
    dialog.content.append(element);

    return new DialogControls(dialog);
  }

  fromId(id: string): DialogControls {
    const element = document.getElementById(id);
    if (element === null) {
      throw new Error(`Unable to find the element identified by '${id}'.`);
    }

    return this.fromElement(element);
  }

  fromHtml(html: string): DialogControls {
    const element = document.createElement("div");
    element.innerHTML = html;
    if (element.childElementCount === 0) {
      throw new TypeError("The provided HTML string did not contain any elements.");
    }

    const fragment = document.createDocumentFragment();
    fragment.append(...element.childNodes);

    return this.fromElement(fragment);
  }
}

export default DialogSetup;

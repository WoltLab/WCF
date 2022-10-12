import DialogControls from "./Controls";

export class DialogSetup {
  fromElement(element: HTMLElement | DocumentFragment): DialogControls {
    if (element instanceof HTMLTemplateElement) {
      element = element.content.cloneNode(true) as DocumentFragment;
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
    if (element.childElementCount === 0 && element.textContent!.trim() === "") {
      throw new TypeError("The provided HTML string was empty.");
    }

    const fragment = document.createDocumentFragment();
    fragment.append(...element.childNodes);

    return this.fromElement(fragment);
  }

  withoutContent(): DialogControls {
    const dialog = document.createElement("woltlab-core-dialog");

    return new DialogControls(dialog);
  }
}

export default DialogSetup;

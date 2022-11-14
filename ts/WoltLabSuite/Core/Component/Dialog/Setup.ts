/**
 * Helper module to expose a fluent API to create
 * dialogs through `dialogFactory()`.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module WoltLabSuite/Core/Component/Dialog/Setup
 * @since 6.0
 */

import DialogControls from "./Controls";
import * as DomUtil from "../../Dom/Util";

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
    const fragment = DomUtil.createFragmentFromHtml(html);
    if (fragment.childElementCount === 0 && fragment.textContent!.trim() === "") {
      throw new TypeError("The provided HTML string was empty.");
    }

    return this.fromElement(fragment);
  }

  withoutContent(): DialogControls {
    const dialog = document.createElement("woltlab-core-dialog");

    return new DialogControls(dialog);
  }
}

export default DialogSetup;

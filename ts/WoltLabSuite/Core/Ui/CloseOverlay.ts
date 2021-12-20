/**
 * Allows to be informed when a click event bubbled up to the document's body.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  Ui/CloseOverlay (alias)
 * @module  WoltLabSuite/Core/Ui/CloseOverlay
 */

import CallbackList from "../CallbackList";

const _callbackList = new CallbackList();

export enum Origin {
  Document = "document",
  DropDown = "dropdown",
}

type Callback = (origin?: string | Origin, identifier?: string) => void;

let hasGlobalListener = false;
export function add(identifier: string, callback: Callback): void {
  _callbackList.add(identifier, callback);

  if (!hasGlobalListener) {
    document.body.addEventListener("click", () => {
      execute(Origin.Document);
    });

    hasGlobalListener = true;
  }
}

export function remove(identifier: string): void {
  _callbackList.remove(identifier);
}

export function execute(): void;
export function execute(origin: string | Origin): void;
export function execute(origin: string | Origin, identifier: string): void;
export function execute(origin?: string | Origin, identifier?: string): void {
  _callbackList.forEach(null, (callback) => callback(origin, identifier));
}

// This is required for the backwards compatibility with WSC <= 5.4.
const UiCloseOverlay = {
  add,
  remove,
  execute,
};
export default UiCloseOverlay;

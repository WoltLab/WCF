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

const UiCloseOverlay = {
  /**
   * @see CallbackList.add
   */
  add: _callbackList.add.bind(_callbackList),

  /**
   * @see CallbackList.remove
   */
  remove: _callbackList.remove.bind(_callbackList),

  /**
   * Invokes all registered callbacks.
   */
  execute() {
    _callbackList.forEach(null, (callback) => callback());
  },
};

document.body.addEventListener("click", UiCloseOverlay.execute);

export = UiCloseOverlay;

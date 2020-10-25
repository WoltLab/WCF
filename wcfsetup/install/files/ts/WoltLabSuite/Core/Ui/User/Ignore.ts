/**
 * Provides global helper methods to interact with ignored content.
 *
 * @author  Alexander Ebert
 * @copyright  2001-2019 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/User/Ignore
 */

import DomChangeListener from '../../Dom/Change/Listener';

const _availableMessages = document.getElementsByClassName('ignoredUserMessage');
const _knownMessages = new Set<HTMLElement>();

/**
 * Adds ignored messages to the collection.
 *
 * @protected
 */
function rebuild() {
  for (let i = 0, length = _availableMessages.length; i < length; i++) {
    const message = _availableMessages[i] as HTMLElement;

    if (!_knownMessages.has(message)) {
      message.addEventListener('click', showMessage, {once: true});

      _knownMessages.add(message);
    }
  }
}

/**
 * Reveals a message on click/tap and disables the listener.
 */
function showMessage(event: MouseEvent): void {
  event.preventDefault();

  const message = event.currentTarget as HTMLElement;
  message.classList.remove('ignoredUserMessage');
  _knownMessages.delete(message);

  // Firefox selects the entire message on click for no reason
  window.getSelection()!.removeAllRanges();
}

/**
 * Initializes the click handler for each ignored message and listens for
 * newly inserted messages.
 */
export function init() {
  rebuild();

  DomChangeListener.add('WoltLabSuite/Core/Ui/User/Ignore', rebuild);
}

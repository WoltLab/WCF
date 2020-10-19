/**
 * Wrapper around the web browser's various clipboard APIs.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Clipboard
 */

export async function copyTextToClipboard(text: string): Promise<void> {
  if (navigator.clipboard) {
    return navigator.clipboard.writeText(text);
  }

  throw new Error("navigator.clipboard is not supported.");
}

export async function copyElementTextToClipboard(element: HTMLElement): Promise<void> {
  return copyTextToClipboard(element.textContent!);
}

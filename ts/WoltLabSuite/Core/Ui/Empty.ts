/**
 * Automatically reloads the page if `.jsReloadPageWhenEmpty` elements contain no child elements.
 *
 * @author  Matthias Schmidt
 * @copyright  2001-2021 WoltLab GmbH
 * @license  GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module  WoltLabSuite/Core/Ui/Empty
 */

import DomChangeListener from "../Dom/Change/Listener";

const observer = new MutationObserver((mutations) => {
  mutations.forEach((mutation) => {
    const target = mutation.target as HTMLElement;

    if (target.childElementCount === 0) {
      window.location.reload();
    } else {
      // Some elements may contain items, like a head row, that should not be considered when checking
      // whether the list is empty.
      const isEmpty = Array.from(target.children).every(
        (el: HTMLElement) => el.dataset.reloadPageWhenEmpty === "ignore",
      );
      if (isEmpty) {
        window.location.reload();
      }
    }
  });
});

function observeElements(): void {
  document.querySelectorAll(".jsReloadPageWhenEmpty").forEach((el) => {
    el.classList.remove("jsReloadPageWhenEmpty");
    observer.observe(el, {
      childList: true,
    });
  });
}
export function setup(): void {
  observeElements();
  DomChangeListener.add("WoltLabSuite/Core/Ui/Empty", () => observeElements());
}

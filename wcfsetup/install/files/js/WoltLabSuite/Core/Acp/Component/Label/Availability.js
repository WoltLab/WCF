/**
 * Recursively marks child items as available for the label group.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = setup;
    function observeListItem(listItem) {
        const checkbox = listItem.querySelector('input[type="checkbox"]');
        checkbox.addEventListener("change", () => {
            if (checkbox.checked) {
                const depth = parseInt(listItem.dataset.depth);
                let nextItem = listItem.nextElementSibling;
                while (nextItem !== null) {
                    const isChild = parseInt(nextItem.dataset.depth) > depth;
                    if (!isChild) {
                        break;
                    }
                    nextItem.querySelector('input[type="checkbox"]').checked = true;
                    nextItem = nextItem.nextElementSibling;
                }
            }
        });
    }
    function setup() {
        const listItems = Array.from(document.querySelectorAll("#connect .structuredList li"));
        if (listItems.length === 0) {
            return;
        }
        for (const listItem of listItems) {
            observeListItem(listItem);
        }
    }
});

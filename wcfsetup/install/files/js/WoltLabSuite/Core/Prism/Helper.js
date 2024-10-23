/**
 * Provide helper functions for prism processing.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2021 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
define(["require", "exports"], function (require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.splitIntoLines = splitIntoLines;
    function* splitIntoLines(container) {
        const it = document.createNodeIterator(container, NodeFilter.SHOW_TEXT, {
            acceptNode() {
                return NodeFilter.FILTER_ACCEPT;
            },
        });
        let line = document.createElement("span");
        let node;
        while ((node = it.nextNode())) {
            const text = node;
            const lines = text.data.split(/\r?\n/);
            for (let i = 0, max = lines.length; i < max; i++) {
                const codeLine = lines[i];
                // We are behind a newline, insert \n and create new container.
                if (i >= 1) {
                    line.appendChild(document.createTextNode("\n"));
                    yield line;
                    line = document.createElement("span");
                }
                let current = document.createTextNode(codeLine);
                // Copy hierarchy (to preserve CSS classes).
                let parent = text.parentNode;
                while (parent && parent !== container) {
                    const clone = parent.cloneNode(false);
                    clone.appendChild(current);
                    current = clone;
                    parent = parent.parentNode;
                }
                line.appendChild(current);
            }
        }
        yield line;
    }
});

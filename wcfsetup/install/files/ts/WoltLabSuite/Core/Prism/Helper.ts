/**
 * Provide helper functions for prism processing.
 *
 * @author	Tim Duesterhus
 * @copyright	2001-2021 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Prism/Helper
 */
export function splitIntoLines(container: Node): DocumentFragment {
  const frag = document.createDocumentFragment();
  let lineNo = 1;
  const newLine = () => {
    const line = document.createElement("span");
    line.dataset.number = lineNo.toString();
    lineNo++;
    frag.appendChild(line);
    return line;
  };

  const it = document.createNodeIterator(container, NodeFilter.SHOW_TEXT, {
    acceptNode() {
      return NodeFilter.FILTER_ACCEPT;
    },
  });

  let line = newLine();
  let node;
  while ((node = it.nextNode())) {
    const text = node as Text;
    text.data.split(/\r?\n/).forEach((codeLine, index) => {
      // We are behind a newline, insert \n and create new container.
      if (index >= 1) {
        line.appendChild(document.createTextNode("\n"));
        line = newLine();
      }

      let current: Node = document.createTextNode(codeLine);
      // Copy hierarchy (to preserve CSS classes).
      let parent = text.parentNode;
      while (parent && parent !== container) {
        const clone = parent.cloneNode(false);
        clone.appendChild(current);
        current = clone;
        parent = parent.parentNode;
      }
      line.appendChild(current);
    });
  }
  return frag;
}

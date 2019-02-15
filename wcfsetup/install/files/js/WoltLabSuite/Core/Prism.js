/**
 * Augments the Prism syntax highlighter with additional functions.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Prism
 */

window.Prism = window.Prism || {}
window.Prism.manual = true

define(['prism/prism'], function () {
	Prism.wscSplitIntoLines = function (container) {
		var frag = document.createDocumentFragment();
		var lineNo = 1;
		var it, node, line;
		
		function newLine() {
			var line = elCreate('span');
			elData(line, 'number', lineNo++);
			frag.appendChild(line);
			
			return line;
		}
		
		// IE11 expects a fourth, non-standard, parameter (entityReferenceExpansion) and a valid function as third
		it = document.createNodeIterator(container, NodeFilter.SHOW_TEXT, function () {
			return NodeFilter.FILTER_ACCEPT;
		}, false);
		
		line = newLine(lineNo);
		while (node = it.nextNode()) {
			node.data.split(/\r?\n/).forEach(function (codeLine, index) {
				var current, parent;
				
				// We are behind a newline, insert \n and create new container.
				if (index >= 1) {
					line.appendChild(document.createTextNode("\n"));
					line = newLine(lineNo);
				}
				
				current = document.createTextNode(codeLine);
				
				// Copy hierarchy (to preserve CSS classes).
				parent = node.parentNode
				while (parent !== container) {
					var clone = parent.cloneNode(false);
					clone.appendChild(current);
					current = clone;
					parent = parent.parentNode;
				}
				
				line.appendChild(current);
			});
		}
		
		return frag;
	};

	return Prism;
});

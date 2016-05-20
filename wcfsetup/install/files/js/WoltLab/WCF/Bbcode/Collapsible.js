/**
 * Generic handler for collapsible bbcode boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Bbcode/Collapsible
 */
define([], function() {
	"use strict";
	
	var _containers = elByClass('jsCollapsibleBbcode');
	
	/**
	 * @exports	WoltLab/WCF/Bbcode/Collapsible
	 */
	var BbcodeCollapsible = {
		observe: function() {
			var container, toggleButton;
			while (_containers.length) {
				container = _containers[0];
				container.classList.remove('jsCollapsibleBbcode');
				
				toggleButton = elBySel('.toggleButton');
				if (toggleButton === null) {
					continue;
				}
				
				(function(container, toggleButton) {
					var toggle = function() {
						var expand = container.classList.contains('collapsed');
						container.classList[expand ? 'remove' : 'add']('collapsed');
						toggleButton.textContent = elData(toggleButton, 'title-' + (expand ? 'collapse' : 'expand'));
					};
					
					toggleButton.addEventListener(WCF_CLICK_EVENT, toggle);
					
					// searching in a page causes Google Chrome to scroll
					// the box if something inside it matches
					// 
					// expand the box in this case, to:
					// a) Improve UX
					// b) Hide an ugly misplaced "show all" button
					container.addEventListener('scroll', toggle);
					
					// expand boxes that are initially scrolled
					if (container.scrollTop !== 0) {
						toggle();
					}
				})(container, toggleButton);
			}
		}
	};
	
	return BbcodeCollapsible;
});

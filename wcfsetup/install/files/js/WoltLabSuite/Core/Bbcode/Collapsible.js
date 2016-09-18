/**
 * Generic handler for collapsible bbcode boxes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Bbcode/Collapsible
 */
define([], function() {
	"use strict";
	
	var _containers = elByClass('jsCollapsibleBbcode');
	
	/**
	 * @exports	WoltLabSuite/Core/Bbcode/Collapsible
	 */
	return {
		observe: function() {
			var container, toggleButton;
			while (_containers.length) {
				container = _containers[0];
				container.classList.remove('jsCollapsibleBbcode');
				
				toggleButton = elBySel('.toggleButton', container);
				if (toggleButton === null) {
					continue;
				}
				
				(function(container, toggleButton) {
					var toggle = function(event) {
						if (container.classList.toggle('collapsed')) {
							toggleButton.textContent = elData(toggleButton, 'title-expand');
							
							if (event instanceof Event) {
								// negative top value means the upper boundary is not within the viewport
								var top = container.getBoundingClientRect().top;
								if (top < 0) {
									var y = window.pageYOffset + (top - 100);
									if (y < 0) y = 0;
									window.scrollTo(window.pageXOffset, y);
								}
							}
						}
						else {
							toggleButton.textContent = elData(toggleButton, 'title-collapse');
						}
					};
					
					toggleButton.addEventListener(WCF_CLICK_EVENT, toggle);
					
					// expand boxes that are initially scrolled
					if (container.scrollTop !== 0) {
						toggle();
					}
				})(container, toggleButton);
			}
		}
	};
});

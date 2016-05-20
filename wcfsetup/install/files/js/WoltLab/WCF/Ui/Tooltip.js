/**
 * Provides enhanced tooltips.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Tooltip
 */
define(['Environment', 'Dom/ChangeListener', 'Ui/Alignment'], function(Environment, DomChangeListener, UiAlignment) {
	"use strict";
	
	var _elements = null;
	var _pointer = null;
	var _text = null;
	var _tooltip = null;
	
	/**
	 * @exports	WoltLab/WCF/Ui/Tooltip
	 */
	return {
		/**
		 * Initializes the tooltip element and binds event listener.
		 */
		setup: function() {
			if (Environment.platform() !== 'desktop') return;
			
			_tooltip = elCreate('div');
			elAttr(_tooltip, 'id', 'balloonTooltip');
			_tooltip.classList.add('balloonTooltip');
			
			_text = elCreate('span');
			elAttr(_text, 'id', 'balloonTooltipText');
			_tooltip.appendChild(_text);
			
			_pointer = elCreate('span');
			_pointer.classList.add('elementPointer');
			_pointer.appendChild(elCreate('span'));
			_tooltip.appendChild(_pointer);
			
			document.body.appendChild(_tooltip);
			
			_elements = elByClass('jsTooltip');
			
			this.init();
			
			DomChangeListener.add('WoltLab/WCF/Ui/Tooltip', this.init.bind(this));
			window.addEventListener('scroll', this._mouseLeave.bind(this));
		},
		
		/**
		 * Initializes tooltip elements.
		 */
		init: function() {
			var element, title;
			while (_elements.length) {
				element = _elements[0];
				element.classList.remove('jsTooltip');
				
				title = elAttr(element, 'title').trim();
				if (title.length) {
					elData(element, 'tooltip', title);
					element.removeAttribute('title');
					
					element.addEventListener('mouseenter', this._mouseEnter.bind(this));
					element.addEventListener('mouseleave', this._mouseLeave.bind(this));
					element.addEventListener(WCF_CLICK_EVENT, this._mouseLeave.bind(this));
				}
			}
		},
		
		/**
		 * Displays the tooltip on mouse enter.
		 * 
		 * @param	{Event}         event	event object
		 */
		_mouseEnter: function(event) {
			var element = event.currentTarget;
			var title = elAttr(element, 'title');
			title = (typeof title === 'string') ? title.trim() : '';
			
			if (title !== '') {
				elData(element, 'tooltip', title);
				element.removeAttribute('title');
			}
			
			title = elData(element, 'tooltip');
			
			// reset tooltip position
			_tooltip.style.removeProperty('top');
			_tooltip.style.removeProperty('left');
			
			// ignore empty tooltip
			if (!title.length) {
				_tooltip.classList.remove('active');
				return;
			}
			else {
				_tooltip.classList.add('active');
			}
			
			_text.textContent = title;
			
			UiAlignment.set(_tooltip, element, {
				horizontal: 'center',
				verticalOffset: 4,
				pointer: true,
				pointerClassNames: ['inverse'],
				vertical: 'top'
			});
		},
		
		/**
		 * Hides the tooltip once the mouse leaves the element.
		 */
		_mouseLeave: function() {
			_tooltip.classList.remove('active');
		}
	};
});

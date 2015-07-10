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
	var UiTooltip = {
		/**
		 * Initializes the tooltip element and binds event listener.
		 */
		setup: function() {
			if (Environment.platform() !== 'desktop') return;
			
			_tooltip = document.createElement('div');
			_tooltip.setAttribute('id', 'balloonTooltip');
			_tooltip.classList.add('balloonTooltip');
			
			_text = document.createElement('span');
			_text.setAttribute('id', 'balloonTooltipText');
			_tooltip.appendChild(_text);
			
			_pointer = document.createElement('span');
			_pointer.classList.add('elementPointer');
			_pointer.appendChild(document.createElement('span'));
			_tooltip.appendChild(_pointer);
			
			document.body.appendChild(_tooltip);
			
			_elements = document.getElementsByClassName('jsTooltip');
			
			this.init();
			
			DomChangeListener.add('WoltLab/WCF/Ui/Tooltip', this.init.bind(this));
		},
		
		/**
		 * Initializes tooltip elements.
		 */
		init: function() {
			while (_elements.length) {
				var element = _elements[0];
				element.classList.remove('jsTooltip');
				
				var title = element.getAttribute('title');
				title = (typeof title === 'string') ? title.trim() : '';
				
				if (title.length) {
					element.setAttribute('data-tooltip', title);
					element.removeAttribute('title');
					
					element.addEventListener('mouseenter', this._mouseEnter.bind(this));
					element.addEventListener('mouseleave', this._mouseLeave.bind(this));
					element.addEventListener('click', this._mouseLeave.bind(this));
				}
			}
		},
		
		/**
		 * Displays the tooltip on mouse enter.
		 * 
		 * @param	{object}	event	event object
		 */
		_mouseEnter: function(event) {
			var element = event.currentTarget;
			var title = element.getAttribute('title');
			title = (typeof title === 'string') ? title.trim() : '';
			
			if (title !== '') {
				element.setAttribute('data-tooltip', title);
				element.removeAttribute('title');
			}
			
			title = element.getAttribute('data-tooltip');
			
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
				pointer: true,
				pointerClassNames: ['inverse'],
				vertical: 'top'
			});
		},
		
		/**
		 * Hides the tooltip once the mouse leaves the element.
		 * 
		 * @param	{object}	event	event object
		 */
		_mouseLeave: function(event) {
			_tooltip.classList.remove('active');
		}
	};
	
	return UiTooltip;
});

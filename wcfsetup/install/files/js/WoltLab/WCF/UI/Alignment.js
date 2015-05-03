"use strict";

/**
 * Utility class to align elements relatively to another.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/Alignment
 */
define(['Core', 'DOM/Util'], function(Core, DOMUtil) {
	/**
	 * @constructor
	 */
	function UIAlignment() {};
	UIAlignment.prototype = {
		/**
		 * Sets the alignment for target element relatively to the reference element.
		 * 
		 * @param	{Element}		el		target element
		 * @param	{Element}		ref		reference element
		 * @param	{object<string, *>}	options		list of options to alter the behavior
		 */
		set: function(el, ref, options) {
			options = Core.extend({
				// offset to reference element
				verticalOffset: 7,
				
				// align the pointer element, expects .pointer as a direct child of given element
				pointer: false,
				
				// use static pointer positions, expects two items: class to move it to the bottom and the second to move it to the right
				pointerClassNames: [],
				
				// alternate element used to calculate dimensions
				refDimensionsElement: null,
				
				// preferred alignment, possible values: left/right and top/bottom
				horizontal: 'left',
				vertical: 'bottom',
				
				// allow flipping over axis, possible values: both, horizontal, vertical and none
				allowFlip: 'both'
			}, options);
			
			if (!Array.isArray(options.pointerClassNames) || options.pointerClassNames.length !== 2) options.pointerClassNames = [];
			if (options.horizontal !== 'right') options.horizontal = 'left';
			if (options.vertical !== 'bottom') options.horizontal = 'top';
			if (['both', 'horizontal', 'vertical', 'none'].indexOf(options.allowFlip) === -1) options.allowFlip = 'both';
			
			// place element in the upper left corner to prevent calculation issues due to possible scrollbars
			DOMUtil.setStyles(el, {
				bottom: 'auto',
				left: '0px',
				right: 'auto',
				top: '0px'
			});
			
			var elDimensions = DOMUtil.outerDimensions(el);
			var refDimensions = DOMUtil.outerDimensions((options.refDimensionsElement instanceof Element ? options.refDimensionsElement : ref));
			var refOffsets = DOMUtil.offset(ref);
			var windowHeight = window.innerHeight;
			var windowWidth = window.innerWidth;
			
			// in rtl languages we simply swap the value for 'horizontal'
			if (WCF.Language.get('wcf.global.pageDirection') === 'rtl') {
				options.horizontal = (options.horizontal === 'left') ? 'right' : 'left';
			}
			
			var horizontal = this._tryAlignmentHorizontal(options.horizontal, elDimensions, refDimensions, refOffsets, windowWidth);
			if (!horizontal.result && (options.allowFlip === 'both' || options.allowFlip === 'horizontal')) {
				var horizontalFlipped = this._tryAlignmentHorizontal((options.horizontal === 'left' ? 'right' : 'left'), elDimensions, refDimensions, refOffsets, windowWidth);
				// only use these results if it fits into the boundaries, otherwise both directions exceed and we honor the demanded direction
				if (horizontalFlipped.result) {
					horizontal = horizontalFlipped;
				}
			}
			
			var left = horizontal.left;
			var right = horizontal.right;
			
			var vertical = this._tryAlignmentVertical(options.vertical, elDimensions, refDimensions, refOffsets, windowHeight, options.verticalOffset);
			if (!vertical.result && (options.allowFlip === 'both' || options.allowFlip === 'vertical')) {
				var verticalFlipped = this._tryAlignmentVertical((options.vertical === 'top' ? 'bottom' : 'top'), elDimensions, refDimensions, refOffsets, windowHeight, options.verticalOffset);
				// only use these results if it fits into the boundaries, otherwise both directions exceed and we honor the demanded direction
				if (verticalFlipped.result) {
					vertical = verticalFlipped;
				}
			}
			
			var bottom = vertical.bottom;
			var top = vertical.top;
			
			// set pointer position
			if (options.pointer) {
				//var pointer = null;
				// TODO: implement pointer support, e.g. for interactive dropdowns
				console.debug("TODO");
			}
			else if (options.pointerClassNames.length === 2) {
				var pointerRight = 0;
				var pointerBottom = 1;
				
				el.classList[(top === 'auto' ? 'add' : 'remove')](options.pointerClassNames[pointerBottom]);
				el.classList[(left === 'auto' ? 'add' : 'remove')](options.pointerClassNames[pointerRight]);
			}
			
			DOMUtil.setStyles(el, {
				bottom: bottom + (bottom !== 'auto' ? 'px' : ''),
				left: left + (left !== 'auto' ? 'px' : ''),
				right: right + (right !== 'auto' ? 'px' : ''),
				top: top + (top !== 'auto' ? 'px' : '')
			});
		},
		
		/**
		 * Calculates left/right position and verifys if the element would be still within the page's boundaries.
		 * 
		 * @param	{string}			align		align to this side of the reference element
		 * @param	{object<string, integer>}	elDimensions	element dimensions
		 * @param	{object<string, integer>}	refDimensions	reference element dimensions
		 * @param	{object<string, integer>}	refOffsets	position of reference element relative to the document
		 * @param	{integer}			windowWidth	window width
		 * @returns	{object<string, *>}	calculation results
		 */
		_tryAlignmentHorizontal: function(align, elDimensions, refDimensions, refOffsets, windowWidth) {
			var left = 'auto';
			var right = 'auto';
			var result = true;
			
			if (align === 'left') {
				left = refOffsets.left;
				if (left + elDimensions.width > windowWidth) {
					result = false;
				}
			}
			else {
				right = refOffsets.left + refDimensions.width;
				if (right - elDimensions.width < 0) {
					result = false;
				}
			}
			
			return {
				left: left,
				right: right,
				result: result
			};
		},
		
		/**
		 * Calculates top/bottom position and verifys if the element would be still within the page's boundaries.
		 * 
		 * @param	{string}			align		align to this side of the reference element
		 * @param	{object<string, integer>}	elDimensions	element dimensions
		 * @param	{object<string, integer>}	refDimensions	reference element dimensions
		 * @param	{object<string, integer>}	refOffsets	position of reference element relative to the document
		 * @param	{integer}			windowHeight	window height
		 * @param	{integer}			verticalOffset	desired gap between element and reference element
		 * @returns	{object<string, *>}	calculation results
		 */
		_tryAlignmentVertical: function(align, elDimensions, refDimensions, refOffsets, windowHeight, verticalOffset) {
			var bottom = 'auto';
			var top = 'auto';
			var result = true;
			
			if (align === 'top') {
				bottom = refOffsets.top + verticalOffset;
				if (bottom - elDimensions.height < 0) {
					result = false;
				}
			}
			else {
				top = refOffsets.top + refDimensions.height + verticalOffset;
				if (top + elDimensions.height > windowHeight) {
					result = false;
				}
			}
			
			return {
				bottom: bottom,
				top: top,
				result: result
			};
		}
	};
	
	return new UIAlignment();
});

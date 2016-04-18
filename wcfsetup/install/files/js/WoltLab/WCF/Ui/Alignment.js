/**
 * Utility class to align elements relatively to another.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Alignment
 */
define(['Core', 'Language', 'Dom/Traverse', 'Dom/Util'], function(Core, Language, DomTraverse, DomUtil) {
	"use strict";
	
	/**
	 * @exports	WoltLab/WCF/Ui/Alignment
	 */
	return {
		/**
		 * Sets the alignment for target element relatively to the reference element.
		 * 
		 * @param	{Element}		el		target element
		 * @param	{Element}		ref		reference element
		 * @param	{Object<string, *>}	options		list of options to alter the behavior
		 */
		set: function(el, ref, options) {
			options = Core.extend({
				// offset to reference element
				verticalOffset: 0,
				
				// align the pointer element, expects .elementPointer as a direct child of given element
				pointer: false,
				
				// offset from/left side, ignored for center alignment
				pointerOffset: 4,
				
				// use static pointer positions, expects two items: class to move it to the bottom and the second to move it to the right
				pointerClassNames: [],
				
				// alternate element used to calculate dimensions
				refDimensionsElement: null,
				
				// preferred alignment, possible values: left/right/center and top/bottom
				horizontal: 'left',
				vertical: 'bottom',
				
				// allow flipping over axis, possible values: both, horizontal, vertical and none
				allowFlip: 'both'
			}, options);
			
			if (!Array.isArray(options.pointerClassNames) || options.pointerClassNames.length !== (options.pointer ? 1 : 2)) options.pointerClassNames = [];
			if (['left', 'right', 'center'].indexOf(options.horizontal) === -1) options.horizontal = 'left';
			if (options.vertical !== 'bottom') options.vertical = 'top';
			if (['both', 'horizontal', 'vertical', 'none'].indexOf(options.allowFlip) === -1) options.allowFlip = 'both';
			
			// place element in the upper left corner to prevent calculation issues due to possible scrollbars
			DomUtil.setStyles(el, {
				bottom: 'auto !important',
				left: '0 !important',
				right: 'auto !important',
				top: '0 !important',
				visibility: 'hidden !important'
			});
			
			var elDimensions = DomUtil.outerDimensions(el);
			var refDimensions = DomUtil.outerDimensions((options.refDimensionsElement instanceof Element ? options.refDimensionsElement : ref));
			var refOffsets = DomUtil.offset(ref);
			var windowHeight = window.innerHeight;
			var windowWidth = document.body.clientWidth;
			
			var horizontal = { result: null };
			var alignCenter = false;
			if (options.horizontal === 'center') {
				alignCenter = true;
				horizontal = this._tryAlignmentHorizontal(options.horizontal, elDimensions, refDimensions, refOffsets, windowWidth);
				
				if (!horizontal.result) {
					if (options.allowFlip === 'both' || options.allowFlip === 'horizontal') {
						options.horizontal = 'left';
					}
					else {
						horizontal.result = true;
					}
				}
			}
			
			// in rtl languages we simply swap the value for 'horizontal'
			if (Language.get('wcf.global.pageDirection') === 'rtl') {
				options.horizontal = (options.horizontal === 'left') ? 'right' : 'left';
			}
			
			if (!horizontal.result) {
				var horizontalCenter = horizontal;
				horizontal = this._tryAlignmentHorizontal(options.horizontal, elDimensions, refDimensions, refOffsets, windowWidth);
				if (!horizontal.result && (options.allowFlip === 'both' || options.allowFlip === 'horizontal')) {
					var horizontalFlipped = this._tryAlignmentHorizontal((options.horizontal === 'left' ? 'right' : 'left'), elDimensions, refDimensions, refOffsets, windowWidth);
					// only use these results if it fits into the boundaries, otherwise both directions exceed and we honor the demanded direction
					if (horizontalFlipped.result) {
						horizontal = horizontalFlipped;
					}
					else if (alignCenter) {
						horizontal = horizontalCenter;
					}
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
				var pointer = DomTraverse.childrenByClass(el, 'elementPointer');
				pointer = pointer[0] || null;
				if (pointer === null) {
					throw new Error("Expected the .elementPointer element to be a direct children.");
				}
				
				if (horizontal.align === 'center') {
					pointer.classList.add('center');
					
					pointer.classList.remove('left');
					pointer.classList.remove('right');
				}
				else {
					pointer.classList.add(horizontal.align);
					
					pointer.classList.remove('center');
					pointer.classList.remove(horizontal.align === 'left' ? 'right' : 'left');
				}
				
				if (vertical.align === 'top') {
					pointer.classList.add('flipVertical');
				}
				else {
					pointer.classList.remove('flipVertical');
				}
			}
			else if (options.pointerClassNames.length === 2) {
				var pointerBottom = 0;
				var pointerRight = 1;
				
				el.classList[(top === 'auto' ? 'add' : 'remove')](options.pointerClassNames[pointerBottom]);
				el.classList[(left === 'auto' ? 'add' : 'remove')](options.pointerClassNames[pointerRight]);
			}
			
			if (bottom !== 'auto') bottom = Math.round(bottom) + 'px';
			if (left !== 'auto') left = Math.ceil(left) + 'px';
			if (right !== 'auto') right = Math.floor(right) + 'px';
			if (top !== 'auto') top = Math.round(top) + 'px';
			
			DomUtil.setStyles(el, {
				bottom: bottom,
				left: left,
				right: right,
				top: top
			});
			
			elShow(el);
			el.style.removeProperty('visibility');
		},
		
		/**
		 * Calculates left/right position and verifies if the element would be still within the page's boundaries.
		 * 
		 * @param	{string}		align		align to this side of the reference element
		 * @param	{Object<string, int>}	elDimensions	element dimensions
		 * @param	{Object<string, int>}	refDimensions	reference element dimensions
		 * @param	{Object<string, int>}	refOffsets	position of reference element relative to the document
		 * @param	{int}			windowWidth	window width
		 * @returns	{Object<string, *>}	calculation results
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
			else if (align === 'right') {
				right = windowWidth - (refOffsets.left + refDimensions.width);
				if (right < 0) {
					result = false;
				}
			}
			else {
				left = refOffsets.left + (refDimensions.width / 2) - (elDimensions.width / 2);
				left = ~~left;
				
				if (left < 0 || left + elDimensions.width > windowWidth) {
					result = false;
				}
			}
			
			return {
				align: align,
				left: left,
				right: right,
				result: result
			};
		},
		
		/**
		 * Calculates top/bottom position and verifys if the element would be still within the page's boundaries.
		 * 
		 * @param	{string}		align		align to this side of the reference element
		 * @param	{Object<string, int>}	elDimensions	element dimensions
		 * @param	{Object<string, int>}	refDimensions	reference element dimensions
		 * @param	{Object<string, int>}	refOffsets	position of reference element relative to the document
		 * @param	{int}			windowHeight	window height
		 * @param	{int}			verticalOffset	desired gap between element and reference element
		 * @returns	{object<string, *>}	calculation results
		 */
		_tryAlignmentVertical: function(align, elDimensions, refDimensions, refOffsets, windowHeight, verticalOffset) {
			var bottom = 'auto';
			var top = 'auto';
			var result = true;
			
			if (align === 'top') {
				var bodyHeight = document.body.clientHeight;
				bottom = (bodyHeight - refOffsets.top) + verticalOffset;
				if (bodyHeight - (bottom + elDimensions.height) < window.scrollY) {
					result = false;
				}
			}
			else {
				top = refOffsets.top + refDimensions.height + verticalOffset;
				if (top + elDimensions.height - window.scrollY > windowHeight) {
					result = false;
				}
			}
			
			return {
				align: align,
				bottom: bottom,
				top: top,
				result: result
			};
		}
	};
});

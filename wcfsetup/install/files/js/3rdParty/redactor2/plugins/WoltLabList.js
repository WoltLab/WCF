$.Redactor.prototype.WoltLabList = function() {
	"use strict";
	
	return {
		parentsQualifiedForLists: ['woltlab-quote', 'woltlab-spoiler'],
		
		init: function () {
			this.list.combineAfterAndBefore = (function(block) {
				var $prev = $(block).prev();
				var $next = $(block).next();
				var isEmptyBlock = (block && block.tagName === 'P' && (block.innerHTML === '<br>' || block.innerHTML === ''));
				var isBlockWrapped = ($prev.closest('ol, ul', this.core.editor()[0]).length === 1 && $next.closest('ol, ul', this.core.editor()[0]).length === 1);
				
				var isEffectivelyEmptyBlock = false;
				if (isBlockWrapped && !isEmptyBlock) {
					// check if the current block _is_ actually empty, but
					// Redactor does not recognize it due to format elements
					if (block.textContent.replace(/\u200b/g, '').trim().length === 0) {
						// check that only inline format elements are present
						var inlineElements = ['A', 'B', 'BR', 'EM', 'I', 'STRONG', 'U'];
						var isEmpty = true;
						elBySelAll('*', block, function(element) {
							if (inlineElements.indexOf(element.nodeName) !== -1) {
								return;
							}
							
							// only allow spans if they have no CSS classes set
							if (element.nodeName === 'SPAN' && element.className.trim() === '') {
								return;
							}
							
							isEmpty = false;
						});
						
						if (isEmpty) {
							isEffectivelyEmptyBlock = true;
							isEmptyBlock = true;
						}
					}
				}
				
				if (isEmptyBlock && isBlockWrapped) {
					// remove "empty" item instead
					if (block.nodeName === 'LI' && isEffectivelyEmptyBlock) {
						$prev.append(this.marker.get());
						elRemove(block);
						
						this.selection.restore();
						
						return true;
					}
					
					$prev.children('li').last().append(this.marker.get());
					$prev.append($next.contents());
					
					// WoltLab modification
					var list = block.nextElementSibling;
					if ((list.nodeName === 'OL' || list.nodeName === 'UL') && list.childElementCount === 0) {
						elRemove(list);
					}
					
					if (isEffectivelyEmptyBlock) {
						elRemove(block);
					}
					
					this.selection.restore();
					
					return true;
				}
				
				return false;
			}).bind(this);
			
			this.list.toggle = (function(type) {
				if (this.utils.inBlocks(['table', 'td', 'th', 'tr'])) {
					return;
				}
				
				type = (type === 'orderedlist') ? 'ol' : type;
				type = (type === 'unorderedlist') ? 'ul' : type;
				
				type = type.toLowerCase();
				
				this.buffer.set();
				this.selection.save();
				
				var nodes = this.list._getBlocks();
				var block = this.selection.block();
				
				// WoltLab modification: the selector matches lists outside the editor
				//var $list = $(block).parents('ul, ol').last();
				var $list = $(block).parent().closest('ol, ul', this.core.editor()[0]);
				// WoltLab modification END
				if (nodes.length === 0 && $list.length !== 0) {
					nodes = [$list.get(0)];
				}
				
				nodes = (this.list._isUnformat(type, nodes)) ? this.list._unformat(type, nodes) : this.list._format(type, nodes);
				
				this.selection.restore();
				
				return nodes;
			}).bind(this);
			
			this.list._getBlocks = (function() {
				return this.selection.blocks().filter((function(block) {
					var parent = block.parentNode;
					if (parent.classList.contains('redactor-in')) {
						return true;
					}
					else if (this.WoltLabList.parentsQualifiedForLists.indexOf(parent.nodeName.toLowerCase()) !== -1) {
						return true;
					}
					
					return false;
				}).bind(this));
			}).bind(this);
		}
	};
};

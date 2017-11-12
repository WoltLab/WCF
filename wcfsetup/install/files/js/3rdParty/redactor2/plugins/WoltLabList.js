$.Redactor.prototype.WoltLabList = function() {
	"use strict";
	
	return {
		init: function () {
			this.list.toggle = (function(cmd) {
				if (this.utils.inBlocks(['table', 'td', 'th', 'tr'])) {
					return;
				}
				
				var tag = (cmd === 'orderedlist' || cmd === 'ol') ? 'OL' : 'UL';
				cmd = (tag === 'OL') ? 'orderedlist' : 'unorderedlist';
				
				var $list = $(this.selection.current()).parentsUntil('.redactor-in', 'ul, ol').first();
				
				this.placeholder.hide();
				this.buffer.set();
				
				if ($list.length !== 0 && $list[0].tagName === tag && this.utils.isRedactorParent($list)) {
					this.selection.save();
					
					// remove list
					$list.find('ul, ol').each(function () {
						var parent = $(this).closest('li');
						$(this).find('li').each(function () {
							$(parent).after(this);
						});
					});
					
					$list.find('ul, ol').remove();
					$list.find('li').each(function () {
						return $(this).replaceWith(function () {
							return $('<p />').append($(this).contents());
						});
					});
					
					$list.replaceWith(function () {
						return $(this).contents();
					});
					
					this.selection.restore();
					return;
				}
				
				
				this.selection.save();
				
				if ($list.length !== 0 && $list[0].tagName !== tag) {
					$list.each($.proxy(function (i, s) {
						this.utils.replaceToTag(s, tag);
						
					}, this));
				}
				else {
					document.execCommand('insert' + cmd);
				}
				
				this.selection.restore();
				
				var $insertedList = this.list.get();
				if (!$insertedList) {
					if (!this.selection.block()) {
						document.execCommand('formatblock', false, 'p');
					}
					
					return;
				}
				
				// clear span
				// WoltLab modification: do not remove <span>
				/*$insertedList.find('span').replaceWith(function () {
					return $(this).contents();
				});*/
				
				// remove style
				$insertedList.find(this.opts.inlineTags.join(',')).each(function () {
					// WoltLab modification: exclude spans
					if (this.nodeName !== 'SPAN') $(this).removeAttr('style');
				});
				
				// remove block-element list wrapper
				var $listParent = $insertedList.parent();
				if (this.utils.isRedactorParent($listParent) && $listParent[0].tagName !== 'LI' && this.utils.isBlock($listParent)) {
					this.selection.save();
					
					$listParent.replaceWith($listParent.contents());
					
					this.selection.restore();
				}
			}).bind(this);
			
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
		}
	};
};

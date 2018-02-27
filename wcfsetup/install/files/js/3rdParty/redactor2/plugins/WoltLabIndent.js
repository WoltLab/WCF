$.Redactor.prototype.WoltLabIndent = function() {
	"use strict";
	
	return {
		init: function () {
			// Firefox's `execCommand('outdent')` is broken and yields invalid HTML
			if (this.detect.isFirefox()) {
				var marker1, marker2;
				
				var mpDecrease = this.indent.decrease;
				this.indent.decrease = (function () {
					if (!this.list.get()) {
						return;
					}
					
					var $current = $(this.selection.current()).closest('li', this.core.editor()[0]);
					var $list = $current.closest('ul, ol', this.core.editor()[0]);
					
					// check if there is a parent list
					if ($list.parent().closest('ul, ol', this.core.editor()[0]).length === 0) {
						// `execCommand('outdent')` fails if the caret is inside the list's root and there
						// is at least one more list inside (nesting). Firefox simply does nothing in this
						// case, see https://bugzilla.mozilla.org/show_bug.cgi?id=485466
						var current = $current[0];
						if (elBySel('ul, ol', current) !== null) {
							this.buffer.set();
							this.selection.save();
							
							// first we need to check if we have any siblings that need to be moved into separate lists
							var sibling = current.previousElementSibling;
							
							// move the current item and all following siblings into a new list
							var newList, parent;
							if (sibling !== null) {
								newList = elCreate(current.parentNode.nodeName.toLowerCase());
								while (sibling.nextSibling) {
									newList.appendChild(sibling.nextSibling);
								}
								
								parent = sibling.parentNode;
								parent.parentNode.insertBefore(newList, parent.nextSibling);
							}
							
							// move the following sibling into a new list
							if (current.nextElementSibling !== null) {
								newList = elCreate(current.parentNode.nodeName.toLowerCase());
								while (current.nextSibling) {
									newList.appendChild(current.nextSibling);
								}
								
								parent = current.parentNode;
								parent.parentNode.insertBefore(newList, parent.nextSibling);
							}
							
							// unwrap the current list
							parent = current.parentNode;
							while (current.childNodes.length) {
								parent.parentNode.insertBefore(current.childNodes[0], parent);
							}
							elRemove(parent);
							
							this.selection.restore();
							return;
						}
					}
					else {
						elBySelAll('woltlab-list-marker', this.core.editor()[0], elRemove);
						
						this.selection.save();
						
						marker1 = elCreate('woltlab-list-marker');
						$current[0].insertBefore(marker1, $current[0].firstChild);
						
						// Firefox fails to outdent the item when it contains a trailing `<br>`
						var lastElement = $current[0].lastElementChild;
						if (lastElement.nodeName === 'BR') {
							// verify that there is no text after the br
							var text = '';
							var sibling = lastElement;
							while (sibling = sibling.nextSibling) {
								text += sibling.textContent;
							}
							
							if (text.replace(/\u200B/g, '').trim() === '') {
								elRemove(lastElement);
							}
						}
						
						marker2 = elCreate('woltlab-list-marker');
						$current[0].appendChild(marker2);
						
						this.selection.restore();
					}
					
					mpDecrease.call(this);
				}).bind(this);
				
				var mpRemoveEmpty = this.indent.removeEmpty;
				this.indent.removeEmpty = (function () {
					if (marker1 && marker1.parentNode) {
						var sibling, li = elCreate('li');
						while (sibling = marker1.nextSibling) {
							if (sibling === marker2) break;
							
							li.appendChild(sibling);
						}
						
						marker1.parentNode.insertBefore(li, marker1);
						
						elBySelAll('woltlab-list-marker', this.core.editor()[0], elRemove);
						
						marker1 = undefined;
						marker2 = undefined;
					}
					
					mpRemoveEmpty.call(this);
				}).bind(this);
			}
			
			this.indent.repositionItem = (function($item) {
				var $next = $item.next();
				if ($next.length !== 0 && ($next[0].tagName !== 'UL' || $next[0].tagName !== 'OL')) {
					$item.append($next);
				}
				
				var $prev = $item.prev();
				if ($prev.length !== 0 && $prev[0].tagName !== 'LI') {
					this.selection.save();
					// WoltLab modification
					//var $li = $item.parents('li', this.core.editor()[0]);
					var $li = $item.closest('li', this.core.editor()[0]);
					// WoltLab modification END
					$li.after($item);
					this.selection.restore();
				}
			}).bind(this);
			
			this.indent.normalize = (function() {
				// `document.execCommand('outdent')` can spawn a `<br>` if there is whitespace in the DOM
				// see https://bugzilla.mozilla.org/show_bug.cgi?id=1428073
				if (this.detect.isFirefox()) {
					var block = this.selection.block();
					if (block && block.nodeName === 'P') {
						var br = block.previousElementSibling;
						if (br && br.nodeName === 'BR') elRemove(br);
					}
				}
				
				this.core.editor().find('li').each($.proxy(function (i, s) {
					var $el = $(s);
					
					// remove style
					var filter = '';
					if (this.opts.keepStyleAttr.length !== 0) {
						filter = ',' + this.opts.keepStyleAttr.join(',');
					}
					
					// WoltLab modification: exclude <span> from style purification
					$el.find(this.opts.inlineTags.join(',')).not('img' + filter).not('span').removeAttr('style');
					
					var $parent = $el.parent();
					if ($parent.length !== 0 && $parent[0].tagName === 'LI') {
						// WoltLab modification: move not only the current li, but also all following siblings
						while (s.nextSibling) {
							s.appendChild(s.nextSibling);
						}
						
						$parent.after($el);
						return;
					}
					
					var $next = $el.next();
					if ($next.length !== 0 && ($next[0].tagName === 'UL' || $next[0].tagName === 'OL')) {
						$el.append($next);
					}
					
				}, this));
			}).bind(this);
		}
	};
};

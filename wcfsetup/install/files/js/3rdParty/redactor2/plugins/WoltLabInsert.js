$.Redactor.prototype.WoltLabInsert = function() {
	"use strict";
	
	return {
		init: function () {
			var callback = this.opts.woltlab.placeholderCallback;
			
			var mpHtml = this.insert.html;
			this.insert.html = (function (html, data) {
				if (callback) callback = callback();
				
				var selection = window.getSelection();
				if (selection.rangeCount && selection.anchorNode.nodeName === 'IMG') {
					this.caret.after(selection.anchorNode);
				}
				
				this.placeholder.hide();
				this.core.editor().focus();
				
				// Firefox may have an incorrect selection if pasting into the editor using the contextual menu
				if (this.detect.isFirefox()) {
					var anchorNode = (selection.anchorNode.nodeType === Node.TEXT_NODE) ? selection.anchorNode.parentNode : selection.anchorNode;
					if (anchorNode.closest('.redactor-layer') === null) {
						this.selection.restore();
						
						anchorNode = (selection.anchorNode.nodeType === Node.TEXT_NODE) ? selection.anchorNode.parentNode : selection.anchorNode;
						if (anchorNode.closest('.redactor-layer') === null) {
							this.WoltLabCaret.endOfEditor();
							this.selection.save();
						}
					}
				}
				
				/** @var Element */
				var block = this.selection.block();
				
				var isEmptyEditor = (this.$editor[0].innerHTML.replace(/<\/?p>/g, '').replace(/<br>/g, '').replace(/\u200B/g, '').trim() === '');
				
				mpHtml.call(this, html, data);
				
				if (isEmptyEditor) {
					block = this.$editor[0].firstElementChild;
				}
				
				if (block && block.nodeName === 'P' && block.nextElementSibling) {
					var removeBlock = false;
					if (block.childElementCount === 0 && block.textContent.replace(/\u200B/g, '').trim() === '') {
						removeBlock = true;
					}
					else if (block.childElementCount === 1 && block.innerHTML === '<br>') {
						removeBlock = true;
					}
					
					if (removeBlock) {
						// inserting HTML tends to cause new paragraphs inserted
						// rather than using the current, empty one
						elRemove(block);
					}
				}
				
				if (selection.rangeCount && selection.anchorNode.nodeName === 'IMG') {
					this.caret.after(selection.anchorNode);
				}
			}).bind(this);
			
			var mpText = this.insert.text;
			this.insert.text = (function (text) {
				if (callback) callback = callback();
				
				this.core.editor().focus();
				this.selection.restore();
				if (elClosest(window.getSelection().anchorNode, '.redactor-layer') !== this.core.editor()[0]) {
					this.WoltLabCaret.endOfEditor();
				}
				
				mpText.call(this, text);
				
				this.selection.saveInstant();
			}).bind(this);
			
			this.insert.placeHtml = (function(html) {
				var hasBbcodeMarker = false;
				html.forEach(function(fragment) {
					if (fragment instanceof Element && fragment.classList.contains('woltlab-bbcode-marker')) {
						hasBbcodeMarker = true;
					}
				});
				
				var marker = document.createElement('span');
				marker.id = 'redactor-insert-marker';
				marker = this.insert.node(marker);
				
				$(marker).before(html);
				if (!hasBbcodeMarker) {
					this.selection.restore();
					this.caret.after(marker);
				}
				$(marker).remove();
			}).bind(this);
		}
	};
};

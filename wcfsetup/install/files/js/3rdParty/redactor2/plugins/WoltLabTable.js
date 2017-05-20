$.Redactor.prototype.WoltLabTable = function() {
	"use strict";
	
	return {
		init: function() {
			this.WoltLabEvent.register('insertedTable', (function() {
				window.setTimeout((function () {
					var table = this.selection.block() || this.selection.current();
					if (table.nodeName === 'TBODY') table = table.parentNode;
					if (table.nodeName === 'TABLE') {
						// remove whitespaces directly inside the table element
						var node, textNodes = [];
						for (var i = 0, length = table.childNodes.length; i < length; i++) {
							node = table.childNodes[i];
							if (node.nodeType === Node.TEXT_NODE && node.textContent.length > 0) {
								textNodes.push(node);
							}
						}
						textNodes.forEach(elRemove);
						
						// find first <td> and set the caret inside it
						var td = elBySel('td', table);
						if (td) {
							this.caret.end(td);
						}
					}
				}).bind(this), 10);
			}).bind(this));
		}
	}
};

$.Redactor.prototype.WoltLabTable = function() {
	"use strict";
	
	return {
		init: function() {
			this.WoltLabEvent.register('insertedTable', (function() {
				window.setTimeout((function () {
					var node = this.selection.block() || this.selection.current();
					if (node.nodeName === 'TBODY') {
						// find first <td> and set the caret inside it
						var td = elBySel('td', node);
						if (td) {
							this.caret.end(td);
						}
					}
				}).bind(this), 10);
			}).bind(this));
		}
	}
};

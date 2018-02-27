$.Redactor.prototype.WoltLabTable = function() {
	"use strict";
	
	var _dialogApi = null;
	
	return {
		init: function() {
			// fix cross-browser caret placement on table insert
			this.WoltLabEvent.register('insertedTable', (function() {
				window.setTimeout((function () {
					var table = this.selection.block() || this.selection.current();
					
					// Safari sends the caret on a journey
					if (table === this.$editor[0]) {
						var selection = window.getSelection();
						if (selection.isCollapsed && selection.anchorNode === this.$editor[0] && selection.anchorOffset > 0) {
							table = selection.anchorNode.childNodes[selection.anchorOffset - 1];
						}
					}
					
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
			
			// use own implementation for table insertion
			var dropdown = this.button.get('table').data('dropdown');
			var button = dropdown.find('.redactor-dropdown-insert_table');
			
			// rebind mouse event
			button.off('mousedown');
			button[0].addEventListener('mousedown', this.WoltLabTable._promptTableSize.bind(this));
			
			require(['WoltLabSuite/Core/Ui/Redactor/Table'], function(UiRedactorTable) {
				_dialogApi = UiRedactorTable;
			});
		},
		
		_promptTableSize: function (event) {
			event.preventDefault();
			
			if (this.table.getTable()) {
				return;
			}
			
			this.selection.save();
			
			_dialogApi.showDialog({
				submitCallback: (function() {
					this.WoltLabTable._insertTable(
						~~elById('redactor-table-rows').value,
						~~elById('redactor-table-cols').value
					);
				}).bind(this)
			});
		},
		
		_insertTable: function (rows, cols) {
			this.placeholder.hide();
			
			// build row template
			var i, rowTemplate = '<tr>';
			for (i = 0; i < cols; i++) {
				rowTemplate += '<td>' + this.opts.invisibleSpace + '</td>';
			}
			rowTemplate += '</tr>';
			
			var table = '<table>';
			for (i = 0; i < rows; i++) {
				if (i === 0) {
					table += rowTemplate.replace(new RegExp('^(<tr><td>' + this.opts.invisibleSpace + ')'), '$1' + this.marker.html());
				}
				else {
					table += rowTemplate;
				}
			}
			table += '</table>';
			
			this.selection.restore();
			this.buffer.set();
			
			var current = this.selection.current();
			if ($(current).closest('li', this.core.editor()[0]).length !== 0) {
				$(current).closest('ul, ol').first().after(table);
			}
			else {
				this.insert.html(table);
			}
			
			this.selection.restore();
			this.core.callback('insertedTable', undefined);
		}
	}
};

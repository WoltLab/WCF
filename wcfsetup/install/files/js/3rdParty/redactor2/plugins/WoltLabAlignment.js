$.Redactor.prototype.WoltLabAlignment  = function() {
	"use strict";
	
	return {
		init: function() {
			var mpRemoveAlign = this.alignment.removeAlign;
			this.alignment.removeAlign = (function() {
				mpRemoveAlign.call(this);
				
				this.block.removeClass('text-justify');
			}).bind(this);
			
			var listItem = this.dropdown.buildItem('justify', {
				title: this.lang.get('align-justify'),
				func: (function () {
					this.buffer.set();
					this.alignment.removeAlign();
					this.block.addClass('text-justify');
				}).bind(this)
			});
			
			listItem.appendTo(this.button.get('alignment').data('dropdown'));
		}
	}
};

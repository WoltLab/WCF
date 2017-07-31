(function($)
{
	$.Redactor.prototype.alignment = function()
	{
		return {
			langs: {
				en: {
					"align": "Align",
					"align-left": "Align Left",
					"align-center": "Align Center",
					"align-right": "Align Right",
					"align-justify": "Align Justify",
				}
			},
			init: function()
			{
				var that = this;
				var dropdown = {};
				
				dropdown.left = { title: that.lang.get('align-left'), func: that.alignment.setLeft };
				dropdown.center = { title: that.lang.get('align-center'), func: that.alignment.setCenter };
				dropdown.right = { title: that.lang.get('align-right'), func: that.alignment.setRight };
				dropdown.justify = { title: that.lang.get('align-justify'), func: that.alignment.setJustify };
				
				var button = this.button.add('alignment', this.lang.get('align'));
				this.button.setIcon(button, '<i class="re-icon-alignment"></i>');
				this.button.addDropdown(button, dropdown);
			},
			removeAlign: function()
			{
				this.block.removeClass('text-center');
				this.block.removeClass('text-right');
				this.block.removeClass('text-justify');
			},
			setLeft: function()
			{
				this.buffer.set();
				this.alignment.removeAlign();
			},
			setCenter: function()
			{
				this.buffer.set();
				this.alignment.removeAlign();
				this.block.addClass('text-center');
				this.core.editor().focus()
			},
			setRight: function()
			{
				this.buffer.set();
				this.alignment.removeAlign();
				this.block.addClass('text-right');
				this.core.editor().focus()
			},
			setJustify: function()
			{
				this.buffer.set();
				this.alignment.removeAlign();
				this.block.addClass('text-justify');
				this.core.editor().focus()
			}
		};
	};
})(jQuery);

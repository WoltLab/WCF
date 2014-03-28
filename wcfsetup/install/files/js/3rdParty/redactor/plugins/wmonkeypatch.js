if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * This plugin makes liberally use of dumb monkey patching to adjust Redactor for our needs. In
 * general this is a collection of methods whose side-effects cannot be prevented in any other
 * way or a work-around would cause a giant pile of boilerplates.
 * 
 * ATTENTION!
 * This plugin partially contains code taken from Redactor, Copyright (c) 2009-2014 Imperavi LLC.
 * Under no circumstances you are allowed to use potions or entire code blocks for use anywhere
 * except when directly working with WoltLab Community Framework.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH, 2009-2014 Imperavi LLC.
 * @license	http://imperavi.com/redactor/license/
 */
RedactorPlugins.wmonkeypatch = {
	/**
	 * Initializes the RedactorPlugins.wmonkeypatch plugin.
	 */
	init: function() {
		var self = this;
		
		var $mpIndentingStart = this.indentingStart;
		this.indentingStart = function(cmd) {
			$mpIndentingStart.call(self, cmd);
			self.mpIndentingStart(cmd);
		};
		
		var $mpBuildEventKeydown = this.buildEventKeydown;
		this.buildEventKeydown = function(e) {
			if (self.callback('wkeydown', e) !== false) {
				$mpBuildEventKeydown.call(self, e);
			}
		};
		
		var $mpToggleCode = this.toggleCode;
		this.toggleCode = function(direct) {
			var $height = self.normalize(self.$editor.css('height'));
			
			$mpToggleCode.call(self, direct);
			
			self.$source.height($height);
		};
	},
	
	/**
	 * Overwrites $.Redactor.inlineRemoveStyle() to drop empty <inline> elements.
	 * 
	 * @see		$.Redactor.inlineRemoveStyle()
	 * @param	string		rule
	 */
	inlineRemoveStyle: function(rule) {
		this.selectionSave();
		
		this.inlineEachNodes(function(node) {
			$(node).css(rule, '');
			this.removeEmptyAttr(node, 'style');
		});
		
		// WoltLab modifications START
		// drop all <inline> elements without an actual attribute
		this.$editor.find('inline').each(function(index, inlineElement) {
			if (!inlineElement.attributes.length) {
				var $inlineElement = $(inlineElement);
				$inlineElement.replaceWith($inlineElement.html());
			}
		});
		// WoltLab modifications END
		
		this.selectionRestore();
		this.sync();
	},
	
	/**
	 * Drops the indentation if not within a list.
	 * 
	 * @param	string		cmd
	 */
	mpIndentingStart: function(cmd) {
		if (cmd === 'indent') {
			var block = this.getBlock();
			if (block.tagName === 'DIV' && block.getAttribute('data-tagblock') !== null) {
				this.selectionSave();
				
				// drop the indention block again. bye bye block
				block = $(block);
				block.replaceWith(block.html());
				
				this.selectionRestore();
				this.sync();
			}
		}
	},
}
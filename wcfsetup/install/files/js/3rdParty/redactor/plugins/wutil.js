if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides utility methods extending $.Redactor
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wutil = {
	/**
	 * Initializes the RedactorPlugins.wutil plugin.
	 */
	init: function() {
		// convert HTML to BBCode upon submit
		this.$source.parents('form').submit($.proxy(this.submit, this));
	},
	
	/**
	 * Allows inserting of text contents in Redactor's source area.
	 * 
	 * @param	string		string
	 * @return	boolean
	 */
	insertAtCaret: function(string) {
		if (this.opts.visual) {
			console.debug("insertAtCaret() failed: Editor is in WYSIWYG-mode.");
			return false;
		}
		
		this.$source.focus();
		var $position = this.$source.getCaret();
		if ($position == -1) {
			console.debug("insertAtCaret() failed: Source is not input[type=text], input[type=password] or textarea.");
		}
		
		var $content = this.$source.val();
		$content = $content.substr(0, $position) + string + $content.substr($position);
		this.$source.val($content);
		
		return true;
	},
	
	/**
	 * Inserts content into the editor depending if it is in wysiwyg or plain mode. If 'plainValue' is
	 * null or undefined, the value from 'html' will be taken instead.
	 * 
	 * @param	string		html
	 * @param	string		plainValue
	 */
	insertDynamic: function(html, plainValue) {
		if (plainValue === undefined || plainValue === null) {
			// shortcut if both 'html' and 'html' are the same
			plainValue = html;
		}
		
		if (this.inWysiwygMode()) {
			this.insertHtml(html);
		}
		else {
			this.insertAtCaret(plainValue);
		}
	},
	
	/**
	 * Sets an option value after initialization.
	 */
	setOption: function(key, value) {
		this.opts[key] = value;
	},
	
	/**
	 * Returns true if editor is in source mode.
	 * 
	 * @return	boolean
	 */
	inPlainMode: function() {
		return !this.opts.visual;
	},
	
	/**
	 * Returns true if editor is in WYSIWYG mode.
	 * 
	 * @return	boolean
	 */
	inWysiwygMode: function() {
		return (this.opts.visual);
	},
	
	/**
	 * Replaces all ranges from the current selection with the provided one.
	 * 
	 * @param	DOMRange	range
	 */
	replaceRangesWith: function(range) {
		getSelection().removeAllRanges();
		getSelection().addRange(range);
	},
	
	/**
	 * Returns text using BBCodes.
	 * 
	 * @return	string
	 */
	getText: function() {
		if (this.inWysiwygMode()) {
			this.sync();
			
			this._convertFromHtml();
		}
		
		return this.$source.val();
	},
	
	/**
	 * Converts HTML to BBCode upon submit.
	 */
	submit: function() {
		if (this.inWysiwygMode()) {
			this.sync();
			
			this._convertFromHtml();
		}
	},
	
	/**
	 * Resets the editor's contents.
	 */
	reset: function() {
		if (this.inWysiwygMode()) {
			this.$editor.empty();
			this.sync();
		}
		else {
			this.$source.val('');
		}
	}
};

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
	}
};

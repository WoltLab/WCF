/**
 * Provides custom buttons for CKEditor.
 * 
 * In short we're applying a style element on the current selection which will be replaced
 * with the plain BBCode tag (e.g. [tt]) afterwards. Using insertText() or insertHtml() does
 * not work here as it discards the inline styles set for the selection.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
(function() {
	/**
	 * Transforms the BBCode span-element into a plain BBCode.
	 * 
	 * @param	CKEDITOR	editor
	 */
	function transformBBCode(editor) {
		var $markerID = null;
		$(editor.container.$).find('span.wcfBBCode').removeClass('wcfBBCode').html(function() {
			var $bbcode = $(this).data('bbcode');
			$markerID = WCF.getRandomID();
			return '[' + $bbcode + ']' + $(this).html() + '<span id="' + $markerID + '" />[/' + $bbcode + ']';
		});
		
		if ($markerID !== null && typeof window.getSelection != "undefined") {
			var $marker = $('#' + $markerID).get(0);
			var $range = document.createRange();
			$range.setStartAfter($marker);
			$range.collapse(true);
			var $selection = window.getSelection();
			$selection.removeAllRanges();
			$selection.addRange($range);
			
			$marker.remove();
		}
	}
	
	// listens for 'afterCommandExec' to transform BBCodes into plain text
	CKEDITOR.on('instanceReady', function(event) {
		event.editor.on('afterCommandExec', function(ev) {
			if (ev.data.name.indexOf('__wcf_') == 0) {
				transformBBCode(ev.editor);
			}
		});
	});
	
	/**
	 * Enables this plugin.
	 */
	CKEDITOR.plugins.add('wbutton', {
		/**
		 * list of required plugins
		 * @var	array<string>
		 */
		requires: [ 'button' ],
		
		/**
		 * Initializes the 'wbutton' plugin.
		 * 
		 * @param	CKEDITOR	editor
		 */
		init: function(editor) {
			if (!__CKEDITOR_BUTTONS.length) {
				return;
			}
			
			for (var $i = 0, $length = __CKEDITOR_BUTTONS.length; $i < $length; $i++) {
				this._wcfAddButton(editor, __CKEDITOR_BUTTONS[$i]);
			}
		},
		
		/**
		 * Adds command and button for given BBCode.
		 * 
		 * @param	CKEDITOR	editor
		 * @param	object		button
		 */
		_wcfAddButton: function(editor, button) {
			var $style = new CKEDITOR.style({
				element: 'span',
				attributes: {
					'class': 'wcfBBCode',
					'data-bbcode': button.name
				}
			});
			editor.addCommand('__wcf_' + button.name, new CKEDITOR.styleCommand($style));
			editor.ui.addButton('__wcf_' + button.name, {
				command: '__wcf_' + button.name,
				icon: button.icon,
				label: button.label
			});
		}
	});
})();

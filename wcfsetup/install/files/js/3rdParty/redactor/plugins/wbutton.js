if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides custom BBCode buttons for Redactor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.wbutton = {
	/**
	 * list of button names and their associated bbcode tag
	 * @var	object<string>
	 */
	_bbcodes: { },
	
	/**
	 * Initializes the RedactorPlugins.wbutton plugin.
	 */
	init: function() {
		this._bbcodes = { };
		
		for (var $i = 0, $length = __REDACTOR_BUTTONS.length; $i < $length; $i++) {
			this._addBBCodeButton(__REDACTOR_BUTTONS[$i]);
		}
		
		// this list contains overrides for built-in buttons, if a button is not present
		// Redactor's own icon will be used instead. This solves the problem of FontAwesome
		// not providing an icon for everything we need (especially the core stuff)
		var $faIcons = {
			'html': 'fa-square-o',
			'bold': 'fa-bold',
			'italic': 'fa-italic',
			'underline': 'fa-underline',
			'deleted': 'fa-strikethrough',
			'subscript': 'fa-subscript',
			'superscript': 'fa-superscript',
			'orderedlist': 'fa-list-ol',
			'unorderedlist': 'fa-list-ul',
			'outdent': 'fa-outdent',
			'indent': 'fa-indent',
			'link': 'fa-link',
			'alignment': 'fa-align-left',
			'table': 'fa-table'
		};
		
		var $buttons = this.getOption('buttons');
		var $lastButton = '';
		for (var $i = 0, $length = $buttons.length; $i < $length; $i++) {
			var $button = $buttons[$i];
			
			if ($button == 'separator') {
				this.buttonGet($lastButton).parent().addClass('separator');
				
				continue;
			}
			
			// check if button does not exist
			var $buttonObj = this.buttonGet($button);
			if ($buttonObj.length) {
				if ($faIcons[$button]) {
					this.buttonAwesome($button, $faIcons[$button]);
				}
			}
			else {
				this._addCoreButton($button, ($faIcons[$button] ? $faIcons[$button] : null), $lastButton);
			}
			
			$lastButton = $button;
		}
	},
	
	_addCoreButton: function(buttonName, faIcon, insertAfter) {
		var $button = this.buttonBuild(buttonName, {
			title: buttonName,
			exec: buttonName
		}, false);
		$('<li />').append($button).insertAfter(this.buttonGet(insertAfter).parent());
		
		if (faIcon !== null) {
			this.buttonAwesome(buttonName, faIcon);
		}
	},
	
	/**
	 * Adds a custom button.
	 * 
	 * @param	object<string>		data
	 */
	_addBBCodeButton: function(data) {
		var $buttonName = '__wcf_' + data.name;
		var $button = this.buttonAdd($buttonName, data.label, this._insertBBCode);
		this._bbcodes[$buttonName] = data.name;
		
		// FontAwesome class name
		if (data.icon.match(/^fa\-[a-z\-]+$/)) {
			this.buttonAwesome($buttonName, data.icon);
		}
		else {
			// image reference
			$button.css('background-image', 'url(' + __REDACTOR_ICON_PATH + data.icon + ')');
		}
	},
	
	/**
	 * Inserts the specified BBCode.
	 * 
	 * @param	string		buttonName
	 * @param	jQuery		buttonDOM
	 * @param	object		buttonObj
	 * @param	object		event
	 */
	_insertBBCode: function(buttonName, buttonDOM, buttonObj, event) {
		var $bbcode = this._bbcodes[buttonName];
		var $eventData = {
			buttonName: buttonName,
			buttonDOM: buttonDOM,
			buttonObj: buttonObj,
			event: event,
			cancel: false
		};
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'insertBBCode_' + $bbcode + '_' + this.$source.wcfIdentify(), $eventData);
		
		if ($eventData.cancel === false) {
			var $selectedHtml = this.getSelectionHtml();
			
			if ($bbcode === 'tt') {
				var $parent = (this.getParent()) ? $(this.getParent()) : null;
				if ($parent && $parent.closest('inline.inlineCode', this.$editor.get()[0]).length) {
					this.inlineRemoveClass('inlineCode');
				}
				else {
					this.inlineSetClass('inlineCode');
				}
			}
			else {
				this.insertHtml('[' + $bbcode + ']' + $selectedHtml + '[/' + $bbcode + ']');
			}
		}
		
		event.preventDefault();
		return false;
	}
};

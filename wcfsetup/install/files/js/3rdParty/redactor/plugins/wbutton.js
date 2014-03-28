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
		
		var $faIcons = {
			'alignment': 'align-left',
			'deleted': 'strikethrough',
			'html': 'file-o',
			'image': 'picture-o',
			'orderedlist': 'list-ol',
			'smiley': 'smile-o',
			'unorderedlist': 'list-ul'
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
			var $className = ($faIcons[$button]) ? $faIcons[$button] : $button;
			if ($buttonObj.length) {
				this.buttonAwesome($button, 'fa-' + $className);
			}
			else {
				this._addCoreButton($button, $className, $lastButton);
			}
			
			$lastButton = $button;
		}
	},
	
	_addCoreButton: function(buttonName, className, insertAfter) {
		var $button = this.buttonBuild(buttonName, {
			title: buttonName,
			exec: buttonName
		}, false);
		$('<li />').append($button).insertAfter(this.buttonGet(insertAfter).parent());
		
		this.buttonAwesome(buttonName, 'fa-' + className);
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
		var $selectedHtml = this.getSelectionHtml();
		this.insertHtml('[' + $bbcode + ']' + $selectedHtml + '[/' + $bbcode + ']');
		
		this.sync();
	}
};

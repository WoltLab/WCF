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
	},
	
	/**
	 * Adds a custom button.
	 * 
	 * @param	object<string>		data
	 */
	_addBBCodeButton: function(data) {
		var $buttonName = '__wcf_' + data.name;
		this.buttonAdd($buttonName, data.label, this._insertBBCode);
		this._bbcodes[$buttonName] = data.name;
		
		//
		// TODO: These are hardcoded for now, since the API does not provide class names yet, this has to be changes
		//
		var $iconName = '';
		switch (data.name) {
			case 'code':
				$iconName = 'fa-code';
			break;
			
			case 'quote':
				$iconName = 'fa-quote-left';
			break;
			
			case 'spoiler':
				$iconName = 'fa-eye-slash';
			break;
			
			case 'tt':
				$iconName = 'fa-font';
			break;
		}
		
		this.buttonAwesome($buttonName, $iconName);
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

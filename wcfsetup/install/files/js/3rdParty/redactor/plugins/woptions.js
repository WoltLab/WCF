if (!RedactorPlugins) var RedactorPlugins = {};

/**
 * Provides message option tabs for Redactor.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
RedactorPlugins.woptions = {
	/**
	 * list of message option elements
	 * @var	object<object>
	 */
	_messageOptions: { },
	
	/**
	 * message option container
	 * @var	jQuery
	 */
	_messageOptionContainer: null,
	
	/**
	 * navigation container
	 * @var	jQuery
	 */
	_messageOptionNavigation: null,
	
	/**
	 * Initializes the RedactorPlugins.woptions plugin.
	 */
	init: function() {
		var $options = this.getOption('wMessageOptions');
		if (!$options.length) {
			return;
		}
		
		this._messageOptionContainer = $('<div id="redactorMessageOptions" class="redactorMessageOptions" />').appendTo(this.$box);
		this._messageOptionNavigation = $('<nav><ul /></nav>').appendTo(this._messageOptionContainer).children('ul');
		
		for (var $i = 0; $i < $options.length; $i++) {
			var $container = $options[$i];
			
			var $listItem = $('<li><a>' + $container.title + '</a></li>').appendTo(this._messageOptionNavigation);
			$listItem.data('containerID', $container.containerID).click($.proxy(this._showMessageOptionContainer, this));
			
			var $tabContainer = $('<div class="redactorMessageOptionContainer" id="redactorMessageOptions_' + $container.containerID + '" />').hide().appendTo(this._messageOptionContainer);
			
			for (var $j = 0; $j < $container.items.length; $j++) {
				$($container.items[$j]).appendTo($tabContainer);
			}
			
			this._messageOptions[$container.containerID] = {
				container: $tabContainer,
				listItem: $listItem
			};
		}
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'updateMessageOptions', this._messageOptions);
		
		WCF.System.Event.addListener('com.woltlab.wcf.redactor', 'reset', $.proxy(this._wOptionsListener, this));
	},
	
	/**
	 * Toggles the specified message option container.
	 * 
	 * @param	object		event
	 * @param	string		containerID
	 */
	_showMessageOptionContainer: function(event, containerID) {
		var $containerID = (event === null) ? containerID : $(event.currentTarget).data('containerID');
		if (this._messageOptions[$containerID].listItem.hasClass('active')) {
			this._messageOptions[$containerID].listItem.removeClass('active');
			this._messageOptions[$containerID].container.hide();
			
			return;
		}
		
		$.each(this._messageOptions, function(containerID, elements) {
			if (containerID == $containerID) {
				elements.listItem.addClass('active');
				elements.container.show();
			}
			else {
				elements.listItem.removeClass('active');
				elements.container.hide();
			}
		});
	},
	
	/**
	 * Collapses all message option containers.
	 * 
	 * @param	object		data
	 */
	_wOptionsListener: function(data) {
		$.each(this._messageOptions, function(containerID, elements) {
			elements.listItem.removeClass('active');
			elements.container.hide();
			
			elements.container.find('input, select, textarea').each(function(index, element) {
				var $element = $(element);
				switch ($element.getTagName()) {
					case 'input':
						$element.prop('checked', false);
					break;
					
					default:
						$element.val('');
					break;
				}
			});
		});
		
		WCF.System.Event.fireEvent('com.woltlab.wcf.redactor', 'updateMessageOptions', this._messageOptions);
	}
};

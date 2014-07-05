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
	 * list of message option elements grouped by instance
	 * @var	object<object>
	 */
	_messageOptions: { },
	
	/**
	 * message option container per instance
	 * @var	object<jQuery>
	 */
	_messageOptionContainer: { },
	
	/**
	 * navigation container per instance
	 * @var	object<jQuery>
	 */
	_messageOptionNavigation: { },
	
	/**
	 * Initializes the RedactorPlugins.woptions plugin.
	 */
	init: function() {
		var $options = this.getOption('wMessageOptions');
		if (!$options.length) {
			return;
		}
		
		var $instanceID = this.$source.wcfIdentify();
		this.$box.wrap('<div class="redactorContainer" />')
		this._messageOptionContainer[$instanceID] = $('<div id="redactorMessageOptions" class="redactorMessageOptions" />').insertAfter(this.$box);
		this._messageOptionNavigation[$instanceID] = $('<nav><ul /></nav>').appendTo(this._messageOptionContainer[$instanceID]).children('ul');
		this._messageOptions[$instanceID] = { };
		
		for (var $i = 0; $i < $options.length; $i++) {
			var $container = $options[$i];
			
			var $listItem = $('<li><a>' + $container.title + '</a></li>').appendTo(this._messageOptionNavigation[$instanceID]);
			$listItem.data('containerID', $container.containerID).click($.proxy(this._showMessageOptionContainer, this));
			
			var $tabContainer = $('<div class="redactorMessageOptionContainer redactorMessageOptions_' + $container.containerID + '" />').hide().appendTo(this._messageOptionContainer[$instanceID]);
			for (var $j = 0; $j < $container.items.length; $j++) {
				$($container.items[$j]).appendTo($tabContainer);
			}
			
			this._messageOptions[$instanceID][$container.containerID] = {
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
		var $instanceID = this.$source.wcfIdentify();
		
		if (this._messageOptions[$instanceID][$containerID].listItem.hasClass('active')) {
			this._messageOptions[$instanceID][$containerID].listItem.removeClass('active');
			this._messageOptions[$instanceID][$containerID].container.hide();
			
			return;
		}
		
		$.each(this._messageOptions[$instanceID], function(containerID, elements) {
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

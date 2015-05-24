/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/BootstrapFrontend
 */
define(['Ajax', 'WoltLab/WCF/Bootstrap', 'WoltLab/WCF/Controller/Sitemap', 'WoltLab/WCF/Controller/Style/Changer', 'WoltLab/WCF/Controller/Popover'], function(Ajax, Bootstrap, ControllerSitemap, ControllerStyleChanger, ControllerPopover) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function BootstrapFrontend() {}
	BootstrapFrontend.prototype = {
		/**
		 * Bootstraps general modules and frontend exclusive ones.
		 * 
		 * @param	{object<string, *>}	options		bootstrap options
		 */
		setup: function(options) {
			Bootstrap.setup();
			
			ControllerSitemap.setup();
			
			if (options.styleChanger) {
				ControllerStyleChanger.setup();
			}
			
			this._initUserPopover();
		},
		
		/**
		 * Initializes user profile popover.
		 */
		_initUserPopover: function() {
			ControllerPopover.init({
				attributeName: 'data-user-id',
				className: 'userLink',
				identifier: 'com.woltlab.wcf.user',
				loadCallback: function(objectId, popover) {
					var callback = function(data) {
						popover.setContent('com.woltlab.wcf.user', objectId, data.returnValues.template);
					};
					
					popover.ajaxApi({
						actionName: 'getUserProfile',
						className: 'wcf\\data\\user\\UserProfileAction',
						objectIDs: [ objectId ]
					}, callback, callback);
				}
			});
		}
	};
	
	return new BootstrapFrontend();
});

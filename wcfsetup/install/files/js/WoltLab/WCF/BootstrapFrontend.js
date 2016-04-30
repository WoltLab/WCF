/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/BootstrapFrontend
 */
define(
	[
	 	'Ajax',                           'WoltLab/WCF/Bootstrap',      'WoltLab/WCF/Controller/Sitemap', 'WoltLab/WCF/Controller/Style/Changer',
	 	'WoltLab/WCF/Controller/Popover', 'WoltLab/WCF/Ui/User/Ignore'
	],
	function(
		Ajax,                              Bootstrap,                    ControllerSitemap,                ControllerStyleChanger,
		ControllerPopover,                 UiUserIgnore
	)
{
	"use strict";
	
	var queueInvocations = 0;
	
	/**
	 * @exports	WoltLab/WCF/BootstrapFrontend
	 */
	return {
		/**
		 * Bootstraps general modules and frontend exclusive ones.
		 * 
		 * @param	{object<string, *>}	options		bootstrap options
		 */
		setup: function(options) {
			Bootstrap.setup();
			
			ControllerSitemap.setup();
			
			if (options.styleChanger) {
				//ControllerStyleChanger.setup();
			}
			
			this._initUserPopover();
			this._invokeBackgroundQueue(options.backgroundQueue.url, options.backgroundQueue.force);
			
			UiUserIgnore.init();
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
		},
		
		/**
		 * Invokes the background queue roughly every 10th request.
		 * 
		 * @param	{string}	url	background queue url
		 * @param	{boolean}	force	whether execution should be forced
		 */
		_invokeBackgroundQueue: function(url, force) {
			var again = this._invokeBackgroundQueue.bind(this, url, true);
			
			if (Math.random() < 0.1 || force) {
				// 'fire and forget' background queue perform task
				Ajax.apiOnce({
					url: url,
					ignoreError: true,
					silent: true,
					success: (function(data) {
						queueInvocations++;
						
						// process up to 5 queue items per page load
						if (data > 0 && queueInvocations < 5) setTimeout(again, 1000);
					}).bind(this)
				});
			}
		}
	};
});

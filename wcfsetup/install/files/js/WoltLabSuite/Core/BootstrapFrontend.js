/**
 * Bootstraps WCF's JavaScript with additions for the frontend usage.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/BootstrapFrontend
 */
define(
	[
	 	'Ajax',                           'WoltLabSuite/Core/Bootstrap',      'WoltLabSuite/Core/Controller/Style/Changer',
	 	'WoltLabSuite/Core/Controller/Popover', 'WoltLabSuite/Core/Ui/User/Ignore', 'WoltLabSuite/Core/Ui/Page/Header/Menu'
	],
	function(
		Ajax,                              Bootstrap,                    ControllerStyleChanger,
		ControllerPopover,                 UiUserIgnore, UiPageHeaderMenu
	)
{
	"use strict";
	
	var queueInvocations = 0;
	
	/**
	 * @exports	WoltLabSuite/Core/BootstrapFrontend
	 */
	return {
		/**
		 * Bootstraps general modules and frontend exclusive ones.
		 * 
		 * @param	{object<string, *>}	options		bootstrap options
		 */
		setup: function(options) {
			// fix the background queue URL to always run against the current domain (avoiding CORS)
			options.backgroundQueue.url = WSC_API_URL + options.backgroundQueue.url.substr(WCF_PATH.length);
			
			Bootstrap.setup();
			
			UiPageHeaderMenu.init();
			
			if (options.styleChanger) {
				ControllerStyleChanger.setup();
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

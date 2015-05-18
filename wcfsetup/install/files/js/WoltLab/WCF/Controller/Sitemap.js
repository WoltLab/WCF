/**
 * Provides the sitemap dialog.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Sitemap
 */
define(['EventHandler', 'Language', 'DOM/Util', 'UI/Dialog', 'UI/TabMenu'], function(EventHandler, Language, DOMUtil, UIDialog, UITabMenu) {
	"use strict";
	
	var _cache = [];
	var _dialog = null;
	
	/**
	 * @constructor
	 */
	function ControllerSitemap() {};
	ControllerSitemap.prototype = {
		/**
		 * Binds click handler.
		 */
		setup: function() {
			document.getElementById('sitemap').addEventListener('click', this._click.bind(this));
		},
		
		/**
		 * Handles clicks on the sitemap button.
		 * 
		 * @param	{object}	event	event object
		 */
		_click: function(event) {
			event.preventDefault();
			
			if (UIDialog.getDialog('sitemapDialog') === null) {
				new WCF.Action.Proxy({
					autoSend: true,
					data: {
						actionName: 'getSitemap',
						className: 'wcf\\data\\sitemap\\SitemapAction'
					},
					success: (function(data) {
						_cache.push(data.returnValues.sitemapName);
						
						_dialog = UIDialog.open('sitemapDialog', data.returnValues.template, {
							disableContentPadding: true,
							title: Language.get('wcf.page.sitemap')
						});
						
						var tabMenuContainer = _dialog.content.querySelector('.tabMenuContainer');
						var menuId = DOMUtil.identify(tabMenuContainer);
						
						UITabMenu.getTabMenu(menuId).select('sitemap_' + data.returnValues.sitemapName);
						
						EventHandler.add('com.woltlab.wcf.simpleTabMenu_' + menuId, 'select', this.showTab.bind(this));
					}).bind(this)
				});
			}
			else {
				UIDialog.open('sitemapDialog');
			}
		},
		
		/**
		 * Callback for tab links, lazy loads content.
		 * 
		 * @param	{object<string, Element>}	tabData		tab data
		 */
		showTab: function(tabData) {
			var name = tabData.active.getAttribute('data-name').replace(/^sitemap_/, '');
			
			if (_cache.indexOf(name) === -1) {
				new WCF.Action.Proxy({
					autoSend: true,
					data: {
						actionName: 'getSitemap',
						className: 'wcf\\data\\sitemap\\SitemapAction',
						parameters: {
							sitemapName: name
						}
					},
					success: function(data) {
						_cache.push(data.returnValues.sitemapName);
						
						document.getElementById('sitemap_' + data.returnValues.sitemapName).innerHTML = data.returnValues.template;
					}
				});
			}
		} 
	};
	
	return new ControllerSitemap();
});

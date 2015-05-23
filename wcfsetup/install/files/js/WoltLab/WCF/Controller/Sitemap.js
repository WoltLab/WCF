/**
 * Provides the sitemap dialog.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Sitemap
 */
define(['Ajax', 'EventHandler', 'Language', 'DOM/Util', 'UI/Dialog', 'UI/TabMenu'], function(Ajax, EventHandler, Language, DOMUtil, UIDialog, UITabMenu) {
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
			
			if (UIDialog.getDialog('sitemapDialog') === undefined) {
				Ajax.apiOnce({
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
		
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'getSitemap',
					className: 'wcf\\data\\sitemap\\SitemapAction'
				}
			};
		},
		
		_ajaxSuccess: function(data) {
			_cache.push(data.returnValues.sitemapName);
			
			document.getElementById('sitemap_' + data.returnValues.sitemapName).innerHTML = data.returnValues.template;
		},
		
		/**
		 * Callback for tab links, lazy loads content.
		 * 
		 * @param	{object<string, Element>}	tabData		tab data
		 */
		showTab: function(tabData) {
			var name = tabData.active.getAttribute('data-name').replace(/^sitemap_/, '');
			
			if (_cache.indexOf(name) === -1) {
				Ajax.api(this, {
					parameters: {
						sitemapName: name
					}
				});
			}
		} 
	};
	
	return new ControllerSitemap();
});

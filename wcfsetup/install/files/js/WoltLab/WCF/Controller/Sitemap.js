/**
 * Provides the sitemap dialog.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Sitemap
 */
define(['Ajax', 'EventHandler', 'Language', 'Dom/Util', 'Ui/Dialog', 'Ui/TabMenu'], function(Ajax, EventHandler, Language, DomUtil, UiDialog, UiTabMenu) {
	"use strict";
	
	var _cache = [];
	var _dialog = null;
	
	/**
	 * @exports	WoltLab/WCF/Controller/Sitemap
	 */
	var ControllerSitemap = {
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
			
			UiDialog.open(this);
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
		
		_dialogSetup: function() {
			return {
				id: 'sitemapDialog',
				options: {
					disableContentPadding: true,
					title: Language.get('wcf.page.sitemap')
				},
				source: {
					data: {
						actionName: 'getSitemap',
						className: 'wcf\\data\\sitemap\\SitemapAction'
					},
					after: (function(content, data) {
						_cache.push(data.returnValues.sitemapName);
						
						var tabMenuContainer = content.querySelector('.tabMenuContainer');
						var menuId = DomUtil.identify(tabMenuContainer);
						
						UiTabMenu.getTabMenu(menuId).select('sitemap_' + data.returnValues.sitemapName);
						
						EventHandler.add('com.woltlab.wcf.simpleTabMenu_' + menuId, 'select', this.showTab.bind(this));
					}).bind(this)
				}
			};
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
	
	return ControllerSitemap;
});

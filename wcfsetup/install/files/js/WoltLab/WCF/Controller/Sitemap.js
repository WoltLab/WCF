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
	
	/**
	 * @exports	WoltLab/WCF/Controller/Sitemap
	 */
	return {
		/**
		 * Binds click handler.
		 */
		setup: function() {
			elBySel('#sitemap > a').addEventListener(WCF_CLICK_EVENT, this.open.bind(this));
		},
		
		/**
		 * Handles clicks on the sitemap button.
		 * 
		 * @param	{Event}         event	event object
		 */
		open: function(event) {
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
			
			elById('sitemap_' + data.returnValues.sitemapName).children[0].innerHTML = data.returnValues.template;
		},
		
		_dialogSetup: function() {
			return {
				id: 'sitemapDialog',
				options: {
					title: Language.get('wcf.page.sitemap')
				},
				source: {
					data: {
						actionName: 'getSitemap',
						className: 'wcf\\data\\sitemap\\SitemapAction'
					},
					after: (function(content, data) {
						_cache.push(data.returnValues.sitemapName);
						
						var tabMenuContainer = elBySel('.tabMenuContainer', content);
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
			var name = elAttr(tabData.active, 'data-name').replace(/^sitemap_/, '');
			
			if (_cache.indexOf(name) === -1) {
				Ajax.api(this, {
					parameters: {
						sitemapName: name
					}
				});
			}
		} 
	};
});

/**
 * Simple tab menu implementation with a straight-forward logic.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/UI/TabMenu/Simple
 */
define(['Dictionary', 'DOM/Traverse', 'DOM/Util', 'EventHandler'], function(Dictionary, DOMTraverse, DOMUtil, EventHandler) {
	"use strict";
	
	/**
	 * @param	{string}	containerId	container id
	 * @param	{Element}	container	container element
	 * @constructor
	 */
	function TabMenuSimple(containerId, container) {
		this._container = container;
		this._containers = new Dictionary();
		this._containerId = containerId;
		this._isLegacy = null;
		this._isParent = false;
		this._parent = null;
		this._tabs = new Dictionary();
	};
	
	TabMenuSimple.prototype = {
		/**
		 * Validates the properties and DOM structure of this container.
		 * 
		 * Expected DOM:
		 * <div class="tabMenuContainer">
		 * 	<nav>
		 * 		<ul>
		 * 			<li data-name="foo"><a>bar</a></li>
		 * 		</ul>
		 * 	</nav>
		 * 	
		 * 	<div id="foo">baz</div>
		 * </div>
		 * 
		 * @return	{boolean}	false if any properties are invalid or the DOM does not match the expectations
		 */
		validate: function() {
			if (!this._container.classList.contains('tabMenuContainer')) {
				return false;
			}
			
			var nav = DOMTraverse.childByTag(this._container, 'NAV');
			if (nav === null) {
				return false;
			}
			
			// get children
			var tabs = nav.getElementsByTagName('li');
			if (tabs.length === null) {
				return false;
			}
			
			var containers = DOMTraverse.childrenByTag(this._container, 'DIV');
			for (var i = 0, length = containers.length; i < length; i++) {
				var container = containers[i];
				var name = container.getAttribute('data-name');
				
				if (!name) {
					name = DOMUtil.identify(container);
				}
				
				container.setAttribute('data-name', name);
				this._containers.set(name, container);
			}
			
			for (var i = 0, length = tabs.length; i < length; i++) {
				var tab = tabs[i];
				var name = this._getTabName(tab);
				
				if (!name) {
					continue;
				}
				
				if (this._tabs.has(name)) {
					throw new Error("Tab names must be unique, li[data-name='" + name + "'] (tab menu id: '" + this._containerId + "') exists more than once.");
				}
				
				var container = this._containers.get(name);
				if (container === undefined) {
					throw new Error("Expected content element for li[data-name='" + name + "'] (tab menu id: '" + this._containerId + "').");
				}
				else if (container.parentNode !== this._container) {
					throw new Error("Expected content element '" + name + "' (tab menu id: '" + this._containerId + "') to be a direct children.");
				}
				
				// check if tab holds exactly one children which is an anchor element
				if (tab.childElementCount !== 1 || tab.children[0].nodeName !== 'A') {
					throw new Error("Expected exactly one <a> as children for li[data-name='" + name + "'] (tab menu id: '" + this._containerId + "').");
				}
				
				this._tabs.set(name, tab);
			}
			
			if (!this._tabs.size) {
				throw new Error("Expected at least one tab (tab menu id: '" + this._containerId + "').");
			}
			
			if (this._isLegacy) {
				this._container.setAttribute('data-is-legacy', true);
				
				this._tabs.forEach(function(tab, name) {
					tab.setAttribute('aria-controls', name);
				});
			}
			
			return true;
		},
		
		/**
		 * Initializes this tab menu.
		 * 
		 * @param	{Dictionary=}	oldTabs		previous list of tabs
		 */
		init: function(oldTabs) {
			oldTabs = oldTabs || null;
			
			// bind listeners
			this._tabs.forEach((function(tab) {
				if (oldTabs === null || oldTabs.get(tab.getAttribute('data-name')) !== tab) {
					tab.children[0].addEventListener('click', this._onClick.bind(this));
				}
			}).bind(this));
			
			if (oldTabs === null) {
				var preselect = this._container.getAttribute('data-preselect');
				if (preselect === "true" || preselect === null || preselect === "") preselect = true;
				if (preselect === "false") preselect = false;
				
				this._containers.forEach(function(container) {
					container.classList.add('hidden');
				});
				
				if (preselect !== false) {
					if (preselect !== true) {
						var tab = this._tabs.get(preselect);
						if (tab !== undefined) {
							this.select(null, tab, true);
						}
					}
					else {
						var selectTab = null;
						this._tabs.forEach(function(tab) {
							if (selectTab === null && tab.previousElementSibling === null) {
								selectTab = tab; 
							}
						});
						
						if (selectTab !== null) {
							this.select(null, selectTab, true);
						}
					}
				}
			}
		},
		
		/**
		 * Selects a tab.
		 * 
		 * @param	{?(string|integer)}	name		tab name or sequence no
		 * @param	{Element=}		tab		tab element
		 * @param	{boolean=}		disableEvent	suppress event handling
		 */
		select: function(name, tab, disableEvent) {
			tab = tab || this._tabs.get(name) || null;
			
			if (tab === null) {
				// check if name is an integer
				if (~~name == name) {
					name = ~~name;
					
					var i = 0;
					this._tabs.forEach(function(item) {
						if (i === name) {
							tab = item;
						}
						
						i++;
					});
				}
				
				if (tab === null) {
					throw new Error("Expected a valid tab name, '" + name + "' given (tab menu id: '" + this._containerId + "').");
				}
			}
			
			if (!name) name = tab.getAttribute('data-name');
			
			// unmark active tab
			var oldTab = document.querySelector('#' + this._containerId + ' > nav > ul > li.active');
			var oldContent = null;
			if (oldTab !== null) {
				oldTab.classList.remove('active');
				oldContent = this._containers.get(oldTab.getAttribute('data-name'));
				oldContent.classList.remove('active');
				oldContent.classList.add('hidden');
				
				if (this._isLegacy) {
					oldTab.classList.remove('ui-state-active');
					oldContent.classList.remove('ui-state-active');
				}
			}
			
			tab.classList.add('active');
			var newContent = this._containers.get(name);
			newContent.classList.add('active');
			
			if (this._isLegacy) {
				tab.classList.add('ui-state-active');
				newContent.classList.add('ui-state-active');
				newContent.classList.remove('hidden');
			}
			
			if (disableEvent !== true) {
				EventHandler.fire('com.woltlab.wcf.simpleTabMenu_' + this._containerId, 'select', {
					active: tab,
					previous: oldTab,
				});
				
				if (this._isLegacy && typeof window.jQuery === 'function') {
					// simulate jQuery UI Tabs event
					window.jQuery(this._container).trigger('wcftabsbeforeactivate', {
						newTab: window.jQuery(tab),
						oldTab: window.jQuery(oldTab),
						newPanel: window.jQuery(newContent),
						oldPanel: window.jQuery(oldContent)
					});
				}
			}
		},
		
		/**
		 * Rebuilds all tabs, must be invoked after adding or removing of tabs.
		 * 
		 * Warning: Do not remove tabs if you plan to add these later again or at least clone the nodes
		 *          to prevent issues with already bound event listeners. Consider hiding them via CSS.
		 */
		rebuild: function() {
			var oldTabs = this._tabs;
			
			this.validate();
			this.init(oldTabs);
		},
		
		/**
		 * Handles clicks on a tab.
		 * 
		 * @param	{object}	event	event object
		 */
		_onClick: function(event) {
			event.preventDefault();
			
			var tab = event.currentTarget.parentNode;
			
			this.select(null, tab);
		},
		
		/**
		 * Returns the tab name.
		 * 
		 * @param	{Element}	tab	tab element
		 * @return	{string}	tab name
		 */
		_getTabName: function(tab) {
			var name = tab.getAttribute('data-name');
			
			// handle legacy tab menus
			if (!name) {
				if (tab.childElementCount === 1 && tab.children[0].nodeName === 'A') {
					var href = tab.children[0].getAttribute('href');
					if (href.match(/#([^#]+)$/)) {
						name = RegExp.$1;
						
						if (document.getElementById(name) === null) {
							name = null;
						}
						else {
							this._isLegacy = true;
							tab.setAttribute('data-name', name);
						}
					}
				}
			}
			
			return name;
		},
		
		/**
		 * Returns the list of registered content containers.
		 * 
		 * @returns	{Dictionary}	content containers
		 */
		getContainers: function() {
			return this._containers;
		},
		
		/**
		 * Returns the list of registered tabs.
		 * 
		 * @returns	{Dictionary}	tab items
		 */
		getTabs: function() {
			return this._tabs;
		}
	};
	
	return TabMenuSimple;
});

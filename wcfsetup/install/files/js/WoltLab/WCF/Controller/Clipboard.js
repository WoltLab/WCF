/**
 * Clipboard API Handler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Controller/Clipboard
 */
define(
	[
		'Ajax',         'Core',     'Dictionary',      'EventHandler',
		'Language',     'List',     'ObjectMap',       'DOM/ChangeListener',
		'DOM/Traverse', 'DOM/Util', 'UI/Confirmation', 'UI/SimpleDropdown'
	],
	function(
		Ajax,            Core,       Dictionary,        EventHandler,
		Language,        List,       ObjectMap,         DOMChangeListener,
		DOMTraverse,     DOMUtil,    UIConfirmation,    UISimpleDropdown
	)
{
	"use strict";
	
	var _containers = new Dictionary();
	var _editors = new Dictionary();
	var _elements = document.getElementsByClassName('jsClipboardContainer');
	var _itemData = new ObjectMap();
	var _knownCheckboxes = new List();
	var _options = {};
	
	var _callbackCheckbox = null;
	var _callbackItem = null;
	var _callbackUnmarkAll = null;
	
	/**
	 * Clipboard API
	 * 
	 * @exports	WoltLab/WCF/Controller/Clipboard
	 */
	var ControllerClipboard = {
		/**
		 * Initializes the clipboard API handler.
		 * 
		 * @param	{object<string, *>}	options		initialization options
		 */
		setup: function(options) {
			_callbackCheckbox = this._mark.bind(this);
			_callbackItem = this._executeAction.bind(this);
			_callbackUnmarkAll = this._unmarkAll.bind(this);
			_options = Core.extend({
				hasMarkedItems: false,
				pageClassName: '',
				pageObjectId: 0
			}, options);
			
			if (!_options.pageClassName) {
				throw new Error("Expected a non-empty string for parameter 'pageClassName'.");
			}
			
			this._initContainers();
			this._initEditors();
			
			if (_options.hasMarkedItems && _elements.length) {
				this._loadMarkedItems();
			}
			
			DOMChangeListener.add('WoltLab/WCF/Controller/Clipboard', this._initContainers.bind(this));
		},
		
		/**
		 * Reloads the clipboard data.
		 */
		reload: function() {
			if (_containers.size) {
				this._loadMarkedItems();
			}
		},
		
		/**
		 * Initializes clipboard containers.
		 */
		_initContainers: function() {
			for (var i = 0, length = _elements.length; i < length; i++) {
				var container = _elements[i];
				var containerId = DOMUtil.identify(container);
				var containerData = _containers.get(containerId);
				
				if (containerData === undefined) {
					var markAll = container.querySelector('.jsClipboardMarkAll');
					markAll.setAttribute('data-container-id', containerId);
					markAll.addEventListener('click', this._markAll.bind(this));
					
					containerData = {
						checkboxes: container.getElementsByClassName('jsClipboardItem'),
						element: container,
						markAll: markAll,
						markedObjectIds: new List()
					};
					_containers.set(containerId, containerData);
				}
				
				for (var j = 0, innerLength = containerData.checkboxes.length; j < innerLength; j++) {
					var checkbox = containerData.checkboxes[j];
					
					if (!_knownCheckboxes.has(checkbox)) {
						checkbox.setAttribute('data-container-id', containerId);
						checkbox.addEventListener('click', _callbackCheckbox);
						
						_knownCheckboxes.add(checkbox);
					}
				}
			}
		},
		
		/**
		 * Initializes the clipboard editor dropdowns.
		 */
		_initEditors: function() {
			var getTypes = function(editor) {
				var tmp = null;
				
				try {
					var types = editor.getAttribute('data-types');
					if (typeof types === 'string') {
						tmp = JSON.parse('{ "types": ' + types.replace(/'/g, '"') + '}');
					}
				}
				catch (e) {
					throw new Error("Expected a valid 'data-type' attribute for element '" + DOMUtil.identify(editor) + "'.");
				}
				
				return tmp.types;
				if (types !== null) {
					types = types.types;
				}
				
				return types;
			};
			
			var editors = document.getElementsByClassName('jsClipboardEditor');
			for (var i = 0, length = editors.length; i < length; i++) {
				var editor = editors[i];
				var types = getTypes(editor);
				
				for (var j = 0, innerLength = types.length; j < innerLength; j++) {
					_editors.set(types[j], editor);
				}
			}
		},
		
		/**
		 * Loads marked items from clipboard.
		 */
		_loadMarkedItems: function() {
			Ajax.api(this, {
				actionName: 'getMarkedItems',
				parameters: {
					pageClassName: _options.pageClassName,
					pageObjectID: _options.pageObjectId
				}
			});
		},
		
		/**
		 * Marks or unmarks all visible items at once.
		 * 
		 * @param	{object}	event	event object
		 */
		_markAll: function(event) {
			var checkbox = event.currentTarget;
			var isMarked = (checkbox.nodeName !== 'INPUT' || checkbox.checked);
			var objectIds = [];
			
			var containerId = checkbox.getAttribute('data-container-id');
			var data = _containers.get(containerId);
			var type = data.element.getAttribute('data-type');
			
			for (var i = 0, length = data.checkboxes.length; i < length; i++) {
				var item = data.checkboxes[i];
				var objectId = ~~item.getAttribute('data-object-id');
				
				if (isMarked) {
					if (!item.checked) {
						item.checked = true;
						
						data.markedObjectIds.add(objectId);
						objectIds.push(objectId);
					}
				}
				else {
					if (item.checked) {
						item.checked = false;
						
						data.markedObjectIds['delete'](objectId);
						objectIds.push(objectId);
					}
				}
				
				var clipboardObject = DOMTraverse.parentByClass(checkbox, 'jsClipboardObject');
				if (clipboardObject !== null) {
					clipboardObject.classList[(isMarked ? 'addClass' : 'removeClass')]('jsMarked');
				}
			}
			
			this._saveState(type, objectIds, isMarked);
		},
		
		/**
		 * Marks or unmarks an individual item.
		 * 
		 * @param	{object}	event		event object
		 */
		_mark: function(event) {
			var checkbox = event.currentTarget;
			var objectId = ~~checkbox.getAttribute('data-object-id');
			var isMarked = checkbox.checked;
			var containerId = checkbox.getAttribute('data-container-id');
			var data = _containers.get(containerId);
			var type = data.element.getAttribute('data-type');
			
			var clipboardObject = DOMTraverse.parentByClass(checkbox, 'jsClipboardObject');
			data.markedObjectIds[(isMarked ? 'add' : 'delete')](objectId);
			clipboardObject.classList[(isMarked) ? 'add' : 'remove']('jsMarked');
			
			var markedAll = true;
			for (var i = 0, length = data.checkboxes.length; i < length; i++) {
				if (!data.checkboxes[i].checked) {
					markedAll = false;
					
					break;
				}
			}
			
			data.markAll.checked = markedAll;
			
			this._saveState(type, [ objectId ], isMarked);
		},
		
		/**
		 * Saves the state for given item object ids.
		 * 
		 * @param	{string}		type		object type
		 * @param	{array<integer>}	objectIds	item object ids
		 * @param	{boolean]		isMarked	true if marked
		 */
		_saveState: function(type, objectIds, isMarked) {
			Ajax.api(this, {
				actionName: (isMarked ? 'mark' : 'unmark'),
				parameters: {
					pageClassName: _options.pageClassName,
					pageObjectID: _options.pageObjectId,
					objectIDs: objectIds,
					objectType: type
				}
			});
		},
		
		/**
		 * Executes an editor action.
		 * 
		 * @param	{object}	event		event object
		 */
		_executeAction: function(event) {
			var listItem = event.currentTarget;
			var data = _itemData.get(listItem);
			
			if (data.url) {
				window.location.href = data.url;
				return;
			}
			
			var triggerEvent = function() {
				var type = listItem.getAttribute('data-type');
				
				EventHandler.fire('com.woltlab.wcf.clipboard', type, {
					data: data,
					listItem: listItem,
					responseData: null
				});
				
				if (typeof window.jQuery === 'function') {
					window.jQuery(_editors.get(type)).trigger('clipboardAction', [ type, data.actionName, data.parameters ]);
				}
			};
			
			var confirmMessage = (typeof data.internalData.confirmMessage === 'string') ? data.internalData.confirmMessage : '';
			var fireEvent = true;
			
			if (typeof data.parameters === 'object' && data.parameters.actionName && data.parameters.className) {
				if (data.parameters.actionName === 'unmarkAll' || Array.isArray(data.parameters.objectIDs)) {
					if (confirmMessage.length) {
						var template = (typeof data.internalData.template === 'string') ? data.internalData.template : '';
						
						UIConfirmation.show({
							confirm: (function() {
								var formData = {};
								
								if (template.length) {
									var items = UIConfirmation.getContentElement().querySelectorAll('input, select, textarea');
									for (var i = 0, length = items.length; i < length; i++) {
										var item = items[i];
										var name = item.getAttribute('name');
										
										switch (item.nodeName) {
											case 'INPUT':
												if (item.checked) {
													formData[name] = item.getAttribute('value');
												}
												break;
											
											case 'SELECT':
												formData[name] = item.value;
												break;
											
											case 'TEXTAREA':
												formData[name] = item.value.trim();
												break;
										}
									}
								}
								
								this._executeProxyAction(listItem, data, formData);
							}).bind(this),
							message: confirmMessage,
							template: template
						});
					}
					else {
						this._executeProxyAction(listItem, data);
					}
				}
			}
			else if (confirmMessage.length) {
				fireEvent = false;
				
				UIConfirmation.show({
					confirm: triggerEvent,
					message: confirmMessage
				});
			}
			
			if (fireEvent) {
				triggerEvent();
			}
		},
		
		/**
		 * Forwards clipboard actions to an individual handler.
		 * 
		 * @param	{Element}		listItem	dropdown item element
		 * @param	{object<string, *>}	data		action data
		 * @param	{object<string, *>=}	formData	form data
		 */
		_executeProxyAction: function(listItem, data, formData) {
			formData = formData || {};
			
			var objectIds = (data.parameters.actionName !== 'unmarkAll') ? data.parameters.objectIDs : [];
			var parameters = { data: formData };
			
			if (typeof data.internalData.parameters === 'object') {
				for (var key in data.internalData.parameters) {
					if (data.internalData.parameters.hasOwnProperty(key)) {
						parameters[key] = data.internalData.parameters[key];
					}
				}
			}
			
			Ajax.api(this, {
				actionName: data.parameters.actionName,
				className: data.parameters.className,
				objectIDs: objectIds,
				parameters: parameters
			}, (function(responseData) {
				if (data.actionName !== 'unmarkAll') {
					var type = listItem.getAttribute('data-type');
					
					EventHandler.fire('com.woltlab.wcf.clipboard', type, {
						data: data,
						listItem: listItem,
						responseData: responseData
					});
					
					if (typeof window.jQuery === 'function') {
						window.jQuery(_editors.get(type)).trigger('clipboardActionResponse', [ responseData, type, data.actionName, data.parameters ]);
					}
				}
				
				this._loadMarkedItems();
			}).bind(this));
		},
		
		/**
		 * Unmarks all clipboard items for an object type.
		 * 
		 * @param	{object}	event		event object
		 */
		_unmarkAll: function(event) {
			var type = event.currentTarget.getAttribute('data-type');
			
			Ajax.api(this, {
				actionName: 'unmarkAll',
				parameters: {
					objectType: type
				}
			});
		},
		
		/**
		 * Sets up ajax request object.
		 * 
		 * @return	{object}	request options
		 */
		_ajaxSetup: function() {
			return {
				data: {
					className: 'wcf\\data\\clipboard\\item\\ClipboardItemAction'
				}
			};
		},
		
		/**
		 * Handles successful AJAX requests.
		 * 
		 * @param	{object}	data	response data
		 */
		_ajaxSuccess: function(data) {
			if (data.actionName === 'unmarkAll') {
				_containers.forEach((function(containerData) {
					if (containerData.element.getAttribute('data-type') === data.returnValues.objectType) {
						var clipboardObjects = containerData.element.getElementsByClassName('jsMarked');
						while (clipboardObjects.length) {
							clipboardObjects[0].classList.remove('jsMarked');
						}
						
						containerData.markAll.checked = false;
						for (var i = 0, length = containerData.checkboxes.length; i < length; i++) {
							containerData.checkboxes[i].checked = false;
						}
						
						_editors.get(data.returnValues.objectType).innerHTML = '';
					}
				}).bind(this));
				
				return;
			}
			
			// clear editors
			_editors.forEach(function(editor) {
				editor.innerHTML = '';
			});
			_itemData = new ObjectMap();
			
			// rebuild markings
			_containers.forEach((function(containerData) {
				var typeName = containerData.element.getAttribute('data-type');
				
				var objectIds = (data.returnValues.markedItems.hasOwnProperty(typeName)) ? data.returnValues.markedItems[typeName] : [];
				this._rebuildMarkings(containerData, objectIds);
			}).bind(this));
			
			// no marked items
			if (!data.returnValues || !data.returnValues.items) {
				return;
			}
			
			// rebuild editors
			var fragment = document.createDocumentFragment();
			for (var typeName in data.returnValues.items) {
				if (!data.returnValues.items.hasOwnProperty(typeName) || !_editors.has(typeName)) {
					continue;
				}
				
				var typeData = data.returnValues.items[typeName];
				
				var editor = _editors.get(typeName);
				var lists = DOMTraverse.childrenByTag('UL');
				var list = lists[0] || null;
				if (list === null) {
					list = document.createElement('ul');
				}
				
				fragment.appendChild(list);
				
				var listItem = document.createElement('li');
				listItem.classList.add('dropdown');
				list.appendChild(listItem);
				
				var toggleButton = document.createElement('span');
				toggleButton.className = 'dropdownToggle button';
				toggleButton.textContent = typeData.label;
				listItem.appendChild(toggleButton);
				
				var itemList = document.createElement('ol');
				itemList.classList.add('dropdownMenu');
				
				// create editor items
				for (var itemIndex in typeData.items) {
					if (!typeData.items.hasOwnProperty(itemIndex)) continue;
					
					var itemData = typeData.items[itemIndex];
					
					var item = document.createElement('li');
					var label = document.createElement('span');
					label.textContent = itemData.label;
					item.appendChild(label);
					itemList.appendChild(item);
					
					item.setAttribute('data-type', typeName);
					item.addEventListener('click', _callbackItem);
					
					_itemData.set(item, itemData);
				}
				
				var divider = document.createElement('li');
				divider.classList.add('dropdownDivider');
				itemList.appendChild(divider);
				
				// add 'unmark all'
				var unmarkAll = document.createElement('li');
				unmarkAll.setAttribute('data-type', typeName);
				var label = document.createElement('span');
				label.textContent = Language.get('wcf.clipboard.item.unmarkAll');
				unmarkAll.appendChild(label);
				itemList.appendChild(unmarkAll);
				listItem.appendChild(itemList);
				
				unmarkAll.addEventListener('click', _callbackUnmarkAll);
				editor.appendChild(fragment);
				
				UISimpleDropdown.init(toggleButton, false);
			}
		},
		
		/**
		 * Rebuilds the mark state for each item.
		 * 
		 * @param	{object<string, *>}	data		container data
		 * @param	{array<integer>}	objectIds	item object ids
		 */
		_rebuildMarkings: function(data, objectIds) {
			var markAll = true;
			
			for (var i = 0, length = data.checkboxes.length; i < length; i++) {
				var checkbox = data.checkboxes[i];
				var clipboardObject = DOMTraverse.parentByClass(checkbox, 'jsClipboardObject');
				
				var isMarked = (objectIds.indexOf(~~checkbox.getAttribute('data-object-id')) !== -1);
				if (!isMarked) markAll = false;
				
				checkbox.checked = isMarked;
				clipboardObject.classList[(isMarked ? 'add' : 'remove')]('jsMarked');
			}
			
			data.markAll.checked = markAll;
		}
	};
	
	return ControllerClipboard;
});

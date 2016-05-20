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
		'Language',     'List',     'ObjectMap',       'Dom/ChangeListener',
		'Dom/Traverse', 'Dom/Util', 'Ui/Confirmation', 'Ui/SimpleDropdown',
		'WoltLab/WCF/Ui/Page/Action'
	],
	function(
		Ajax,            Core,       Dictionary,        EventHandler,
		Language,        List,       ObjectMap,         DomChangeListener,
		DomTraverse,     DomUtil,    UiConfirmation,    UiSimpleDropdown,
	        UiPageAction
	)
{
	"use strict";
	
	var _containers = new Dictionary();
	var _editors = new Dictionary();
	var _editorDropdowns = new Dictionary();
	var _elements = elByClass('jsClipboardContainer');
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
	return {
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
			
			if (_options.hasMarkedItems && _elements.length) {
				this._loadMarkedItems();
			}
			
			DomChangeListener.add('WoltLab/WCF/Controller/Clipboard', this._initContainers.bind(this));
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
				var containerId = DomUtil.identify(container);
				var containerData = _containers.get(containerId);
				
				if (containerData === undefined) {
					var markAll = elBySel('.jsClipboardMarkAll', container);
					if (markAll !== null) {
						elData(markAll, 'container-id', containerId);
						markAll.addEventListener(WCF_CLICK_EVENT, this._markAll.bind(this));
					}
					
					containerData = {
						checkboxes: elByClass('jsClipboardItem', container),
						element: container,
						markAll: markAll,
						markedObjectIds: new List()
					};
					_containers.set(containerId, containerData);
				}
				
				for (var j = 0, innerLength = containerData.checkboxes.length; j < innerLength; j++) {
					var checkbox = containerData.checkboxes[j];
					
					if (!_knownCheckboxes.has(checkbox)) {
						elData(checkbox, 'container-id', containerId);
						checkbox.addEventListener(WCF_CLICK_EVENT, _callbackCheckbox);
						
						_knownCheckboxes.add(checkbox);
					}
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
			
			var containerId = elData(checkbox, 'container-id');
			var data = _containers.get(containerId);
			var type = elData(data.element, 'type');
			
			for (var i = 0, length = data.checkboxes.length; i < length; i++) {
				var item = data.checkboxes[i];
				var objectId = ~~elData(item, 'object-id');
				
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
				
				var clipboardObject = DomTraverse.parentByClass(checkbox, 'jsClipboardObject');
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
			var objectId = ~~elData(checkbox, 'object-id');
			var isMarked = checkbox.checked;
			var containerId = elData(checkbox, 'container-id');
			var data = _containers.get(containerId);
			var type = elData(data.element, 'type');
			
			var clipboardObject = DomTraverse.parentByClass(checkbox, 'jsClipboardObject');
			data.markedObjectIds[(isMarked ? 'add' : 'delete')](objectId);
			clipboardObject.classList[(isMarked) ? 'add' : 'remove']('jsMarked');
			
			if (data.markAll !== null) {
				var markedAll = true;
				for (var i = 0, length = data.checkboxes.length; i < length; i++) {
					if (!data.checkboxes[i].checked) {
						markedAll = false;
						
						break;
					}
				}
				
				data.markAll.checked = markedAll;
			}
			
			this._saveState(type, [ objectId ], isMarked);
		},
		
		/**
		 * Saves the state for given item object ids.
		 * 
		 * @param	{string}		type		object type
		 * @param	{array<int>}	        objectIds	item object ids
		 * @param	{boolean}		isMarked	true if marked
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
				var type = elData(listItem, 'type');
				
				EventHandler.fire('com.woltlab.wcf.clipboard', type, {
					data: data,
					listItem: listItem,
					responseData: null
				});
			};
			
			var confirmMessage = (typeof data.internalData.confirmMessage === 'string') ? data.internalData.confirmMessage : '';
			var fireEvent = true;
			
			if (typeof data.parameters === 'object' && data.parameters.actionName && data.parameters.className) {
				if (data.parameters.actionName === 'unmarkAll' || Array.isArray(data.parameters.objectIDs)) {
					if (confirmMessage.length) {
						var template = (typeof data.internalData.template === 'string') ? data.internalData.template : '';
						
						UiConfirmation.show({
							confirm: (function() {
								var formData = {};
								
								if (template.length) {
									var items = elBySelAll('input, select, textarea', UiConfirmation.getContentElement());
									for (var i = 0, length = items.length; i < length; i++) {
										var item = items[i];
										var name = elAttr(item, 'name');
										
										switch (item.nodeName) {
											case 'INPUT':
												if (item.checked) {
													formData[name] = elAttr(item, 'value');
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
				
				UiConfirmation.show({
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
					if (objOwns(data.internalData.parameters, key)) {
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
					var type = elData(listItem, 'type');
					
					EventHandler.fire('com.woltlab.wcf.clipboard', type, {
						data: data,
						listItem: listItem,
						responseData: responseData
					});
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
			var type = elData(event.currentTarget, 'type');
			
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
					if (elData(containerData.element, 'type') === data.returnValues.objectType) {
						var clipboardObjects = elByClass('jsMarked', containerData.element);
						while (clipboardObjects.length) {
							clipboardObjects[0].classList.remove('jsMarked');
						}
						
						if (containerData.markAll !== null) {
							containerData.markAll.checked = false;
						}
						for (var i = 0, length = containerData.checkboxes.length; i < length; i++) {
							containerData.checkboxes[i].checked = false;
						}
						
						UiPageAction.remove('wcfClipboard-' + data.returnValues.objectType);
					}
				}).bind(this));
				
				return;
			}
			
			_itemData = new ObjectMap();
			
			// rebuild markings
			_containers.forEach((function(containerData) {
				var typeName = elData(containerData.element, 'type');
				
				var objectIds = (data.returnValues.markedItems && objOwns(data.returnValues.markedItems, typeName)) ? data.returnValues.markedItems[typeName] : [];
				this._rebuildMarkings(containerData, objectIds);
			}).bind(this));
			
			var keepEditors = [], typeName;
			if (data.returnValues && data.returnValues.items) {
				for (typeName in data.returnValues.items) {
					if (objOwns(data.returnValues.items, typeName)) {
						keepEditors.push(typeName);
					}
				}
			}
			
			// clear editors
			_editors.forEach(function(editor, typeName) {
				if (keepEditors.indexOf(typeName) === -1) {
					UiPageAction.remove('wcfClipboard-' + typeName);
					
					_editorDropdowns.get(typeName).innerHTML = '';
				}
			});
			
			// no items
			if (!data.returnValues || !data.returnValues.items) {
				return;
			}
			
			// rebuild editors
			var created, dropdown, editor, typeData;
			var divider, item, itemData, itemIndex, label, unmarkAll;
			for (typeName in data.returnValues.items) {
				if (!objOwns(data.returnValues.items, typeName)) {
					continue;
				}
				
				typeData = data.returnValues.items[typeName];
				created = false;
				
				editor = _editors.get(typeName);
				dropdown = _editorDropdowns.get(typeName);
				if (editor === undefined) {
					created = true;
					
					editor = elCreate('a');
					editor.className = 'dropdownToggle';
					editor.textContent = typeData.label;
					
					_editors.set(typeName, editor);
					
					dropdown = elCreate('ol');
					dropdown.className = 'dropdownMenu';
					
					_editorDropdowns.set(typeName, dropdown);
				}
				else {
					editor.textContent = typeData.label;
					dropdown.innerHTML = '';
				}
				
				// create editor items
				for (itemIndex in typeData.items) {
					if (!objOwns(typeData.items, itemIndex)) continue;
					
					itemData = typeData.items[itemIndex];
					
					item = elCreate('li');
					label = elCreate('span');
					label.textContent = itemData.label;
					item.appendChild(label);
					dropdown.appendChild(item);
					
					elData(item, 'type', typeName);
					item.addEventListener(WCF_CLICK_EVENT, _callbackItem);
					
					_itemData.set(item, itemData);
				}
				
				divider = elCreate('li');
				divider.classList.add('dropdownDivider');
				dropdown.appendChild(divider);
				
				// add 'unmark all'
				unmarkAll = elCreate('li');
				elData(unmarkAll, 'type', typeName);
				label = elCreate('span');
				label.textContent = Language.get('wcf.clipboard.item.unmarkAll');
				unmarkAll.appendChild(label);
				unmarkAll.addEventListener(WCF_CLICK_EVENT, _callbackUnmarkAll);
				dropdown.appendChild(unmarkAll);
				
				if (keepEditors.indexOf(typeName) !== -1) {
					UiPageAction.add('wcfClipboard-' + typeName, editor);
				}
				
				if (created) {
					editor.parentNode.classList.add('dropdown');
					editor.parentNode.appendChild(dropdown);
					UiSimpleDropdown.init(editor);
				}
			}
		},
		
		/**
		 * Rebuilds the mark state for each item.
		 * 
		 * @param	{object<string, *>}	data		container data
		 * @param	{array<int>}	        objectIds	item object ids
		 */
		_rebuildMarkings: function(data, objectIds) {
			var markAll = true;
			
			for (var i = 0, length = data.checkboxes.length; i < length; i++) {
				var checkbox = data.checkboxes[i];
				var clipboardObject = DomTraverse.parentByClass(checkbox, 'jsClipboardObject');
				
				var isMarked = (objectIds.indexOf(~~elData(checkbox, 'object-id')) !== -1);
				if (!isMarked) markAll = false;
				
				checkbox.checked = isMarked;
				clipboardObject.classList[(isMarked ? 'add' : 'remove')]('jsMarked');
			}
			
			if (data.markAll !== null) {
				data.markAll.checked = markAll;
			}
		}
	};
});

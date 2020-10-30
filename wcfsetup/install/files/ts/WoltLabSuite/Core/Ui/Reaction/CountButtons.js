/**
 * Provides interface elements to use reactions.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Reaction/Handler
 * @since       5.2
 */
define(
	[
		'Ajax',      'Core',          'Dictionary',         'Language',
		'ObjectMap', 'StringUtil',    'Dom/ChangeListener', 'Dom/Util',
		'Ui/Dialog', 'EventHandler'
	],
	function(
		Ajax,        Core,                        Dictionary,           Language,
		ObjectMap,   StringUtil,                  DomChangeListener,    DomUtil,
		UiDialog, EventHandler
	)
	{
		"use strict";
		
		/**
		 * @constructor
		 */
		function CountButtons(objectType, options) { this.init(objectType, options); }
		CountButtons.prototype = {
			/**
			 * Initializes the like handler.
			 *
			 * @param	{string}	objectType	object type
			 * @param	{object}	options		initialization options
			 */
			init: function(objectType, options) {
				if (options.containerSelector === '') {
					throw new Error("[WoltLabSuite/Core/Ui/Reaction/CountButtons] Expected a non-empty string for option 'containerSelector'.");
				}
				
				this._containers = new Dictionary();
				this._objects = new Dictionary();
				this._objectType = objectType;
				
				this._options = Core.extend({
					// selectors
					summaryListSelector: '.reactionSummaryList',
					containerSelector: '',
					isSingleItem: false,
					
					// optional parameters
					parameters: {
						data: {}
					}
				}, options);
				
				this.initContainers(options, objectType);
				
				DomChangeListener.add('WoltLabSuite/Core/Ui/Reaction/CountButtons-' + objectType, this.initContainers.bind(this));
			},
			
			/**
			 * Initialises the containers. 
			 */
			initContainers: function() {
				var element, elements = elBySelAll(this._options.containerSelector), elementData, triggerChange = false, objectId;
				for (var i = 0, length = elements.length; i < length; i++) {
					element = elements[i];
					if (this._containers.has(DomUtil.identify(element))) {
						continue;
					}
					
					objectId = ~~elData(element, 'object-id');
					elementData = {
						reactButton: null,
						summary: null,
						
						objectId: objectId, 
						element: element
					};
					
					this._containers.set(DomUtil.identify(element), elementData);
					this._initReactionCountButtons(element, elementData);

					var objects = [];
					if (this._objects.has(objectId)) {
						objects = this._objects.get(objectId);
					}
					
					objects.push(elementData);
					
					this._objects.set(objectId, objects);
					
					triggerChange = true;
				}
				
				if (triggerChange) {
					DomChangeListener.trigger();
				}
			},
			
			/**
			 * Update the count buttons with the given data. 
			 * 
			 * @param       {int}           objectId
			 * @param       {object}        data
			 */
			updateCountButtons: function(objectId, data) {
				var triggerChange = false;
				this._objects.get(objectId).forEach(function(elementData) {
					var summaryList = elBySel(this._options.summaryListSelector, this._options.isSingleItem ? undefined : elementData.element);
					
					// summary list for the object not found; abort
					if (summaryList === null) return; 
					
					var sortedElements = {}, elements = elBySelAll('.reactCountButton', summaryList);
					for (var i = 0, length = elements.length; i < length; i++) {
						var reactionTypeId = elData(elements[i], 'reaction-type-id');
						if (data.hasOwnProperty(reactionTypeId)) {
							sortedElements[reactionTypeId] = elements[i];
						}
						else {
							// The reaction no longer has any reactions.
							elRemove(elements[i]);
						}
					}
					
					Object.keys(data).forEach(function(key) {
						if (sortedElements[key] !== undefined) {
							var reactionCount = elBySel('.reactionCount', sortedElements[key]);
							reactionCount.innerHTML = StringUtil.shortUnit(data[key]);
						}
						else if (REACTION_TYPES[key] !== undefined) {
							var createdElement = elCreate('span');
							createdElement.className = 'reactCountButton';
							createdElement.innerHTML = REACTION_TYPES[key].renderedIcon;
							elData(createdElement, 'reaction-type-id', key);

							var countSpan = elCreate('span');
							countSpan.className = 'reactionCount';
							countSpan.innerHTML = StringUtil.shortUnit(data[key]);
							createdElement.appendChild(countSpan);
							
							summaryList.appendChild(createdElement);
							
							triggerChange = true;
						}
					}, this);
					
					window[(summaryList.childElementCount > 0 ? 'elShow' : 'elHide')](summaryList);
				}.bind(this));
				
				if (triggerChange) {
					DomChangeListener.trigger();
				}
			},
			
			/**
			 * Initialized the reaction count buttons. 
			 * 
			 * @param       {element}        element
			 * @param       {object}        elementData
			 */
			_initReactionCountButtons: function(element, elementData) {
				var summaryList = elBySel(this._options.summaryListSelector, this._options.isSingleItem ? undefined : element);
				if (summaryList !== null) {
					summaryList.addEventListener('click', this._showReactionOverlay.bind(this, elementData.objectId));
				}
			},
			
			/**
			 * Shows the reaction overly for a specific object. 
			 *
			 * @param {int} objectId
			 * @param {Event} event
			 */
			_showReactionOverlay: function(objectId, event) {
				event.preventDefault();
				
				this._currentObjectId = objectId;
				this._showOverlay();
			},
			
			/**
			 * Shows a specific page of the current opened reaction overlay.
			 */
			_showOverlay: function() {
				this._options.parameters.data.containerID = this._objectType + '-' + this._currentObjectId;
				this._options.parameters.data.objectID = this._currentObjectId;
				this._options.parameters.data.objectType = this._objectType;
				
				Ajax.api(this, {
					parameters: this._options.parameters
				});
			},
			
			_ajaxSuccess: function(data) {
				EventHandler.fire('com.woltlab.wcf.ReactionCountButtons', 'openDialog', data);
				
				UiDialog.open(this, data.returnValues.template);
				UiDialog.setTitle('userReactionOverlay-' + this._objectType, data.returnValues.title);
			},
			
			_ajaxSetup: function() {
				return {
					data: {
						actionName: 'getReactionDetails',
						className: '\\wcf\\data\\reaction\\ReactionAction'
					}
				};
			},
			
			_dialogSetup: function() {
				return {
					id: 'userReactionOverlay-' + this._objectType,
					options: {
						title: ""
					},
					source: null
				};
			}
		};
		
		return CountButtons;
	});

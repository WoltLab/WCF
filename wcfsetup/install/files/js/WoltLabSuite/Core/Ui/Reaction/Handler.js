/**
 * Provides interface elements to use reactions.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Reaction/Handler
 * @since       3.2
 */
define(
	[
		'Ajax',      'Core',                     'Dictionary',         'Language',
		'ObjectMap', 'StringUtil',               'Dom/ChangeListener', 'Dom/Util',
		'Ui/Dialog', 'WoltLabSuite/Core/Ui/User/List', 'User', 'WoltLabSuite/Core/Ui/Reaction/CountButtons', 'Ui/Alignment'
	],
	function(
		Ajax,        Core,              Dictionary,           Language,
		ObjectMap,   StringUtil,        DomChangeListener,    DomUtil,
		UiDialog,    UiUserList,        User,                 CountButtons, 
		UiAlignment
	)
	{
		"use strict";
		
		/**
		 * @constructor
		 */
		function UiReactionHandler(objectType, options) { this.init(objectType, options); }
		UiReactionHandler.prototype = {
			/**
			 * Initializes the reaction handler.
			 *
			 * @param	{string}	objectType	object type
			 * @param	{object}	options		initialization options
			 */
			init: function(objectType, options) {
				if (options.containerSelector === '') {
					throw new Error("[WoltLabSuite/Core/Ui/Reaction/Handler] Expected a non-empty string for option 'containerSelector'.");
				}
				
				this._containers = new Dictionary();
				this._details = new ObjectMap();
				this._objectType = objectType;
				this._cache = new Dictionary();
				
				this._popoverCurrentObjectId = 0;
				
				this._popover = null; 
				
				this._options = Core.extend({
					// permissions
					canReact: false,
					canReactToOwnContent: false,
					
					// selectors
					buttonSelector: '.reactButton', 
					containerSelector: '',
					
					// other stuff
					parameters: {
						data: {}
					}
				}, options);
				
				this.initReactButtons(options, objectType);
				
				this.countButtons = new CountButtons(this._objectType, this._options); 
				
				DomChangeListener.add('WoltLabSuite/Core/Ui/Reaction/Handler-' + objectType, this.initReactButtons.bind(this));
			},
			
			/**
			 * Initializes all applicable react buttons with the given selector.
			 */
			initReactButtons: function() {
				var element, elements = elBySelAll(this._options.containerSelector), elementData, triggerChange = false, objectId;
				for (var i = 0, length = elements.length; i < length; i++) {
					element = elements[i];
					objectId = ~~elData(element, 'object-id');
					if (this._containers.has(objectId)) {
						continue;
					}
					
					elementData = {
						reactButton: null,
						objectId: ~~elData(element, 'object-id'), 
						element: element
					};
					
					this._containers.set(objectId, elementData);
					this._initReactButton(element, elementData);
					
					triggerChange = true;
				}
				
				if (triggerChange) {
					DomChangeListener.trigger();
				}
			},
			
			
			/**
			 * Initializes a specific react button.
			 */
			_initReactButton: function(element, elementData) {
				elementData.reactButton = elBySel(this._options.buttonSelector, element);
				
				if (elementData.reactButton.length === 0) {
					throw new Error("[WoltLabSuite/Core/Ui/Reaction/Handler] Unable to find reactButton.");
				}
				
				elementData.reactButton.addEventListener(WCF_CLICK_EVENT, this._toggleReactPopover.bind(this, elementData.objectId, elementData.reactButton));
			},
			
			/**
			 * Toggle the visibility of the react popover. 
			 * 
			 * @param       {int}           objectId
			 * @param       {Element}       element
			 */
			_toggleReactPopover: function(objectId, element) {
				if (this._popoverCurrentObjectId === 0 || this._popoverCurrentObjectId !== objectId) {
					this._openReactPopover(objectId, element);
				}
				else {
					this._closePopover();
				}
			},
			
			/**
			 * Opens the react popover for a specific react button.
			 * 
			 * @param       {int}	        objectId		objectId of the element
			 * @param       {Element}	element 		container element
			 */
			_openReactPopover: function(objectId, element) {
				UiAlignment.set(this._getPopover(), element, {
					pointer: true,
					horizontal: 'center',
					vertical: 'top'
				});
				
				this._popoverCurrentObjectId = objectId;
				
				this._getPopover().classList.remove('forceHide');
				this._getPopover().classList.add('active');
			},
			
			/**
			 * Returns the react popover element. 
			 * 
			 * @returns {Element}
			 */
			_getPopover: function() {
				if (this._popover == null) {
					this._popover = elCreate('div');
					this._popover.className = 'reactionPopover forceHide';
					
					var _popoverContent = elCreate('div');
					_popoverContent.className = 'reactionPopoverContent';
					
					var popoverContentHTML = elCreate('ul');
					
					for (var key in REACTION_TYPES) {
						if (!REACTION_TYPES.hasOwnProperty(key)) continue;
						
						var reactionType = REACTION_TYPES[key];
						
						var reactionTypeItem = elCreate('li');
						reactionTypeItem.className = 'reactionTypeButton jsTooltip';
						elData(reactionTypeItem, 'reaction-type-id', key);
						elData(reactionTypeItem, 'title', reactionType.title);
						reactionTypeItem.title = reactionType.title;
						
						reactionTypeItem.innerHTML = reactionType.renderedIcon;
						
						reactionTypeItem.addEventListener(WCF_CLICK_EVENT, this._react.bind(this, key));
						
						popoverContentHTML.appendChild(reactionTypeItem);
					}
					
					_popoverContent.appendChild(popoverContentHTML);
					this._popover.appendChild(_popoverContent);
					
					var pointer = elCreate('span');
					pointer.className = 'elementPointer';
					pointer.appendChild(elCreate('span'));
					this._popover.appendChild(pointer);
					
					document.body.appendChild(this._popover);
					
					DomChangeListener.trigger();
				}
				
				return this._popover; 
			},
			
			/**
			 * Closes the react popover. 
			 */
			_closePopover: function() {
				this._popoverCurrentObjectId = 0;
				this._getPopover().classList.remove('active');
			},
			
			/**
			 * React with the given reactionTypeId on an object.
			 * 
			 * @param       {init}          reactionTypeId
			 */
			_react: function(reactionTypeId) {
				this._options.parameters.reactionTypeID = reactionTypeId;
				this._options.parameters.data.containerID = this._currentReactionTypeId;
				this._options.parameters.data.objectID = this._popoverCurrentObjectId;
				this._options.parameters.data.objectType = this._objectType;
				
				Ajax.api(this, {
					parameters: this._options.parameters
				});
				
				this._closePopover();
			},
			
			_ajaxSuccess: function(data) {
				this.countButtons.updateCountButtons(data.returnValues.objectID, data.returnValues.reactions);
				
				// update react button status
				if (data.returnValues.reactionTypeID) {
					this._containers.get(data.returnValues.objectID).reactButton.classList.add('active');
				}
				else {
					this._containers.get(data.returnValues.objectID).reactButton.classList.remove('active');
				}
			},
				
			_ajaxSetup: function() {
				return {
					data: {
						actionName: 'react',
						className: '\\wcf\\data\\reaction\\ReactionAction'
					}
				};
			}
		};
		
		return UiReactionHandler;
	});

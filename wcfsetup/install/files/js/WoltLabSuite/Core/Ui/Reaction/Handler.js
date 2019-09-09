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
		'Ajax',      'Core',                            'Dictionary',           'Language',
		'ObjectMap', 'StringUtil',                      'Dom/ChangeListener',   'Dom/Util',
		'Ui/Dialog', 'WoltLabSuite/Core/Ui/User/List',  'User',                 'WoltLabSuite/Core/Ui/Reaction/CountButtons',
		'Ui/Alignment', 'Ui/CloseOverlay',              'Ui/Screen'
	],
	function(
		Ajax,        Core,              Dictionary,             Language,
		ObjectMap,   StringUtil,        DomChangeListener,      DomUtil,
		UiDialog,    UiUserList,        User,                   CountButtons,
		UiAlignment, UiCloseOverlay,    UiScreen
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
				this._objectType = objectType;
				this._cache = new Dictionary();
				this._objects = new Dictionary();
				
				this._popoverCurrentObjectId = 0;
				
				this._popover = null;
				
				this._options = Core.extend({
					// selectors
					buttonSelector: '.reactButton',
					containerSelector: '',
					isButtonGroupNavigation: false,
					isSingleItem: false,
					
					// other stuff
					parameters: {
						data: {}
					}
				}, options);
				
				this.initReactButtons(options, objectType);
				
				this.countButtons = new CountButtons(this._objectType, this._options);
				
				DomChangeListener.add('WoltLabSuite/Core/Ui/Reaction/Handler-' + objectType, this.initReactButtons.bind(this));
				UiCloseOverlay.add('WoltLabSuite/Core/Ui/Reaction/Handler', this._closePopover.bind(this));
			},
			
			/**
			 * Initializes all applicable react buttons with the given selector.
			 */
			initReactButtons: function() {
				var element, elements = elBySelAll(this._options.containerSelector), elementData, triggerChange = false, objectId;
				for (var i = 0, length = elements.length; i < length; i++) {
					element = elements[i];
					if (this._containers.has(DomUtil.identify(element))) {
						continue;
					}
					
					objectId = ~~elData(element, 'object-id');
					elementData = {
						reactButton: null,
						objectId: objectId,
						element: element
					};
					
					this._containers.set(DomUtil.identify(element), elementData);
					this._initReactButton(element, elementData);

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
			 * Initializes a specific react button.
			 */
			_initReactButton: function(element, elementData) {
				if (this._options.isSingleItem) {
					elementData.reactButton = elBySel(this._options.buttonSelector);
				}
				else {
					elementData.reactButton = elBySel(this._options.buttonSelector, element);
				}
				
				if (elementData.reactButton === null || elementData.reactButton.length === 0) {
					// The element may have no react button. 
					return;
				}
				
				//noinspection JSUnresolvedVariable
				if (Object.keys(REACTION_TYPES).length === 1) {
					//noinspection JSUnresolvedVariable
					var reaction = REACTION_TYPES[Object.keys(REACTION_TYPES)[0]];
					elementData.reactButton.title = reaction.title;
					var textSpan = elBySel('.invisible', elementData.reactButton);
					textSpan.innerText = reaction.title;
				}
				
				if (elementData.reactButton.closest('.messageFooterGroup > .jsMobileNavigation')) {
					UiScreen.on('screen-sm-down', {
						match: this._enableMobileView.bind(this, elementData.reactButton, elementData.objectId),
						unmatch: this._disableMobileView.bind(this, elementData.reactButton, elementData.objectId),
						setup: this._setupMobileView.bind(this, elementData.reactButton, elementData.objectId)
					});
				}
				
				elementData.reactButton.addEventListener(WCF_CLICK_EVENT, this._toggleReactPopover.bind(this, elementData.objectId, elementData.reactButton));
			},
			
			/**
			 * Enables the mobile view for the reaction button.
			 * 
			 * @param       {Element}       element
			 */
			_enableMobileView: function(element) {
				var messageFooterGroup = element.closest('.messageFooterGroup');
				
				elShow(elBySel('.mobileReactButton', messageFooterGroup));
			},
			
			/**
			 * Disables the mobile view for the reaction button.
			 * 
			 * @param       {Element}       element
			 */
			_disableMobileView: function(element) {
				var messageFooterGroup = element.closest('.messageFooterGroup');
				
				elHide(elBySel('.mobileReactButton', messageFooterGroup));
			},
			
			/**
			 * Setup the mobile view for the reaction button.
			 * 
			 * @param       {Element}       element
			 * @param       {int}           objectID
			 */
			_setupMobileView: function(element, objectID) {
				var messageFooterGroup = element.closest('.messageFooterGroup');
				
				var button = elCreate('button');
				button.classList = 'mobileReactButton';
				button.innerHTML = element.innerHTML;
				
				button.addEventListener(WCF_CLICK_EVENT, this._toggleReactPopover.bind(this, objectID, button));
				
				messageFooterGroup.appendChild(button);
			},
			
			_updateReactButton: function(objectID, reactionTypeID) {
				this._objects.get(objectID).forEach(function (elementData) {
					if (elementData.reactButton !== null) {
						if (reactionTypeID) {
							elementData.reactButton.classList.add('active');
							elData(elementData.reactButton, 'reaction-type-id', reactionTypeID);
						}
						else {
							elData(elementData.reactButton, 'reaction-type-id', 0);
							elementData.reactButton.classList.remove('active');
						}
					}
				});
			},
			
			_markReactionAsActive: function() {
				var reactionTypeID = null;
				this._objects.get(this._popoverCurrentObjectId).forEach(function (element) {
					if (element.reactButton !== null) {
						reactionTypeID = ~~elData(element.reactButton, 'reaction-type-id');
					}
				});
				
				if (reactionTypeID === null) {
					throw new Error("Unable to find react button for current popover.");
				}
				
				//  Clear the old active state.
				elBySelAll('.reactionTypeButton.active', this._getPopover(), function(element) {
					element.classList.remove('active');
				});
				
				if (reactionTypeID) {
					var reactionTypeButton = elBySel('.reactionTypeButton[data-reaction-type-id="' + reactionTypeID + '"]', this._getPopover());
					reactionTypeButton.classList.add('active');
					
					if (~~elData(reactionTypeButton, 'is-assignable') === 0) {
						elShow(reactionTypeButton);
					}
				}
			},
			
			/**
			 * Toggle the visibility of the react popover.
			 * 
			 * @param       {int}           objectId
			 * @param       {Element}       element
			 * @param       {?Event}        event
			 */
			_toggleReactPopover: function(objectId, element, event) {
				if (event !== null) {
					event.preventDefault();
					event.stopPropagation();
				}

				//noinspection JSUnresolvedVariable
				if (Object.keys(REACTION_TYPES).length === 1) {
					//noinspection JSUnresolvedVariable
					var reaction = REACTION_TYPES[Object.keys(REACTION_TYPES)[0]];
					this._popoverCurrentObjectId = objectId;
					
					this._react(reaction.reactionTypeID);
				}
				else {
					if (this._popoverCurrentObjectId === 0 || this._popoverCurrentObjectId !== objectId) {
						this._openReactPopover(objectId, element);
					}
					else {
						this._closePopover(objectId, element);
					}
				}
			},
			
			/**
			 * Opens the react popover for a specific react button.
			 * 
			 * @param       {int}	        objectId		objectId of the element
			 * @param       {Element}	element 		container element
			 */
			_openReactPopover: function(objectId, element) {
				if (this._popoverCurrentObjectId !== 0) {
					this._closePopover();
				}
				
				this._popoverCurrentObjectId = objectId;
				this._markReactionAsActive();
				
				UiAlignment.set(this._getPopover(), element, {
					pointer: true,
					horizontal: (this._options.isButtonGroupNavigation) ? 'left' :'center',
					vertical: 'top'
				});
				
				if (this._options.isButtonGroupNavigation) {
					element.closest('nav').style.setProperty('opacity', '1', '');
				}
				
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
					
					var sortedReactionTypes = this._getSortedReactionTypes();
					
					for (var key in sortedReactionTypes) {
						if (!sortedReactionTypes.hasOwnProperty(key)) continue;
						
						var reactionType = sortedReactionTypes[key];
						
						var reactionTypeItem = elCreate('li');
						reactionTypeItem.className = 'reactionTypeButton jsTooltip';
						elData(reactionTypeItem, 'reaction-type-id', reactionType.reactionTypeID);
						elData(reactionTypeItem, 'title', reactionType.title);
						elData(reactionTypeItem, 'is-assignable', ~~reactionType.isAssignable);
						
						reactionTypeItem.title = reactionType.title;
						
						var reactionTypeItemSpan = elCreate('span');
						reactionTypeItemSpan.classList = 'reactionTypeButtonTitle';
						reactionTypeItemSpan.innerHTML = reactionType.title;

						//noinspection JSUnresolvedVariable
						reactionTypeItem.innerHTML = reactionType.renderedIcon;
						
						reactionTypeItem.appendChild(reactionTypeItemSpan);
						
						reactionTypeItem.addEventListener(WCF_CLICK_EVENT, this._react.bind(this, reactionType.reactionTypeID));
						
						if (!reactionType.isAssignable) {
							elHide(reactionTypeItem);
						}
						
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
			 * Sort the reaction types by the showOrder field.
			 * 
			 * @returns     {Array}         the reaction types sorted by showOrder
			 */
			_getSortedReactionTypes: function() {
				var sortedReactionTypes = [];
				
				// convert our reaction type object to an array
				//noinspection JSUnresolvedVariable
				for (var key in REACTION_TYPES) {
					//noinspection JSUnresolvedVariable
					if (REACTION_TYPES.hasOwnProperty(key)) {
						//noinspection JSUnresolvedVariable
						sortedReactionTypes.push(REACTION_TYPES[key]);
					}
				}
				
				// sort the array
				sortedReactionTypes.sort(function (a, b) {
					//noinspection JSUnresolvedVariable
					return a.showOrder - b.showOrder;
				});
				
				return sortedReactionTypes;
			},
			
			/**
			 * Closes the react popover.
			 */
			_closePopover: function() {
				if (this._popoverCurrentObjectId !== 0) {
					this._getPopover().classList.remove('active');
					
					elBySelAll('.reactionTypeButton[data-is-assignable="0"]', this._getPopover(), elHide);
					
					if (this._options.isButtonGroupNavigation) {
						this._objects.get(this._popoverCurrentObjectId).forEach(function (elementData) {
							elementData.reactButton.closest('nav').style.cssText = "";
						});
					}
					
					this._popoverCurrentObjectId = 0;
				}
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
				//noinspection JSUnresolvedVariable
				this.countButtons.updateCountButtons(data.returnValues.objectID, data.returnValues.reactions);
				
				this._updateReactButton(data.returnValues.objectID, data.returnValues.reactionTypeID);
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

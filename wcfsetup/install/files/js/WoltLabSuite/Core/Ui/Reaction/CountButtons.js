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
		'Ajax',      'Core',          'Dictionary',         'Language',
		'ObjectMap', 'StringUtil',    'Dom/ChangeListener', 'Dom/Util',
		'Ui/Dialog'
	],
	function(
		Ajax,        Core,                        Dictionary,           Language,
		ObjectMap,   StringUtil,                  DomChangeListener,    DomUtil,
		UiDialog
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
				this._details = new ObjectMap();
				this._objectType = objectType;
				this._cache = new Dictionary();
				
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
					objectId = ~~elData(element, 'object-id');
					if (this._containers.has(objectId)) {
						continue;
					}
					
					elementData = {
						reactButton: null,
						summary: null,
						
						objectId: ~~elData(element, 'object-id'), 
						element: element
					};
					
					this._containers.set(objectId, elementData);
					this._initReactionCountButtons(element, elementData);
					
					triggerChange = true;
				}
				
				if (triggerChange) {
					DomChangeListener.trigger();
				}
			},
			
			/**
			 * Invalidates the cache for the object with the given objectId. 
			 * 
			 * @param       {int}   objectId
			 */
			invalidateCache: function(objectId) {
				this._cache.delete(objectId);
			},
			
			/**
			 * Update the count buttons with the given data. 
			 * 
			 * @param       {int}           objectId
			 * @param       {object}        data
			 */
			updateCountButtons: function(objectId, data) {
				var summaryList = elBySel(this._options.summaryListSelector, this._containers.get(objectId).element);
				
				var sortedElements = {}, elements = elBySelAll('li', summaryList);
				for (var i = 0, length = elements.length; i < length; i++) {
					if (data[elData(elements[i], 'reaction-type-id')] !== undefined) {
						sortedElements[elData(elements[i], 'reaction-type-id')] = elements[i];
					}
					else {
						// reaction has no longer reactions
						elRemove(elements[i]);
					}
				}
				
				
				var triggerChange = false; 
				Object.keys(data).forEach(function(key) {
					if (sortedElements[key] !== undefined) {
						var reactionCount = elBySel('.reactionCount', sortedElements[key]);
						reactionCount.innerHTML = StringUtil.shortUnit(data[key]);
					}
					else if (REACTION_TYPES[key] !== undefined) {
						// create element 
						var createdElement = elCreate('li');
						createdElement.className = 'reactCountButton';
						elData(createdElement, 'reaction-type-id', key);
						
						var countSpan = elCreate('span');
						countSpan.className = 'reactionCount';
						countSpan.innerHTML = StringUtil.shortUnit(data[key]);
						createdElement.appendChild(countSpan);
						
						createdElement.innerHTML = createdElement.innerHTML + REACTION_TYPES[key].renderedIcon;
						
						summaryList.appendChild(createdElement);
						
						this._initReactionCountButton(createdElement, objectId);
						
						triggerChange = true; 
					}
				}, this);
				
				if (triggerChange) {
					DomChangeListener.trigger();
				}
				
				this.invalidateCache(objectId);
			},
			
			/**
			 * Initialized the reaction count buttons. 
			 * 
			 * @param       {element}        element
			 * @param       {object}        elementData
			 */
			_initReactionCountButtons: function(element, elementData) {
				if (this._options.isSingleItem) {
					var summaryList = elBySel(this._options.summaryListSelector);
				}
				else {
					var summaryList = elBySel(this._options.summaryListSelector, element);
				}
				
				if (summaryList !== null) {
					var elements = elBySelAll('li', summaryList);
					for (var i = 0, length = elements.length; i < length; i++) {
						this._initReactionCountButton(elements[i], elementData.objectId);
					}
				}
			},
			
			/**
			 * Initialized a specific reaction count button for an object.
			 *
			 * @param       {element}        element
			 * @param       {int}            objectId
			 */
			_initReactionCountButton: function(element, objectId) {
				element.addEventListener(WCF_CLICK_EVENT, this._showReactionOverlay.bind(this, elData(element, 'reaction-type-id'), objectId));
			},
			
			/**
			 * Shows the reaction overly for a specific object and reaction type. 
			 *
			 * @param       {int}        reactionTypeId
			 * @param       {int}        objectId
			 */
			_showReactionOverlay: function(reactionTypeId, objectId) {
				this._currentObjectId = objectId;
				this._currentPageNo = 1;
				this._currentReactionTypeId = reactionTypeId;
				this._showPage();
			},
			
			/**
			 * Shows a specific page of the current opened reaction overlay. 
			 *
			 * @param       {int}        pageNo
			 */
			_showPage: function(pageNo) {
				if (pageNo !== undefined) {
					this._currentPageNo = pageNo;
				}
				
				if (this._cache.has(this._currentObjectId) && this._cache.get(this._currentObjectId).has(this._currentReactionTypeId)) {
					// validate pageNo
					if (this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).get('pageCount') !== 0 && (this._currentPageNo < 1 || this._currentPageNo > this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).get('pageCount'))) {
						throw new RangeError("pageNo must be between 1 and " + this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).get('pageCount') + " (" + this._currentPageNo + " given).");
					}
				}
				else {
					// init objectId cache
					if (!this._cache.has(this._currentObjectId)) {
						this._cache.set(this._currentObjectId, new Dictionary());
					}
					
					// init reaction type id cache for objectId
					this._cache.get(this._currentObjectId).set(this._currentReactionTypeId, new Dictionary());
				}
				
				if (this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).has(this._currentPageNo)) {
					var dialog = UiDialog.open(this, this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).get(this._currentPageNo));
					UiDialog.setTitle('userReactionOverlay-' + this._objectType, this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).get('title'));
					
					if (this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).get('pageCount') > 1) {
						var element = elBySel('.jsPagination', dialog.content);
						if (element !== null) {
							new UiPagination(element, {
								activePage: this._currentPageNo,
								maxPage: this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).get('pageCount'),
								callbackSwitch: this._showPage.bind(this)
							});
						}
					}
					
					// init tab menu
					var tabMenu = elBySel('.tabMenu ul', dialog.content);
					var elements = elBySelAll('li', tabMenu);
					for (var i = 0, length = elements.length; i < length; i++) {
						elements[i].addEventListener(WCF_CLICK_EVENT, this._showReactionOverlay.bind(this, elData(elements[i], 'reaction-type-id'), this._currentObjectId));
					}
				}
				else {
					this._options.parameters.pageNo = this._currentPageNo;
					this._options.parameters.reactionTypeID = this._currentReactionTypeId;
					this._options.parameters.data.containerID = this._currentReactionTypeId;
					this._options.parameters.data.objectID = this._currentObjectId;
					this._options.parameters.data.objectType = this._objectType;
					
					Ajax.api(this, {
						parameters: this._options.parameters
					});
				}
			},
			
			_ajaxSuccess: function(data) {
				if (data.returnValues.pageCount !== undefined) {
					this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).set('pageCount', ~~data.returnValues.pageCount);
				}
				
				this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).set(this._currentPageNo, data.returnValues.template);
				this._cache.get(this._currentObjectId).get(this._currentReactionTypeId).set('title', data.returnValues.title);
				this._showPage();
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

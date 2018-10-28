/**
 * Handles the reputation count buttons.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Reaction/Handler
 * @since       3.2
 */
define(
	['Ajax', 'Dictionary', 'Dom/ChangeListener', 'Ui/Dialog'],
	function(Ajax, Dictionary, DomChangeListener, UiDialog)
	{
		"use strict";
		
		/**
		 * @constructor
		 */
		function ReputationButtons(objectType, options) { this.init(objectType, options); }
		ReputationButtons.prototype = {
			/**
			 * Initializes the reputation buttons.
			 *
			 * @param	{string}	objectType	object type
			 */
			init: function(objectType) {
				if (objectType === '') {
					throw new Error("[WoltLabSuite/Core/Ui/Reaction/ReputationButtons] Expected a non-empty string for objectType 'containerSelector'.");
				}
				
				this._containers = new Dictionary();
				this._objectType = objectType;
				
				this.initContainers();
				
				DomChangeListener.add('WoltLabSuite/Core/Ui/Reaction/ReputationButtons-' + objectType, this.initContainers.bind(this));
			},
			
			/**
			 * Initialises the containers.
			 */
			initContainers: function() {
				var element, elements = elBySelAll(".reputationCounter[data-object-type='" + this._objectType + "']"), elementData, triggerChange = false, objectId;
				for (var i = 0, length = elements.length; i < length; i++) {
					element = elements[i];
					objectId = ~~elData(element, 'object-id');
					if (this._containers.has(objectId)) {
						continue;
					}
					
					elementData = {
						objectId: ~~elData(element, 'object-id'),
						element: element
					};
					
					this._containers.set(objectId, elementData);
					
					this._initReputationCountButton(elementData.element, elementData.objectId);
					
					triggerChange = true;
				}
				
				if (triggerChange) {
					DomChangeListener.trigger();
				}
			},
			
			/**
			 * Sets the count of a specific reputation button. 
			 * 
			 * @param       {int}           objectId
			 * @param       {int}           count
			 */
			setReputationCount: function(objectId, count) {
				if (this._containers.has(objectId)) {
					this._containers.get(objectId).element.classList.remove("neutral", "negative", "positive");
					
					if (count > 0) {
						this._containers.get(objectId).element.innerHTML = "+" + count;
						this._containers.get(objectId).element.classList.add("positive");
					}
					else if (count < 0) {
						this._containers.get(objectId).element.innerHTML = count;
						this._containers.get(objectId).element.classList.add("negative");
					}
					else if (count === 0) {
						this._containers.get(objectId).element.innerHTML = "±" + count;
						this._containers.get(objectId).element.classList.add("neutral");
					}
					else {
						this._containers.get(objectId).element.innerHTML = "";
					}
				} 
			},
			
			/**
			 * Initialized a specific reaction count button for an object.
			 *
			 * @param       {element}        element
			 * @param       {int}            objectId
			 */
			_initReputationCountButton: function(element, objectId) {
				element.addEventListener(WCF_CLICK_EVENT, this._showReactionOverlay.bind(this, objectId));
			},
			
			/**
			 * Shows the reaction overly for a specific object.
			 *
			 * @param       {int}        objectId
			 */
			_showReactionOverlay: function(objectId) {
				this._currentObjectId = objectId;
				this._showOverlay();
			},
			
			/**
			 * Shows the overlay for the reactions.
			 */
			_showOverlay: function() {
				Ajax.api(this, {
					parameters: {
						data: {
							containerID: this._objectType + '-' + this._currentObjectId,
							objectID: this._currentObjectId,
							objectType: this._objectType
						}
					}
				});
			},
			
			_ajaxSuccess: function(data) {
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
		
		return ReputationButtons;
	});

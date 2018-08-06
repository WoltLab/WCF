/**
 * Provides interface elements to display and review likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Like/Handler
 * @deprecated  3.2 use ReactionHandler instead 
 */
define(
	[
		'Ajax',      'Core',                     'Dictionary',         'Language',
		'ObjectMap', 'StringUtil',               'Dom/ChangeListener', 'Dom/Util',
		'Ui/Dialog', 'WoltLabSuite/Core/Ui/User/List', 'User',         'WoltLabSuite/Core/Ui/Reaction/Handler'
	],
	function(
		Ajax,        Core,                        Dictionary,           Language,
		ObjectMap,   StringUtil,                  DomChangeListener,    DomUtil,
		UiDialog,    UiUserList,                  User,                 UiReactionHandler
	)
{
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiLikeHandler(objectType, options) { this.init(objectType, options); }
	UiLikeHandler.prototype = {
		/**
		 * Initializes the like handler.
		 * 
		 * @param	{string}	objectType	object type
		 * @param	{object}	options		initialization options
		 */
		init: function(objectType, options) {
			if (options.containerSelector === '') {
				throw new Error("[WoltLabSuite/Core/Ui/Like/Handler] Expected a non-empty string for option 'containerSelector'.");
			}
			
			this._containers = new ObjectMap();
			this._details = new ObjectMap();
			this._objectType = objectType;
			this._options = Core.extend({
				// settings
				badgeClassNames: '',
				isSingleItem: false,
				markListItemAsActive: false,
				renderAsButton: true,
				summaryPrepend: true,
				summaryUseIcon: true,
				
				// permissions
				canDislike: false,
				canLike: false,
				canLikeOwnContent: false,
				canViewSummary: false,
				
				// selectors
				badgeContainerSelector: '.messageHeader .messageStatus',
				buttonAppendToSelector: '.messageFooter .messageFooterButtons',
				buttonBeforeSelector: '',
				containerSelector: '',
				summarySelector: '.messageFooterGroup'
			}, options);
			
			this.initContainers(options, objectType);
			
			DomChangeListener.add('WoltLabSuite/Core/Ui/Like/Handler-' + objectType, this.initContainers.bind(this));
			
			new UiReactionHandler(this._objectType, {
				containerSelector: this._options.containerSelector,
				summaryListSelector: '.reactionSummaryList'
			});
		},
		
		/**
		 * Initializes all applicable containers.
		 */
		initContainers: function() {
			var element, elements = elBySelAll(this._options.containerSelector), elementData, triggerChange = false;
			for (var i = 0, length = elements.length; i < length; i++) {
				element = elements[i];
				if (this._containers.has(element)) {
					continue;
				}
				
				elementData = {
					badge: null,
					dislikeButton: null,
					likeButton: null,
					summary: null,
					
					dislikes: ~~elData(element, 'like-dislikes'),
					liked: ~~elData(element, 'like-liked'),
					likes: ~~elData(element, 'like-likes'),
					objectId: ~~elData(element, 'object-id'),
					users: JSON.parse(elData(element, 'like-users'))
				};
				
				this._containers.set(element, elementData);
				this._buildWidget(element, elementData);
				
				triggerChange = true;
			}
			
			if (triggerChange) {
				DomChangeListener.trigger();
			}
		},
		
		/**
		 * Creates the interface elements.
		 * 
		 * @param	{Element}	element		container element
		 * @param	{object}	elementData	like data
		 */
		_buildWidget: function(element, elementData) {
			// build reaction summary list
			var summaryList, listItem, badgeContainer, isSummaryPosition = true;
			badgeContainer = (this._options.isSingleItem) ? elBySel(this._options.summarySelector) : elBySel(this._options.summarySelector, element);
			if (badgeContainer === null) {
				badgeContainer = (this._options.isSingleItem) ? elBySel(this._options.badgeContainerSelector) : elBySel(this._options.badgeContainerSelector, element);
				isSummaryPosition = false;
			}
			
			if (badgeContainer !== null) {
				summaryList = elCreate('ul');
				summaryList.classList.add('reactionSummaryList');
				if (isSummaryPosition) {
					summaryList.classList.add('likesSummary');
				}
				else {
					summaryList.classList.add('reactionSummaryListTiny');
				}
				
				for (var key in elementData.users) {
					if (key === "reactionTypeID") continue;
					if (!REACTION_TYPES.hasOwnProperty(key)) continue;
					
					// create element 
					var createdElement = elCreate('li');
					createdElement.className = 'reactCountButton';
					createdElement.innerHTML = REACTION_TYPES[key].renderedIcon +' ';
					elData(createdElement, 'reaction-type-id', key);
					
					var countSpan = elCreate('span');
					countSpan.className = 'reactionCount';
					countSpan.innerHTML = StringUtil.shortUnit(elementData.users[key]);
					createdElement.appendChild(countSpan);
					
					summaryList.appendChild(createdElement);
					
				}
				
				if (isSummaryPosition) {
					if (this._options.summaryPrepend) {
						DomUtil.prepend(summaryList, badgeContainer);
					}
					else {
						badgeContainer.appendChild(summaryList);
					}
				}
				else {
					if (badgeContainer.nodeName === 'OL' || badgeContainer.nodeName === 'UL') {
						listItem = elCreate('li');
						listItem.appendChild(summaryList);
						badgeContainer.appendChild(listItem);
					}
					else {
						badgeContainer.appendChild(summaryList);
					}
				}
				
				elementData.badge = summaryList;
			}
			
			// build reaction button
			if (this._options.canLike && (User.userId != elData(element, 'user-id') || this._options.canLikeOwnContent)) {
				var appendTo = (this._options.buttonAppendToSelector) ? ((this._options.isSingleItem) ? elBySel(this._options.buttonAppendToSelector) : elBySel(this._options.buttonAppendToSelector, element)) : null;
				var insertPosition = (this._options.buttonBeforeSelector) ? ((this._options.isSingleItem) ? elBySel(this._options.buttonBeforeSelector) : elBySel(this._options.buttonBeforeSelector, element)) : null;
				if (insertPosition === null && appendTo === null) {
					throw new Error("Unable to find insert location for like/dislike buttons.");
				}
				else {
					elementData.likeButton = this._createButton(element, elementData.users.reactionTypeID, insertPosition, appendTo);
				}
			}
		},
		
		/**
		 * Creates a reaction button.
		 * 
		 * @param	{Element}	element		        container element
		 * @param	{int}	        reactionTypeID		the reactionTypeID of the current state
		 * @param	{Element?}	insertBefore	        insert button before given element
		 * @param       {Element?}      appendTo                append button to given element
		 * @return	{Element}	button element 
		 */
		_createButton: function(element, reactionTypeID, insertBefore, appendTo) {
			var title = Language.get('wcf.reactions.react');
			
			var listItem = elCreate('li');
			listItem.className = 'wcfReactButton';
			
			if (insertBefore) {
				var jsMobileNavigation = insertBefore.parentElement.contains('jsMobileNavigation');
			}
			else {
				var jsMobileNavigation = appendTo.classList.contains('jsMobileNavigation');
			}
			
			var button = elCreate('a');
			button.className = 'jsTooltip reactButton' + (this._options.renderAsButton ? ' button' + (jsMobileNavigation ? ' ignoreMobileNavigation' : '') : '');
			button.href = '#';
			button.title = title;
			
			var icon = elCreate('img');
			icon.className = 'reactionType';
			
			if (reactionTypeID === undefined || reactionTypeID == 0) {
				icon.src = WCF_PATH+'images/reaction/reactionIcon.svg';
				elData(icon, 'reaction-type-id', 0);
			}
			else {
				icon.src = REACTION_TYPES[reactionTypeID].iconPath;
				elData(icon, 'reaction-type-id', reactionTypeID);
				
				button.classList.add("active");
			}
			
			button.appendChild(icon);
			
			var invisibleText = elCreate("span");
			invisibleText.className = "invisible";
			invisibleText.innerHTML = title;
			
			button.appendChild(document.createTextNode(" "));
			button.appendChild(invisibleText);
			
			listItem.appendChild(button);
			
			if (insertBefore) {
				insertBefore.parentNode.insertBefore(listItem, insertBefore);
			}
			else {
				appendTo.appendChild(listItem);
			}
			
			return button;
		}
	};
	
	return UiLikeHandler;
});

/**
 * Provides interface elements to display and review likes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Like/Handler
 */
define(
	[
		'Ajax',      'Core',                     'Dictionary',         'Language',
		'ObjectMap', 'StringUtil',               'Dom/ChangeListener', 'Dom/Util',
		'Ui/Dialog', 'WoltLab/WCF/Ui/User/List'
	],
	function(
		Ajax,        Core,                        Dictionary,           Language,
		ObjectMap,   StringUtil,                  DomChangeListener,    DomUtil,
		UiDialog,    UiUserList
	)
{
	"use strict";
	
	var _isBusy = false;
	
	/**
	 * @constructor
	 */
	function UiLikeHandler(objectType, options) { this.init(objectType, options); }
	UiLikeHandler.prototype = {
		/**
		 * Initializes the like handler.
		 * 
		 * @param	{string}	objectType	object type
		 * @param	{object}	options		initilization options
		 */
		init: function(objectType, options) {
			if (options.containerSelector === '') {
				throw new Error("[WoltLab/WCF/Ui/Like/Handler] Expected a non-emtpy string for option 'containerSelector'.");
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
			
			DomChangeListener.add('WoltLab/WCF/Ui/Like/Handler-' + objectType, this.initContainers.bind(this))
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
			// build summary
			if (this._options.canViewSummary) {
				var summary, summaryContent, summaryIcon;
				var summaryContainer = (this._options.isSingleItem) ? elBySel(this._options.summarySelector) : elBySel(this._options.summarySelector, element);
				if (summaryContainer !== null) {
					summary = elCreate('div');
					summary.className = 'likesSummary';
					
					if (this._options.summaryUseIcon) {
						summaryIcon = elCreate('span');
						summaryIcon.className = 'icon icon16 fa-thumbs-o-up';
						summary.appendChild(summaryIcon);
					}
					
					summaryContent = elCreate('span');
					summaryContent.className = 'likesSummaryContent';
					summaryContent.addEventListener(WCF_CLICK_EVENT, this._showSummary.bind(this, element));
					summary.appendChild(summaryContent);
					
					if (this._options.summaryPrepend) {
						DomUtil.prepend(summary, summaryContainer);
					}
					else {
						summaryContainer.appendChild(summary);
					}
					
					elementData.summary = summaryContent;
					
					this._updateSummary(element);
				}
			}
			
			// cumulative likes
			var badge, listItem;
			var badgeContainer = (this._options.isSingleItem) ? elBySel(this._options.badgeContainerSelector) : elBySel(this._options.badgeContainerSelector, element);
			if (badgeContainer !== null) {
				badge = elCreate('a');
				badge.href = '#';
				badge.className = 'wcfLikeCounter jsTooltip' + (this._options.badgeClassNames ? ' ' + this._options.badgeClassNames : '');
				badge.addEventListener(WCF_CLICK_EVENT, this._showSummary.bind(this, element));
				
				if (badgeContainer.nodeName === 'OL' || badgeContainer.nodeName === 'UL') {
					listItem = elCreate('li');
					listItem.appendChild(badge);
					badgeContainer.appendChild(listItem);
				}
				else {
					badgeContainer.appendChild(badge);
				}
				
				elementData.badge = badge;
				
				this._updateBadge(element);
			}
			
			if (this._options.canLike && (WCF.User.userID != elData(element, 'user-id') || this._options.canLikeOwnContent)) {
				var appendTo = (this._options.buttonAppendToSelector) ? ((this._options.isSingleItem) ? elBySel(this._options.buttonAppendToSelector) : elBySel(this._options.buttonAppendToSelector, element)) : null;
				var insertPosition = (this._options.buttonBeforeSelector) ? ((this._options.isSingleItem) ? elBySel(this._options.buttonBeforeSelector) : elBySel(this._options.buttonBeforeSelector, element)) : null;
				if (insertPosition === null && appendTo === null) {
					throw new Error("Unable to find insert location for like/dislike buttons.");
				}
				else {
					// like button
					elementData.likeButton = this._createButton(element, true, insertPosition, appendTo);
					
					// dislike button
					if (this._options.canDislike) {
						elementData.dislikeButton = this._createButton(element, false, insertPosition, appendTo);
					}
					
					this._updateActiveState(element);
				}
			}
		},
		
		/**
		 * Creates a like or dislike button.
		 * 
		 * @param	{Element}	element		container element
		 * @param	{boolean}	isLike		false if this is a dislike button
		 * @param	{Element?}	insertBefore	insert button before given element
		 * @param       {Element?}      appendTo        append button to given element
		 * @return	{Element}	button element 
		 */
		_createButton: function(element, isLike, insertBefore, appendTo) {
			var title = Language.get('wcf.like.button.' + (isLike ? 'like' : 'dislike'));
			
			var listItem = elCreate('li');
			listItem.className = 'wcf' + (isLike ? 'Like' : 'Dislike') + 'Button';
			
			var button = elCreate('a');
			button.className = 'jsTooltip' + (this._options.renderAsButton ? ' button' : '');
			button.href = '#';
			button.title = title;
			button.innerHTML = '<span class="icon icon16 fa-thumbs-o-' + (isLike ? 'up' : 'down') + '"></span> <span class="invisible">' + title + '</span>';
			button.addEventListener(WCF_CLICK_EVENT, this._like.bind(this, element));
			elData(button, 'type', (isLike ? 'like' : 'dislike'));
			
			listItem.appendChild(button);
			
			if (insertBefore) {
				insertBefore.parentNode.insertBefore(listItem, insertBefore);
			}
			else {
				appendTo.appendChild(listItem);
			}
			
			return button;
		},
		
		/**
		 * Shows the summary of likes/dislikes.
		 * 
		 * @param	{Element}	element		container element
		 * @param	{object}	event		event object
		 */
		_showSummary: function(element, event) {
			event.preventDefault();
			
			if (!this._details.has(element)) {
				this._details.set(element, new UiUserList({
					className: 'wcf\\data\\like\\LikeAction',
					dialogTitle: Language.get('wcf.like.details'),
					parameters: {
						data: {
							containerID: DomUtil.identify(element),
							objectID: this._containers.get(element).objectId,
							objectType: this._objectType
						}
					}
				}));
			}
			
			this._details.get(element).open();
		},
		
		/**
		 * Updates the display of cumulative likes.
		 * 
		 * @param	{Element}	element		container element
		 */
		_updateBadge: function(element) {
			var data = this._containers.get(element);
			
			if (data.likes === 0 && data.dislikes === 0) {
				elHide(data.badge);
			}
			else {
				elShow(data.badge);
				
				// update like counter
				var cumulativeLikes = data.likes - data.dislikes;
				var content = '<span class="icon icon16 fa-thumbs-o-' + (cumulativeLikes < 0 ? 'down' : 'up' ) + '"></span><span class="wcfLikeValue">';
				if (cumulativeLikes > 0) {
					content += '+' + StringUtil.addThousandsSeparator(cumulativeLikes);
					data.badge.classList.add('likeCounterLiked');
				}
				else if (cumulativeLikes < 0) {
					// U+2212 = minus sign
					content += '\u2212' + StringUtil.addThousandsSeparator(Math.abs(cumulativeLikes));
					data.badge.classList.add('likeCounterDisliked');
				}
				else {
					// U+00B1 = plus-minus sign
					content += '\u00B1' + '0';
				}
				
				data.badge.innerHTML = content + '</span>';
				data.badge.setAttribute('data-tooltip', Language.get('wcf.like.tooltip', {
					dislikes: data.dislikes,
					likes: data.likes
				}));
			}
		},
		
		/**
		 * Updates the like summary.
		 * 
		 * @param	{Element}	element		container element
		 */
		_updateSummary: function(element) {
			var data = this._containers.get(element);
			
			if (data.likes) {
				elShow(data.summary.parentNode);
				
				var usernames = [];
				var keys = Object.keys(data.users);
				for (var i = 0, length = keys.length; i < length; i++) {
					usernames.push(data.users[keys[i]]);
				}
				
				var others = data.likes - usernames.length;
				data.summary.innerHTML = Language.get('wcf.like.summary', { users: usernames, others: others });
			}
			else {
				elHide(data.summary.parentNode);
			}
		},
		
		/**
		 * Updates the active like/dislike button state.
		 * 
		 * @param	{Element}	element		container element
		 */
		_updateActiveState: function(element) {
			var data = this._containers.get(element);
			
			var dislikeTarget = (this._options.markListItemAsActive) ? data.dislikeButton.parentNode : data.dislikeButton;
			var likeTarget = (this._options.markListItemAsActive) ? data.likeButton.parentNode : data.likeButton;
			
			if (data.dislikeButton !== null) dislikeTarget.classList.remove('active');
			likeTarget.classList.remove('active');
			
			if (data.liked === 1) {
				likeTarget.classList.add('active');
			}
			else if (data.liked === -1 && data.dislikeButton !== null) {
				dislikeTarget.classList.add('active');
			}
		},
		
		/**
		 * Likes or dislikes an element.
		 * 
		 * @param	{Element}	element		container element
		 * @param	{object}	event		event object
		 */
		_like: function(element, event) {
			event.preventDefault();
			
			if (_isBusy) {
				return;
			}
			
			_isBusy = true;
			
			Ajax.api(this, {
				actionName: elData(event.currentTarget, 'type'),
				parameters: {
					data: {
						containerID: DomUtil.identify(element),
						objectID: this._containers.get(element).objectId,
						objectType: this._objectType
					}
				}
			});
		},
		
		_ajaxSuccess: function(data) {
			var element = elById(data.returnValues.containerID);
			var elementData = this._containers.get(element);
			if (elementData === undefined) {
				return;
			}
			
			elementData.dislikes = ~~data.returnValues.dislikes;
			elementData.likes = ~~data.returnValues.likes;
			
			var users = data.returnValues.users;
			elementData.users = [];
			var keys = Object.keys(users);
			for (var i = 0, length = keys.length; i < length; i++) {
				elementData.users.push(StringUtil.escapeHTML(users[keys[i]].username));
			}
			
			if (data.returnValues.isLiked == 1) elementData.liked = 1;
			else if (data.returnValues.isDisliked == 1) elementData.liked = -1;
			else elementData.liked = 0;
			
			// update label
			this._updateBadge(element);
			
			// update summary
			if (this._options.canViewSummary) this._updateSummary(element);
			
			// mark button as active
			this._updateActiveState(element);
			
			// invalidate cache for like details
			this._details['delete'](element);
			
			_isBusy = false;
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					className: 'wcf\\data\\like\\LikeAction'
				}
			};
		}
	};
	
	return UiLikeHandler;
});

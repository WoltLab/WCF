define(['Ajax', 'Core', 'Dictionary', 'Language', 'ObjectMap', 'StringUtil', 'Dom/ChangeListener', 'Dom/Util', 'Ui/Dialog'], function(Ajax, Core, Dictionary, Language, ObjectMap, StringUtil, DomChangeListener, DomUtil, UiDialog) {
	"use strict";
	
	var _isBusy = false;
	
	function UiLikeHandler(objectType, options) { this.init(objectType, options); };
	UiLikeHandler.prototype = {
		init: function(objectType, options) {
			if (options.containerSelector === '') {
				throw new Error("[WoltLab/WCF/Ui/Like/Handler] Expected a non-emtpy string for option 'containerSelector'.");
			}
			
			this._containers = new ObjectMap();
			this._details = new ObjectMap();
			this._objectType = objectType;
			this._options = Core.extend({
				isSingleItem: false,
				
				// permissions
				canDislike: false,
				canLike: false,
				canLikeOwnContent: false,
				canViewSummary: false,
				
				// selectors
				badgeContainerSelector: '.messageHeader .messageHeadline > p',
				buttonBeforeSelector: '.messageFooterButtons > .toTopLink',
				containerSelector: '',
				summarySelector: '.messageFooterNotes'
			}, options);
			
			this.initContainers(options, objectType);
		},
		
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
					
					dislikes: ~~elAttr(element, 'data-like-dislikes'),
					liked: ~~elAttr(element, 'data-like-liked'),
					likes: ~~elAttr(element, 'data-like-likes'),
					objectId: ~~elAttr(element, 'data-object-id'),
					users: JSON.parse(elAttr(element, 'data-like-users'))
				};
				
				this._containers.set(element, elementData);
				this._buildWidget(element, elementData);
				
				triggerChange = true;
			}
			
			if (triggerChange) {
				DomChangeListener.trigger();
			}
		},
		
		_buildWidget: function(element, elementData) {
			// build summary
			if (this._options.canViewSummary) {
				var summary, summaryContainer = elBySel(this._options.summarySelector, element), summaryContent;
				if (summaryContainer !== null) {
					summary = elCreate('p');
					summary.className = 'likesSummary';
					
					summaryContent = elCreate('span');
					summaryContent.addEventListener('click', this._showSummary.bind(this, element));
					summary.appendChild(summaryContent);
					
					summaryContainer.appendChild(summary);
					elementData.summary = summaryContent;
					
					this._updateSummary(element);
				}
			}
			
			// cumulative likes
			var badge, badgeContainer = elBySel(this._options.badgeContainerSelector, element);
			if (badgeContainer !== null) {
				badge = elCreate('a');
				badge.href = '#';
				badge.className = 'wcfLikeCounter jsTooltip';
				badge.addEventListener('click', this._showSummary.bind(this, element));
				
				badgeContainer.appendChild(badge);
				elementData.badge = badge;
				
				this._updateBadge(element);
			}
			
			var insertPosition, userId = elAttr(element, 'data-user-id');
			if (this._options.canLikeOwnContent || WCF.User.userID === userId) {
				insertPosition = elBySel(this._options.buttonBeforeSelector, element);
				if (insertPosition !== null) {
					// like button
					elementData.likeButton = this._createButton(element, insertPosition, true);
					
					// dislike button
					if (this._options.canDislike) {
						elementData.dislikeButton = this._createButton(element, insertPosition, false);
					}
					
					this._updateActiveState(element);
				}
			}
		},
		
		_createButton: function(element, insertBefore, isLike) {
			var title = Language.get('wcf.like.button.' + (isLike ? 'like' : 'dislike'));
			
			var listItem = elCreate('li');
			listItem.className = 'wcf' + (isLike ? 'Like' : 'Dislike') + 'Button';
			
			var button = elCreate('a');
			button.className = 'button jsTooltip';
			button.href = '#';
			button.title = title;
			button.innerHTML = '<span class="icon icon16 fa-thumbs-o-' + (isLike ? 'up' : 'down') + '" /> <span class="invisible">' + title + '</span>';
			button.addEventListener('click', this._like.bind(this, element));
			button.setAttribute('data-type', (isLike ? 'like' : 'dislike'));
			
			listItem.appendChild(button);
			insertBefore.parentNode.insertBefore(listItem, insertBefore);
			
			return button;
		},
		
		_showSummary: function(element, event) {
			event.preventDefault();
			
			if (!this._details.has(element)) {
				// @TODO
				this._details.set(element, new WCF.User.List('wcf\\data\\like\\LikeAction', Language.get('wcf.like.details'), {
					data: {
						containerID: DomUtil.identify(element),
						objectID: this._containers.get(element).objectId,
						objectType: this._objectType
					}
				}));
			}
			
			this._details.get(element).open();
		},
		
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
				}
				else if (cumulativeLikes < 0) {
					// U+2212 = minus sign
					content += '\u2212' + StringUtil.addThousandsSeparator(Math.abs(cumulativeLikes));
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
		
		_updateActiveState: function(element) {
			var data = this._containers.get(element);
			
			if (data.dislikeButton !== null) data.dislikeButton.classList.remove('active');
			data.likeButton.classList.remove('active');
			
			if (data.liked === 1) {
				data.likeButton.classList.add('active');
			}
			else if (data.liked === -1 && data.dislikeButton !== null) {
				data.dislikeButton.classList.add('active');
			}
		},
		
		_like: function(element, event) {
			event.preventDefault();
			
			if (_isBusy) {
				return;
			}
			
			_isBusy = true;
			
			Ajax.api(this, {
				actionName: elAttr(event.currentTarget, 'data-type'),
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
			
			if (data.isLiked == 1) elementData.liked = 1;
			else if (data.isDisliked) elementData.liked = -1;
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

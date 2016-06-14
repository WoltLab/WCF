"use strict";

/**
 * Like support for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * 
 * @deprecated	3.0 - please use `WoltLab/WCF/Ui/Like/Handler` instead
 */
WCF.Like = Class.extend({
	/**
	 * true, if users can like their own content
	 * @var	boolean
	 */
	_allowForOwnContent: false,
	
	/**
	 * user can like
	 * @var	boolean
	 */
	_canLike: false,
	
	/**
	 * list of containers
	 * @var	object
	 */
	_containers: { },
	
	/**
	 * container meta data
	 * @var	object
	 */
	_containerData: { },
	
	/**
	 * enables the dislike option
	 */
	_enableDislikes: true,
	
	/**
	 * prevents like/dislike until the server responded
	 * @var	boolean
	 */
	_isBusy: false,
	
	/**
	 * cached grouped user lists for like details
	 * @var	object
	 */
	_likeDetails: { },
	
	/**
	 * proxy object
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * shows the detailed summary of users who liked the object
	 * @var	boolean
	 */
	_showSummary: true,
	
	/**
	 * Initializes like support.
	 * 
	 * @param	boolean		canLike
	 * @param	boolean		enableDislikes
	 * @param	boolean		showSummary
	 * @param	boolean		allowForOwnContent
	 */
	init: function(canLike, enableDislikes, showSummary, allowForOwnContent) {
		this._canLike = canLike;
		this._enableDislikes = enableDislikes;
		this._isBusy = false;
		this._likeDetails = { };
		this._showSummary = showSummary;
		this._allowForOwnContent = allowForOwnContent;
		
		var $containers = this._getContainers();
		this._initContainers($containers);
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		// bind dom node inserted listener
		var $date = new Date();
		var $identifier = $date.toString().hashCode + $date.getUTCMilliseconds();
		WCF.DOMNodeInsertedHandler.addCallback('WCF.Like' + $identifier, $.proxy(this._domNodeInserted, this));
	},
	
	/**
	 * Initialize containers once new nodes are inserted.
	 */
	_domNodeInserted: function() {
		var $containers = this._getContainers();
		this._initContainers($containers);
		
	},
	
	/**
	 * Initializes like containers.
	 * 
	 * @param	object		containers
	 */
	_initContainers: function(containers) {
		var $createdWidgets = false;
		containers.each($.proxy(function(index, container) {
			// set container
			var $container = $(container);
			var $containerID = $container.wcfIdentify();
			
			if (!this._containers[$containerID]) {
				this._containers[$containerID] = $container;
				
				// set container data
				this._containerData[$containerID] = {
					'likeButton': null,
					'badge': null,
					'dislikeButton': null,
					'likes': $container.data('like-likes'),
					'dislikes': $container.data('like-dislikes'),
					'objectType': $container.data('objectType'),
					'objectID': this._getObjectID($containerID),
					'users': eval($container.data('like-users')),
					'liked': $container.data('like-liked')
				};
				
				// create UI
				this._createWidget($containerID);
				
				$createdWidgets = true;
			}
		}, this));
		
		if ($createdWidgets) {
			new WCF.PeriodicalExecuter(function(pe) {
				pe.stop();
				
				WCF.DOMNodeInsertedHandler.execute();
			}, 250);
		}
	},
	
	/**
	 * Returns a list of available object containers.
	 * 
	 * @return	jQuery
	 */
	_getContainers: function() { },
	
	/**
	 * Returns widget container for target object container.
	 * 
	 * @param	string		containerID
	 * @return	jQuery
	 */
	_getWidgetContainer: function(containerID) { },
	
	/**
	 * Returns object id for targer object container.
	 * 
	 * @param	string		containerID
	 * @return	integer
	 */
	_getObjectID: function(containerID) { },
	
	/**
	 * Adds the like widget.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		widget
	 */
	_addWidget: function(containerID, widget) {
		var $widgetContainer = this._getWidgetContainer(containerID);
		
		widget.appendTo($widgetContainer);
	},
	
	/**
	 * Builds the like widget.
	 * 
	 * @param	integer		containerID
	 * @param	jQuery		likeButton
	 * @param	jQuery		dislikeButton
	 * @param	jQuery		badge
	 * @param	jQuery		summary
	 */
	_buildWidget: function(containerID, likeButton, dislikeButton, badge, summary) {
		var $widget = $('<aside class="likesWidget"><ul></ul></aside>');
		if (this._canLike) {
			likeButton.appendTo($widget.find('ul'));
			dislikeButton.appendTo($widget.find('ul'));
		}
		badge.appendTo($widget);
		
		this._addWidget(containerID, $widget);
	},
	
	/**
	 * Creates the like widget.
	 * 
	 * @param	integer		containerID
	 */
	_createWidget: function(containerID) {
		var $likeButton = $('<li class="wcfLikeButton"><a href="#" title="'+WCF.Language.get('wcf.like.button.like')+'" class="jsTooltip"><span class="icon icon16 fa-thumbs-o-up" /> <span class="invisible">'+WCF.Language.get('wcf.like.button.like')+'</span></a></li>');
		var $dislikeButton = $('<li class="wcfDislikeButton"><a href="#" title="'+WCF.Language.get('wcf.like.button.dislike')+'" class="jsTooltip"><span class="icon icon16 fa-thumbs-o-down" /> <span class="invisible">'+WCF.Language.get('wcf.like.button.dislike')+'</span></a></li>');
		if (!this._enableDislikes) $dislikeButton.hide();
		
		if (!this._allowForOwnContent && (WCF.User.userID == this._containers[containerID].data('userID'))) {
			$likeButton = $('');
			$dislikeButton = $('');
		}
		
		var $badge = $('<a class="badge jsTooltip likesBadge" />').data('containerID', containerID).click($.proxy(this._showLikeDetails, this));
		
		var $summary = null;
		if (this._showSummary) {
			$summary = $('<p class="likesSummary"><span class="pointer" /></p>');
			$summary.children('span').data('containerID', containerID).click($.proxy(this._showLikeDetails, this));
		}
		this._buildWidget(containerID, $likeButton, $dislikeButton, $badge, $summary);
		
		this._containerData[containerID].likeButton = $likeButton;
		this._containerData[containerID].dislikeButton = $dislikeButton;
		this._containerData[containerID].badge = $badge;
		this._containerData[containerID].summary = $summary;
		
		$likeButton.data('containerID', containerID).data('type', 'like').click($.proxy(this._click, this));
		$dislikeButton.data('containerID', containerID).data('type', 'dislike').click($.proxy(this._click, this));
		this._setActiveState($likeButton, $dislikeButton, this._containerData[containerID].liked);
		this._updateBadge(containerID);
		if (this._showSummary) this._updateSummary(containerID);
	},
	
	/**
	 * Displays like details for an object.
	 * 
	 * @param	object		event
	 * @param	string		containerID
	 */
	_showLikeDetails: function(event, containerID) {
		var $containerID = (event === null) ? containerID : $(event.currentTarget).data('containerID');
		
		if (this._likeDetails[$containerID] === undefined) {
			this._likeDetails[$containerID] = new WCF.User.List('wcf\\data\\like\\LikeAction', WCF.Language.get('wcf.like.details'), {
				data: {
					containerID: $containerID,
					objectID: this._containerData[$containerID].objectID,
					objectType: this._containerData[$containerID].objectType
				}
			});
		}
		
		this._likeDetails[$containerID].open();
	},
	
	/**
	 * Handles likes and dislikes.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		event.preventDefault();
		var $button = $(event.currentTarget);
		if ($button === null) {
			console.debug("[WCF.Like] Unable to find target button, aborting.");
			return;
		}
		
		this._sendRequest($button.data('containerID'), $button.data('type'));
	},
	
	/**
	 * Sends request through proxy.
	 * 
	 * @param	integer		containerID
	 * @param	string		type
	 */
	_sendRequest: function(containerID, type) {
		// ignore retards spamming clicks on the buttons
		if (this._isBusy) {
			return;
		}
		
		this._isBusy = true;
		
		this._proxy.setOption('data', {
			actionName: type,
			className: 'wcf\\data\\like\\LikeAction',
			parameters: {
				data: {
					containerID: containerID,
					objectID: this._containerData[containerID].objectID,
					objectType: this._containerData[containerID].objectType
				}
			}
		});
		
		this._proxy.sendRequest();
	},
	
	/**
	 * Updates likeable object.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	object		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $containerID = data.returnValues.containerID;
		
		if (!this._containers[$containerID]) {
			return;
		}
		
		switch (data.actionName) {
			case 'dislike':
			case 'like':
				// update container data
				this._containerData[$containerID].likes = parseInt(data.returnValues.likes);
				this._containerData[$containerID].dislikes = parseInt(data.returnValues.dislikes);
				this._containerData[$containerID].users = data.returnValues.users;
				$.each(this._containerData[$containerID].users, function(userID, userData) {
					userData.username = WCF.String.escapeHTML(userData.username);
				});
				
				// update label
				this._updateBadge($containerID);
				// update summary
				if (this._showSummary) this._updateSummary($containerID);
				
				// mark button as active
				var $likeButton = this._containerData[$containerID].likeButton;
				var $dislikeButton = this._containerData[$containerID].dislikeButton;
				var $likeStatus = 0;
				if (data.returnValues.isLiked) $likeStatus = 1;
				else if (data.returnValues.isDisliked) $likeStatus = -1;
				this._setActiveState($likeButton, $dislikeButton, $likeStatus);
				
				// invalidate cache for like details
				if (this._likeDetails[$containerID] !== undefined) {
					delete this._likeDetails[$containerID];
				}
				
				this._isBusy = false;
			break;
		}
	},
	
	_updateBadge: function(containerID) {
		if (!this._containerData[containerID].likes && !this._containerData[containerID].dislikes) {
			this._containerData[containerID].badge.hide();
		}
		else {
			this._containerData[containerID].badge.show();
			
			// update like counter
			var $cumulativeLikes = this._containerData[containerID].likes - this._containerData[containerID].dislikes;
			var $badge = this._containerData[containerID].badge;
			$badge.removeClass('green red');
			if ($cumulativeLikes > 0) {
				$badge.text('+' + WCF.String.formatNumeric($cumulativeLikes));
				$badge.addClass('green');
			}
			else if ($cumulativeLikes < 0) {
				$badge.text(WCF.String.formatNumeric($cumulativeLikes));
				$badge.addClass('red');
			}
			else {
				$badge.text('\u00B10');
			}
			
			// update tooltip
			var $likes = this._containerData[containerID].likes;
			var $dislikes = this._containerData[containerID].dislikes;
			$badge.data('tooltip', WCF.Language.get('wcf.like.tooltip', { likes: $likes, dislikes: $dislikes }));
		}
	},
	
	_updateSummary: function(containerID) {
		if (!this._containerData[containerID].likes) {
			this._containerData[containerID].summary.hide();
		}
		else {
			this._containerData[containerID].summary.show();
			
			var $users = this._containerData[containerID].users;
			var $userArray = [];
			for (var $userID in $users) $userArray.push($users[$userID].username);
			var $others = this._containerData[containerID].likes - $userArray.length;
			
			this._containerData[containerID].summary.children('span').html(WCF.Language.get('wcf.like.summary', { users: $userArray, others: $others }));
		}
	},
	
	/**
	 * Sets button active state.
	 * 
	 * @param	jquery		likeButton
	 * @param	jquery		dislikeButton
	 * @param	integer		likeStatus
	 */
	_setActiveState: function(likeButton, dislikeButton, likeStatus) {
		likeButton.removeClass('active');
		dislikeButton.removeClass('active');
		
		if (likeStatus == 1) {
			likeButton.addClass('active');
		}
		else if (likeStatus == -1) {
			dislikeButton.addClass('active');
		}
	}
});

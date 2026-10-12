"use strict";

/**
 * User-related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * UserProfile namespace
 */
WCF.User.Profile = {};

/**
 * Shows the activity point list for users.
 * 
 * @deprecated 6.2 Use `WoltLabSuite/Core/Component/User/ActivityPointList` instead.
 */
WCF.User.Profile.ActivityPointList = {
	/**
	 * list of cached templates
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * initialization state
	 * @var	boolean
	 */
	_didInit: false,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the WCF.User.Profile.ActivityPointList class.
	 */
	init: function() {
		if (this._didInit) {
			return;
		}
		
		this._cache = { };
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._init();
		
		this._didInit = true;
	},
	
	/**
	 * Initializes display for activity points.
	 */
	_init: function() {
		require(["WoltLabSuite/Core/Helper/Selector"], ({ wheneverFirstSeen }) => {
			wheneverFirstSeen(".activityPointsDisplay", (element) => {
				element.classList.remove("activityPointsDisplay");
				element.addEventListener("click", (event) => this._click(event));
			});
		});
	},
	
	/**
	 * Shows or loads the activity point for selected user.
	 * 
	 * @param	object		event
	 */
	_click: function(event) {
		event.preventDefault();
		var $userID = $(event.currentTarget).data('userID');
		
		if (this._cache[$userID] === undefined) {
			this._proxy.setOption('data', {
				actionName: 'getDetailedActivityPointList',
				className: 'wcf\\data\\user\\UserProfileAction',
				objectIDs: [ $userID ]
			});
			this._proxy.sendRequest();
		}
		else {
			this._show($userID);
		}
	},
	
	/**
	 * Displays activity points for given user.
	 * 
	 * @param	integer		userID
	 */
	_show: function(userID) {
		require(["WoltLabSuite/Core/Component/Dialog"], ({ dialogFactory }) => {
			dialogFactory()
				.fromHtml(this._cache[userID])
				.withoutControls()
				.show(WCF.Language.get("wcf.user.activityPoint"));
		});
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		this._cache[data.returnValues.userID] = data.returnValues.template;
		this._show(data.returnValues.userID);
	}
};

/**
 * Provides methods to load tab menu content upon request.
 * 
 * @deprecated 6.2 Handled by `WoltLabSuite/Core/Controller/User/Profile`.
 */
WCF.User.Profile.TabMenu = Class.extend({
	/**
	 * list of containers
	 * @var	object
	 */
	_hasContent: { },
	
	/**
	 * profile content
	 * @var	jQuery
	 */
	_profileContent: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * target user id
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes the tab menu loader.
	 * 
	 * @param	integer		userID
	 */
	init: function(userID) {
		this._profileContent = $('#profileContent');
		this._userID = userID;
		
		var $activeMenuItem = this._profileContent.data('active');
		var $enableProxy = false;
		
		// fetch content state
		this._profileContent.find('div.tabMenuContent').each($.proxy(function(index, container) {
			var $containerID = $(container).wcfIdentify();
			
			if ($activeMenuItem === $containerID) {
				this._hasContent[$containerID] = true;
			}
			else {
				this._hasContent[$containerID] = false;
				$enableProxy = true;
			}
		}, this));
		
		// enable loader if at least one container is empty
		if ($enableProxy) {
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			this._profileContent.on('wcftabsbeforeactivate', $.proxy(this._loadContent, this));
			
			// check which tab is selected
			this._profileContent.find('> nav.tabMenu > ul > li').each($.proxy(function(index, listItem) {
				var $listItem = $(listItem);
				if ($listItem.hasClass('ui-state-active')) {
					if (index) {
						this._loadContent(null, {
							newPanel: $('#' + $listItem.attr('aria-controls'))
						});
					}
					
					return false;
				}
			}, this));
		}
		
		$('.userProfileUser .contentDescription a[href$="#likes"]').click((function (event) {
			event.preventDefault();
			
			require(['Ui/TabMenu'], function (UiTabMenu) {
				UiTabMenu.getTabMenu('profileContent').select('likes');
			})
		}).bind(this))
	},
	
	/**
	 * Prepares to load content once tabs are being switched.
	 * 
	 * @param	object		event
	 * @param	object		ui
	 */
	_loadContent: function(event, ui) {
		var $panel = $(ui.newPanel);
		var $containerID = $panel.attr('id');
		
		if (!this._hasContent[$containerID]) {
			this._proxy.setOption('data', {
				actionName: 'getContent',
				className: 'wcf\\data\\user\\profile\\menu\\item\\UserProfileMenuItemAction',
				parameters: {
					data: {
						containerID: $containerID,
						menuItem: $panel.data('menuItem'),
						userID: this._userID
					}
				}
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Shows previously requested content.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		var $containerID = data.returnValues.containerID;
		this._hasContent[$containerID] = true;
		
		// insert content, uses non jQuery because DomUtil.insertHtml() moves <script> elements
		// to the bottom of the element by default which is exactly what is required here
		require(['Dom/ChangeListener', 'Dom/Util'], function(DomChangeListener, DomUtil) {
			DomUtil.insertHtml(data.returnValues.template, elById($containerID), 'append');
			
			DomChangeListener.trigger();
		});
	}
});

/**
 * Notification system for WCF.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
WCF.Notification = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles the notification list.
	 * @deprecated 6.2 Use `WoltLabSuite/Core/Controller/User/Notification/List` instead.
	 */
	WCF.Notification.List = Class.extend({
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * Initializes the WCF.Notification.List object.
		 */
		init: function () {
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			// handle 'mark all as confirmed' buttons
			$('.jsMarkAllAsConfirmed').click(function () {
				WCF.System.Confirmation.show(WCF.Language.get('wcf.user.notification.markAllAsConfirmed.confirmMessage'), function (action) {
					if (action === 'confirm') {
						new WCF.Action.Proxy({
							autoSend: true,
							data: {
								actionName: 'markAllAsConfirmed',
								className: 'wcf\\data\\user\\notification\\UserNotificationAction'
							},
							success: function () {
								window.location.reload();
							}
						});
					}
				});
			});
			
			// handle regular items
			this._convertList();
		},
		
		/**
		 * Converts the notification item list to be in sync with the notification dropdown.
		 */
		_convertList: function () {
			$('.userNotificationItemList > .notificationItem').each((function (index, item) {
				var $item = $(item);
				
				if (!$item.data('isRead')) {
					$item.find('a:not(.userLink)').prop('href', $item.data('link'));
					
					var $markAsConfirmed = $(`<button type="button" class="notificationItemMarkAsConfirmed jsTooltip" title="${WCF.Language.get('wcf.global.button.markAsRead')}">
						<fa-icon size="24" name="check"></fa-icon>
					</button>`).appendTo($item);
					$markAsConfirmed.click($.proxy(this._markAsConfirmed, this));
				}
			}).bind(this));
			
			WCF.DOMNodeInsertedHandler.execute();
		},
		
		/**
		 * Marks a single notification as confirmed.
		 *
		 * @param        object                event
		 */
		_markAsConfirmed: function (event) {
			event.preventDefault();
			
			var $notificationID = $(event.currentTarget).parents('.notificationItem:eq(0)').data('objectID');
			
			this._proxy.setOption('data', {
				actionName: 'markAsConfirmed',
				className: 'wcf\\data\\user\\notification\\UserNotificationAction',
				objectIDs: [$notificationID]
			});
			this._proxy.sendRequest();
			
			return false;
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			var $item = $('.userNotificationItemList > .notificationItem[data-object-id=' + data.returnValues.markAsRead + ']');
			
			$item.data('isRead', true);
			$item.find('.newContentBadge').remove();
			$item.find('.notificationItemMarkAsConfirmed').remove();
			$item.removeClass('notificationUnconfirmed');
		}
	});
	
	/**
	 * Signature preview.
	 *
	 * @see        WCF.Message.Preview
	 * @deprecated 6.2 Use `WoltLabSuite/Core/Component/User/Signature/Preview` instead.
	 */
	WCF.User.SignaturePreview = WCF.Message.Preview.extend({
		/**
		 * @see        WCF.Message.Preview._handleResponse()
		 */
		_handleResponse: function (data) {
			// get preview container
			var $preview = $('#previewContainer');
			if (!$preview.length) {
				$preview = $('<section class="section" id="previewContainer"><h2 class="sectionTitle">' + WCF.Language.get('wcf.global.preview') + '</h2><div class="htmlContent messageSignatureConstraints"></div></section>').insertBefore($('#signatureContainer')).wcfFadeIn();
			}
			
			$preview.children('div').first().html(data.returnValues.message);
		}
	});
}
else {
	WCF.Notification.List = Class.extend({
		_proxy: {},
		init: function() {},
		_convertList: function() {},
		_markAsConfirmed: function() {},
		_success: function() {}
	});
	
	WCF.User.SignaturePreview = WCF.Message.Preview.extend({
		_handleResponse: function() {},
		_className: "",
		_messageFieldID: "",
		_messageField: {},
		_proxy: {},
		_previewButton: {},
		_previewButtonLabel: "",
		init: function() {},
		_click: function() {},
		_getParameters: function() {},
		_getMessage: function() {},
		_success: function() {},
		_failure: function() {}
	});
}

/**
 * Loads recent activity events once the user scrolls to the very bottom.
 * 
 * @param	integer		userID
 * @deprecated 6.1 use `WoltLabSuite/Core/Components/User/RecentActivity/Loader` instead
 */
WCF.User.RecentActivityLoader = Class.extend({
	/**
	 * container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * true if list should be filtered by followed users
	 * @var	boolean
	 */
	_filteredByFollowedUsers: false,
	
	/**
	 * button to load next events
	 * @var	jQuery
	 */
	_loadButton: null,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * user id
	 * @var	integer
	 */
	_userID: 0,
	
	/**
	 * Initializes a new RecentActivityLoader object.
	 * 
	 * @param	integer		userID
	 * @param	boolean		filteredByFollowedUsers
	 */
	init: function(userID, filteredByFollowedUsers) {
		this._container = $('#recentActivities');
		this._filteredByFollowedUsers = (filteredByFollowedUsers === true);
		this._userID = userID;
		
		if (this._userID !== null && !this._userID) {
			console.debug("[WCF.User.RecentActivityLoader] Invalid parameter 'userID' given.");
			return;
		}
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		if (this._container.children('li').length) {
			this._loadButton = $('<li class="showMore"><button type="button" class="button small">' + WCF.Language.get('wcf.user.recentActivity.more') + '</button></li>').appendTo(this._container);
			this._loadButton = this._loadButton.children('button').click($.proxy(this._click, this));
		}
		else {
			$('<li class="showMore"><small>' + WCF.Language.get('wcf.user.recentActivity.noMoreEntries') + '</small></li>').appendTo(this._container);
		}
		
		if (WCF.User.userID) {
			$('.jsRecentActivitySwitchContext .button').click($.proxy(this._switchContext, this));
		}
	},
	
	/**
	 * Loads next activity events.
	 */
	_click: function() {
		this._loadButton.enable();
		
		var $parameters = {
			lastEventID: this._container.data('lastEventID'),
			lastEventTime: this._container.data('lastEventTime')
		};
		if (this._userID) {
			$parameters.userID = this._userID;
		}
		else if (this._filteredByFollowedUsers) {
			$parameters.filteredByFollowedUsers = 1;
		}
		
		this._proxy.setOption('data', {
			actionName: 'load',
			className: 'wcf\\data\\user\\activity\\event\\UserActivityEventAction',
			parameters: $parameters
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Switches recent activity context.
	 */
	_switchContext: function(event) {
		event.preventDefault();
		
		if (!$(event.currentTarget).hasClass('active')) {
			new WCF.Action.Proxy({
				autoSend: true,
				data: {
					actionName: 'switchContext',
					className: 'wcf\\data\\user\\activity\\event\\UserActivityEventAction'
				},
				success: function() {
					window.location.hash = '#dashboardBoxRecentActivity';
					window.location.reload();
				}
			});
		}
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.template) {
			$(data.returnValues.template).insertBefore(this._loadButton.parent());
			
			this._container.data('lastEventTime', data.returnValues.lastEventTime);
			this._container.data('lastEventID', data.returnValues.lastEventID);
			this._loadButton.enable();
		}
		else {
			$('<small>' + WCF.Language.get('wcf.user.recentActivity.noMoreEntries') + '</small>').appendTo(this._loadButton.parent());
			this._loadButton.remove();
		}
	}
});

/**
 * Initializes WCF.User.Action namespace.
 */
WCF.User.Action = {};

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles user follow and unfollow links.
	 * 
	 * @deprecated 6.1 use `WoltLabSuite/Core/Component/User/Follow` instead
	 */
	WCF.User.Action.Follow = Class.extend({
		/**
		 * list with elements containing follow and unfollow buttons
		 * @var        array
		 */
		_containerList: null,
		
		/**
		 * CSS selector for follow buttons
		 * @var        string
		 */
		_followButtonSelector: '.jsFollowButton',
		
		/**
		 * id of the user that is currently being followed/unfollowed
		 * @var        integer
		 */
		_userID: 0,
		
		/**
		 * Initializes new WCF.User.Action.Follow object.
		 *
		 * @param        array                containerList
		 * @param        string                followButtonSelector
		 */
		init: function (containerList, followButtonSelector) {
			if (!containerList.length) {
				return;
			}
			this._containerList = containerList;
			
			if (followButtonSelector) {
				this._followButtonSelector = followButtonSelector;
			}
			
			// initialize proxy
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			// bind event listeners
			this._containerList.each($.proxy(function (index, container) {
				$(container).find(this._followButtonSelector).click($.proxy(this._click, this));
			}, this));
		},
		
		/**
		 * Handles a click on a follow or unfollow button.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			event.preventDefault();
			var link = $(event.target);
			if (!link.is('a')) {
				link = link.closest('a');
			}
			this._userID = link.data('objectID');
			
			this._proxy.setOption('data', {
				'actionName': link.data('following') ? 'unfollow' : 'follow',
				'className': 'wcf\\data\\user\\follow\\UserFollowAction',
				'parameters': {
					data: {
						userID: this._userID
					}
				}
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Handles the successful (un)following of a user.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			this._containerList.each($.proxy(function (index, container) {
				var button = $(container).find(this._followButtonSelector).get(0);
				
				if (button && $(button).data('objectID') == this._userID) {
					button = $(button);
					
					// toogle icon title
					if (data.returnValues.following) {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.unfollow'));
						button[0].querySelector("fa-icon").setIcon("circle-minus");
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.unfollow'));
					}
					else {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.follow'));
						button[0].querySelector("fa-icon").setIcon("circle-plus");
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.follow'));
					}
					
					button.data('following', data.returnValues.following);
					
					return false;
				}
			}, this));
			
			var $notification = new WCF.System.Notification();
			$notification.show();
		}
	});
	
	/**
	 * Handles user ignore and unignore links.
	 * 
	 * @deprecated 5.4 Use a FormBuilderDialog for wcf\data\user\ignore\UserIgnoreAction::getDialog()
	 */
	WCF.User.Action.Ignore = Class.extend({
		/**
		 * list with elements containing ignore and unignore buttons
		 * @var        array
		 */
		_containerList: null,
		
		/**
		 * CSS selector for ignore buttons
		 * @var        string
		 */
		_ignoreButtonSelector: '.jsIgnoreButton',
		
		/**
		 * id of the user that is currently being ignored/unignored
		 * @var        integer
		 */
		_userID: 0,
		
		/**
		 * Initializes new WCF.User.Action.Ignore object.
		 *
		 * @param        array                containerList
		 * @param        string                ignoreButtonSelector
		 */
		init: function (containerList, ignoreButtonSelector) {
			if (!containerList.length) {
				return;
			}
			this._containerList = containerList;
			
			if (ignoreButtonSelector) {
				this._ignoreButtonSelector = ignoreButtonSelector;
			}
			
			// initialize proxy
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			// bind event listeners
			this._containerList.each($.proxy(function (index, container) {
				$(container).find(this._ignoreButtonSelector).click($.proxy(this._click, this));
			}, this));
		},
		
		/**
		 * Handles a click on a ignore or unignore button.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			event.preventDefault();
			var link = $(event.target);
			if (!link.is('a')) {
				link = link.closest('a');
			}
			this._userID = link.data('objectID');
			
			this._proxy.setOption('data', {
				'actionName': link.data('ignored') ? 'unignore' : 'ignore',
				'className': 'wcf\\data\\user\\ignore\\UserIgnoreAction',
				'parameters': {
					data: {
						userID: this._userID
					}
				}
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Handles the successful (un)ignoring of a user.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			this._containerList.each($.proxy(function (index, container) {
				var button = $(container).find(this._ignoreButtonSelector).get(0);
				
				if (button && $(button).data('objectID') == this._userID) {
					button = $(button);
					
					// toogle icon title
					if (data.returnValues.isIgnoredUser) {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.unignore'));
						button[0].querySelector("fa-icon").setIcon("circle");
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.unignore'));
					}
					else {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.ignore'));
						button[0].querySelector("fa-icon").setIcon("ban");
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.ignore'));
					}
					
					button.data('ignored', data.returnValues.isIgnoredUser);
					
					return false;
				}
			}, this));
			
			var $notification = new WCF.System.Notification();
			$notification.show();
		}
	});
}
else {
	WCF.User.Action.Follow = Class.extend({
		_containerList: {},
		_followButtonSelector: "",
		_userID: 0,
		init: function() {},
		_click: function() {},
		_success: function() {}
	});
	
	/**
	 * @deprecated
	 */
	WCF.User.Action.Ignore = Class.extend({
		_containerList: {},
		_ignoreButtonSelector: "",
		_userID: 0,
		init: function() {},
		_click: function() {},
		_success: function() {}
	});
}

/**
 * Generic implementation for grouped user lists.
 * 
 * @param	string		className
 * @param	string		dialogTitle
 * @param	object		additionalParameters
 * @deprecated 6.0 use `WoltLabSuite/Core/Component/User/List` instead
 */
WCF.User.List = Class.extend({
	/**
	 * list of additional parameters
	 * @var	object
	 */
	_additionalParameters: { },
	
	/**
	 * list of cached pages
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * action class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
	/**
	 * dialog title
	 * @var	string
	 */
	_dialogTitle: '',
	
	/**
	 * page count
	 * @var	integer
	 */
	_pageCount: 0,
	
	/**
	 * current page no
	 * @var	integer
	 */
	_pageNo: 1,
	
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes a new grouped user list.
	 * 
	 * @param	string		className
	 * @param	string		dialogTitle
	 * @param	object		additionalParameters
	 */
	init: function(className, dialogTitle, additionalParameters) {
		this._additionalParameters = additionalParameters || { };
		this._cache = { };
		this._className = className;
		this._dialog = null;
		this._dialogTitle = dialogTitle;
		this._pageCount = 0;
		this._pageNo = 1;
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
	},
	
	/**
	 * Opens the dialog overlay.
	 */
	open: function() {
		this._pageNo = 1;
		this._showPage();
	},
	
	/**
	 * Displays the specified page.
	 * 
	 * @param	object		event
	 * @param	object		data
	 */
	_showPage: function(event, data) {
		if (data && data.activePage) {
			this._pageNo = data.activePage;
		}
		
		if (this._pageCount != 0 && (this._pageNo < 1 || this._pageNo > this._pageCount)) {
			console.debug("[WCF.User.List] Cannot access page " + this._pageNo + " of " + this._pageCount);
			return;
		}
		
		if (this._cache[this._pageNo]) {
			var $dialogCreated = false;
			if (this._dialog === null) {
				this._dialog = $('#userList' + this._className.hashCode());
				if (this._dialog.length === 0) {
					this._dialog = $('<div id="userList' + this._className.hashCode() + '" />').hide().appendTo(document.body);
					$dialogCreated = true;
				}
			}
			
			// remove current view
			this._dialog.empty();
			
			// insert HTML
			this._dialog.html(this._cache[this._pageNo]);
			
			// add pagination
			if (this._pageCount > 1) {
				this._dialog.find('.jsPagination').wcfPages({
					activePage: this._pageNo,
					maxPage: this._pageCount
				}).on('wcfpagesswitched', $.proxy(this._showPage, this));
			}
			else {
				this._dialog.find('.jsPagination').hide();
			}
			
			// show dialog
			if ($dialogCreated) {
				this._dialog.wcfDialog({
					title: this._dialogTitle
				});
			}
			else {
				this._dialog.wcfDialog('option', 'title', this._dialogTitle);
				this._dialog.wcfDialog('open').wcfDialog('render');
			}
			
			WCF.DOMNodeInsertedHandler.execute();
		}
		else {
			this._additionalParameters.pageNo = this._pageNo;
			
			// load template via AJAX
			this._proxy.setOption('data', {
				actionName: 'getGroupedUserList',
				className: this._className,
				interfaceName: 'wcf\\data\\IGroupedUserListAction',
				parameters: this._additionalParameters
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Handles successful AJAX requests.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.pageCount) {
			this._pageCount = data.returnValues.pageCount;
		}
		
		this._cache[this._pageNo] = data.returnValues.template;
		this._showPage();
	}
});

/**
 * Namespace for object watch functions.
 */
WCF.User.ObjectWatch = {};

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles subscribe/unsubscribe links.
	 * 
	 * @deprecated	since 6.0, use `WoltLabSuite/Core/Ui/User/ObjectWatch` instead.
	 */
	WCF.User.ObjectWatch.Subscribe = Class.extend({
		/**
		 * CSS selector for subscribe buttons
		 * @var        string
		 */
		_buttonSelector: '.jsSubscribeButton',
		
		/**
		 * list of buttons
		 * @var        object
		 */
		_buttons: {},
		
		/**
		 * dialog overlay
		 * @var        object
		 */
		_dialog: null,
		
		/**
		 * system notification
		 * @var        WCF.System.Notification
		 */
		_notification: null,
		
		/**
		 * reload page on unsubscribe
		 * @var        boolean
		 */
		_reloadOnUnsubscribe: false,
		
		/**
		 * WCF.User.ObjectWatch.Subscribe object.
		 *
		 * @param        boolean                reloadOnUnsubscribe
		 */
		init: function (reloadOnUnsubscribe) {
			this._buttons = {};
			this._notification = null;
			this._reloadOnUnsubscribe = (reloadOnUnsubscribe === true);
			
			// initialize proxy
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			// bind event listeners
			$(this._buttonSelector).each($.proxy(function (index, button) {
				var $button = $(button);
				$button.addClass('pointer');
				var $objectType = $button.data('objectType');
				var $objectID = $button.data('objectID');
				
				if (this._buttons[$objectType] === undefined) {
					this._buttons[$objectType] = {};
				}
				
				this._buttons[$objectType][$objectID] = $button.click($.proxy(this._click, this));
			}, this));
			
			WCF.System.Event.addListener('com.woltlab.wcf.objectWatch', 'update', $.proxy(this._updateSubscriptionStatus, this));
		},
		
		/**
		 * Handles a click on a subscribe button.
		 *
		 * @param        object                event
		 */
		_click: function (event) {
			event.preventDefault();
			var $button = $(event.currentTarget);
			
			this._proxy.setOption('data', {
				actionName: 'manageSubscription',
				className: 'wcf\\data\\user\\object\\watch\\UserObjectWatchAction',
				parameters: {
					objectID: $button.data('objectID'),
					objectType: $button.data('objectType')
				}
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			if (data.actionName === 'manageSubscription') {
				if (this._dialog === null) {
					this._dialog = $('<div>' + data.returnValues.template + '</div>').hide().appendTo(document.body);
					this._dialog.wcfDialog({
						title: WCF.Language.get('wcf.user.objectWatch.manageSubscription')
					});
				}
				else {
					this._dialog.html(data.returnValues.template);
					this._dialog.wcfDialog('open');
				}
				
				// bind event listener
				this._dialog.find('.formSubmit > .jsButtonSave').data('objectID', data.returnValues.objectID).data('objectType', data.returnValues.objectType).click($.proxy(this._save, this));
				var $enableNotification = this._dialog.find('input[name=enableNotification]').disable();
				
				// toggle subscription
				this._dialog.find('input[name=subscribe]').change(function (event) {
					var $input = $(event.currentTarget);
					if ($input.val() == 1) {
						$enableNotification.enable();
					}
					else {
						$enableNotification.disable();
					}
				});
				
				// setup
				var $selectedOption = this._dialog.find('input[name=subscribe]:checked');
				if ($selectedOption.length && $selectedOption.val() == 1) {
					$enableNotification.enable();
				}
			}
			else if (data.actionName === 'saveSubscription' && this._dialog.is(':visible')) {
				this._dialog.wcfDialog('close');
				
				this._updateSubscriptionStatus({
					isSubscribed: data.returnValues.subscribe,
					objectID: data.returnValues.objectID
				});
				
				
				// show notification
				if (this._notification === null) {
					this._notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success.edit'));
				}
				
				this._notification.show();
			}
		},
		
		/**
		 * Saves the subscription.
		 *
		 * @param        object                event
		 */
		_save: function (event) {
			var $button = this._buttons[$(event.currentTarget).data('objectType')][$(event.currentTarget).data('objectID')];
			var $subscribe = this._dialog.find('input[name=subscribe]:checked').val();
			var $enableNotification = (this._dialog.find('input[name=enableNotification]').is(':checked')) ? 1 : 0;
			
			this._proxy.setOption('data', {
				actionName: 'saveSubscription',
				className: 'wcf\\data\\user\\object\\watch\\UserObjectWatchAction',
				parameters: {
					enableNotification: $enableNotification,
					objectID: $button.data('objectID'),
					objectType: $button.data('objectType'),
					subscribe: $subscribe
				}
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Updates subscription status and icon.
		 *
		 * @param        object                data
		 */
		_updateSubscriptionStatus: function (data) {
			var $button = $(this._buttonSelector + '[data-object-id=' + data.objectID + ']');
			var $icon = $button.children('.icon');
			if (data.isSubscribed) {
				$icon[0].querySelector("fa-icon").setIcon("bookmark", true);
				$button.data('isSubscribed', true);
				$button.addClass('active');
			}
			else {
				if ($button.data('removeOnUnsubscribe')) {
					$button.parent().remove();
				}
				else {
					$icon[0].querySelector("fa-icon").setIcon("bookmark");
					$button.data('isSubscribed', false);
					$button.removeClass('active');
				}
				
				if (this._reloadOnUnsubscribe) {
					window.location.reload();
					return;
				}
			}
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.objectWatch', 'updatedSubscription', data);
		}
	});
}
else {
	WCF.User.ObjectWatch.Subscribe = Class.extend({
		_buttonSelector: "",
		_buttons: {},
		_dialog: {},
		_notification: {},
		_reloadOnUnsubscribe: false,
		init: function() {},
		_click: function() {},
		_success: function() {},
		_save: function() {},
		_updateSubscriptionStatus: function() {}
	});
}

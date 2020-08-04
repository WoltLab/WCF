"use strict";

/**
 * User-related classes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

/**
 * User login
 * 
 * @param	boolean		isQuickLogin
 */
WCF.User.Login = Class.extend({
	/**
	 * login button
	 * @var	jQuery
	 */
	_loginSubmitButton: null,
	
	/**
	 * password input
	 * @var	jQuery
	 */
	_password: null,
	
	/**
	 * password input container
	 * @var	jQuery
	 */
	_passwordContainer: null,
	
	/**
	 * cookie input
	 * @var	jQuery
	 */
	_useCookies: null,
	
	/**
	 * cookie input container
	 * @var	jQuery
	 */
	_useCookiesContainer: null,
	
	/**
	 * Initializes the user login
	 * 
	 * @param	boolean		isQuickLogin
	 */
	init: function(isQuickLogin) {
		this._loginSubmitButton = $('#loginSubmitButton');
		this._password = $('#password'),
		this._passwordContainer = this._password.parents('dl');
		this._useCookies = $('#useCookies');
		this._useCookiesContainer = this._useCookies.parents('dl');
		
		var $loginForm = $('#loginForm');
		$loginForm.find('input[name=action]').change($.proxy(this._change, this));
		
		if (isQuickLogin) {
			WCF.User.QuickLogin.init();
		}
	},
	
	/**
	 * Handle toggle between login and register.
	 * 
	 * @param	object		event
	 */
	_change: function(event) {
		if ($(event.currentTarget).val() === 'register') {
			this._setState(false, WCF.Language.get('wcf.user.button.register'));
		}
		else {
			this._setState(true, WCF.Language.get('wcf.user.button.login'));
		}
	},
	
	/**
	 * Sets form states.
	 * 
	 * @param	boolean		enable
	 * @param	string		buttonTitle
	 */
	_setState: function(enable, buttonTitle) {
		if (enable) {
			this._password.enable();
			this._passwordContainer.removeClass('disabled');
			this._useCookies.enable();
			this._useCookiesContainer.removeClass('disabled');
		}
		else {
			this._password.disable();
			this._passwordContainer.addClass('disabled');
			this._useCookies.disable();
			this._useCookiesContainer.addClass('disabled');
		}
		
		this._loginSubmitButton.val(buttonTitle);
	}
});

/**
 * Namespace for User Panel classes.
 */
WCF.User.Panel = { };

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Abstract implementation for user panel items providing an interactive dropdown.
	 *
	 * @param        jQuery                triggerElement
	 * @param        string                identifier
	 * @param        object                options
	 */
	WCF.User.Panel.Abstract = Class.extend({
		/**
		 * counter badge
		 * @var        jQuery
		 */
		_badge: null,
		
		/**
		 * interactive dropdown instance
		 * @var        WCF.Dropdown.Interactive.Instance
		 */
		_dropdown: null,
		
		/**
		 * dropdown identifier
		 * @var        string
		 */
		_identifier: '',
		
		/**
		 * true if data should be loaded using an AJAX request
		 * @var        boolean
		 */
		_loadData: true,
		
		/**
		 * header icon to mark all items belonging to this user panel item as read
		 * @var        jQuery
		 */
		_markAllAsReadLink: null,
		
		/**
		 * list of options required to dropdown initialization and UI features
		 * @var        object
		 */
		_options: {},
		
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * trigger element
		 * @var        jQuery
		 */
		_triggerElement: null,
		
		/**
		 * @var        Element
		 */
		_button: null,
		
		/**
		 * @var        Function
		 */
		 _callbackFocus: null,
		
		/**
		 * @var {string}
		 */
		_callbackCloseUuid: '',
		
		/**
		 * @var        boolean
		 */
		_wasInsideDropdown: false,
		
		/**
		 * Initializes the WCF.User.Panel.Abstract class.
		 *
		 * @param        jQuery                triggerElement
		 * @param        string                identifier
		 * @param        object                options
		 */
		init: function (triggerElement, identifier, options) {
			this._dropdown = null;
			this._loadData = true;
			this._identifier = identifier;
			this._triggerElement = triggerElement;
			this._options = options;
			this._callbackFocus = null;
			this._callbackCloseUuid = '';
			
			this._proxy = new WCF.Action.Proxy({
				showLoadingOverlay: false,
				success: $.proxy(this._success, this)
			});
			
			this._triggerElement.click($.proxy(this.toggle, this));
			this._button = elBySel('a', this._triggerElement[0]);
			if (this._button) {
				elAttr(this._button, 'role', 'button');
				elAttr(this._button, 'tabindex', '0');
				elAttr(this._button, 'aria-haspopup', true);
				elAttr(this._button, 'aria-expanded', false);
			}
			
			if (this._options.showAllLink) {
				this._triggerElement.dblclick($.proxy(this._dblClick, this));
			}
			
			if (this._options.staticDropdown === true) {
				this._loadData = false;
			}
			else {
				var $badge = this._triggerElement.find('span.badge');
				if ($badge.length) {
					this._badge = $badge;
				}
			}
		},
		
		/**
		 * Toggles the interactive dropdown.
		 *
		 * @param        {Event?}        event
		 * @return        {boolean}
		 */
		toggle: function (event) {
			if (event instanceof Event) {
				event.preventDefault();
			}
			
			if (this._dropdown === null) {
				this._dropdown = this._initDropdown();
			}
			
			if (this._dropdown.toggle()) {
				if (!this._loadData) {
					// check if there are outstanding items but there are no outstanding ones in the current list
					if (this._badge !== null) {
						var $count = parseInt(this._badge.text()) || 0;
						if ($count && !this._dropdown.getItemList().children('.interactiveDropdownItemOutstanding').length) {
							this._loadData = true;
						}
					}
				}
				
				if (this._loadData) {
					this._loadData = false;
					this._load();
				}
				
				elAttr(this._button, 'aria-expanded', true);
				if (this._callbackFocus === null) {
					this._callbackFocus = this._maintainFocus.bind(this);
				}
				document.body.addEventListener('focus', this._callbackFocus, { capture: true });
				
				this._callbackCloseUuid = WCF.System.Event.addListener('WCF.Dropdown.Interactive.Instance', 'close', (function (data) {
					if (data.instance === this._dropdown) {
						WCF.System.Event.removeListener('WCF.Dropdown.Interactive.Instance', 'close', this._callbackCloseUuid);
						document.body.removeEventListener('focus', this._callbackFocus, { capture: true });
					}
				}).bind(this));
			}
			else {
				elAttr(this._button, 'aria-expanded', false);
				
				WCF.System.Event.removeListener('WCF.Dropdown.Interactive.Instance', 'close', this._callbackCloseUuid);
				document.body.removeEventListener('focus', this._callbackFocus, { capture: true });
			}
			
			return false;
		},
		
		/**
		 * Forward to original URL by double clicking the trigger element.
		 *
		 * @param        object                event
		 * @return        boolean
		 */
		_dblClick: function (event) {
			event.preventDefault();
			
			window.location = this._options.showAllLink;
			
			return false;
		},
		
		/**
		 * Initializes the dropdown on first usage.
		 *
		 * @return        WCF.Dropdown.Interactive.Instance
		 */
		_initDropdown: function () {
			var $dropdown = WCF.Dropdown.Interactive.Handler.create(this._triggerElement, this._identifier, this._options);
			$('<li class="loading"><span class="icon icon24 fa-spinner" /> <span>' + WCF.Language.get('wcf.global.loading') + '</span></li>').appendTo($dropdown.getItemList());
			
			return $dropdown;
		},
		
		/**
		 * Loads item list data via AJAX.
		 */
		_load: function () {
			// override this in your own implementation to fetch display data
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param        object                data
		 */
		_success: function (data) {
			if (data.returnValues.template !== undefined) {
				var $itemList = this._dropdown.getItemList().empty();
				$(data.returnValues.template).appendTo($itemList);
				
				if (!$itemList.children().length) {
					$('<li class="noItems">' + this._options.noItems + '</li>').appendTo($itemList);
				}
				
				if (this._options.enableMarkAsRead) {
					var $outstandingItems = this._dropdown.getItemList().children('.interactiveDropdownItemOutstanding');
					if (this._markAllAsReadLink === null && $outstandingItems.length) {
						var $button = this._markAllAsReadLink = $('<li class="interactiveDropdownItemMarkAllAsRead"><a href="#" title="' + WCF.Language.get('wcf.user.panel.markAllAsRead') + '" class="jsTooltip"><span class="icon icon24 fa-check" /></a></li>').appendTo(this._dropdown.getLinkList());
						$button.click((function (event) {
							this._dropdown.close();
							
							this._markAllAsRead();
							
							return false;
						}).bind(this));
					}
					
					$outstandingItems.each((function (index, item) {
						var $item = $(item).addClass('interactiveDropdownItemOutstandingIcon');
						var $objectID = $item.data('objectID');
						
						var $button = $('<div class="interactiveDropdownItemMarkAsRead"><a href="#" title="' + WCF.Language.get('wcf.user.panel.markAsRead') + '" class="jsTooltip"><span class="icon icon16 fa-check" /></a></div>').appendTo($item);
						$button.click((function (event) {
							this._markAsRead(event, $objectID);
							
							return false;
						}).bind(this));
					}).bind(this));
				}
				
				this._dropdown.getItemList().children().each(function (index, item) {
					var $item = $(item);
					var $link = $item.data('link');
					
					if ($link) {
						if ($.browser.msie) {
							$item.click(function (event) {
								if (event.target.tagName !== 'A') {
									window.location = $link;
									
									return false;
								}
							});
						}
						else {
							$item.addClass('interactiveDropdownItemShadow');
							$('<a href="' + $link + '" class="interactiveDropdownItemShadowLink" />').appendTo($item);
						}
						
						if ($item.data('linkReplaceAll')) {
							$item.find('> .box48 a:not(.userLink)').prop('href', $link);
						}
					}
				});
				
				this._dropdown.rebuildScrollbar();
			}
			
			if (data.returnValues.totalCount !== undefined) {
				this.updateBadge(data.returnValues.totalCount);
			}
			
			if (this._options.enableMarkAsRead) {
				if (data.returnValues.markAsRead) {
					var $item = this._dropdown.getItemList().children('li[data-object-id=' + data.returnValues.markAsRead + ']');
					if ($item.length) {
						$item.removeClass('interactiveDropdownItemOutstanding').data('isRead', true);
						$item.children('.interactiveDropdownItemMarkAsRead').remove();
					}
				}
				else if (data.returnValues.markAllAsRead) {
					this.resetItems();
					this.updateBadge(0);
				}
			}
		},
		
		/**
		 * Marks an item as read.
		 *
		 * @param        object                event
		 * @param        integer                objectID
		 */
		_markAsRead: function (event, objectID) {
			// override this in your own implementation to mark an item as read
		},
		
		/**
		 * Marks all items as read.
		 */
		_markAllAsRead: function () {
			// override this in your own implementation to mark all items as read
		},
		
		/**
		 * Updates the badge's count or removes it if count reaches zero. Passing a negative number is undefined.
		 *
		 * @param        integer                count
		 */
		updateBadge: function (count) {
			count = parseInt(count) || 0;
			
			if (count) {
				if (this._badge === null) {
					this._badge = $('<span class="badge badgeUpdate" />').appendTo(this._triggerElement.children('a'));
					this._badge.before(' ');
				}
				
				this._badge.text(count);
			}
			else if (this._badge !== null) {
				this._badge.remove();
				this._badge = null;
			}
			
			if (this._options.enableMarkAsRead) {
				if (!count && this._markAllAsReadLink !== null) {
					this._markAllAsReadLink.remove();
					this._markAllAsReadLink = null;
				}
			}
			
			WCF.System.Event.fireEvent('com.woltlab.wcf.userMenu', 'updateBadge', {
				count: count,
				identifier: this._identifier
			});
		},
		
		/**
		 * Resets the dropdown's inner item list.
		 */
		resetItems: function () {
			// this method could be called from outside, but the dropdown was never
			// toggled and thus never initialized
			if (this._dropdown !== null) {
				this._dropdown.resetItems();
				this._loadData = true;
			}
		},
		
		/**
		 * @param {Event} event
		 */
		_maintainFocus: function(event) {
			var dropdown = this._dropdown.getContainer()[0];
			
			if (!dropdown.contains(event.target)) {
				if (this._wasInsideDropdown) {
					this._button.focus();
					this._wasInsideDropdown = false;
				}
				else {
					elBySel('a', dropdown).focus();
				}
			}
			else {
				this._wasInsideDropdown = true;
			}
		}
	});
	
	/**
	 * User Panel implementation for user notifications.
	 *
	 * @see        WCF.User.Panel.Abstract
	 */
	WCF.User.Panel.Notification = WCF.User.Panel.Abstract.extend({
		/**
		 * favico instance
		 * @var        Favico
		 */
		_favico: null,
		
		/**
		 * @see        WCF.User.Panel.Abstract.init()
		 */
		init: function (options) {
			options.enableMarkAsRead = true;
			
			this._super($('#userNotifications'), 'userNotifications', options);
			
			try {
				this._favico = new Favico({
					animation: 'none',
					type: 'circle'
				});
				
				if (this._badge !== null) {
					var $count = parseInt(this._badge.text()) || 0;
					this._favico.badge($count);
				}
			}
			catch (e) {
				console.debug("[WCF.User.Panel.Notification] Failed to initialized Favico: " + e.message);
			}
			
			WCF.System.PushNotification.addCallback('userNotificationCount', $.proxy(this.updateUserNotificationCount, this));
			
			require(['EventHandler'], (function (EventHandler) {
				EventHandler.add('com.woltlab.wcf.UserMenuMobile', 'more', (function (data) {
					if (data.identifier === 'com.woltlab.wcf.notifications') {
						this.toggle();
					}
				}).bind(this));
			}).bind(this));
		},
		
		/**
		 * @see        WCF.User.Panel.Abstract._initDropdown()
		 */
		_initDropdown: function () {
			var $dropdown = this._super();
			
			$('<li><a href="' + this._options.settingsLink + '" title="' + WCF.Language.get('wcf.user.panel.settings') + '" class="jsTooltip"><span class="icon icon24 fa-cog" /></a></li>').appendTo($dropdown.getLinkList());
			
			return $dropdown;
		},
		
		/**
		 * @see        WCF.User.Panel.Abstract._load()
		 */
		_load: function () {
			this._proxy.setOption('data', {
				actionName: 'getOutstandingNotifications',
				className: 'wcf\\data\\user\\notification\\UserNotificationAction'
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * @see        WCF.User.Panel.Abstract._markAsRead()
		 */
		_markAsRead: function (event, objectID) {
			this._proxy.setOption('data', {
				actionName: 'markAsConfirmed',
				className: 'wcf\\data\\user\\notification\\UserNotificationAction',
				objectIDs: [objectID]
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * @see        WCF.User.Panel.Abstract._markAllAsRead()
		 */
		_markAllAsRead: function (event) {
			this._proxy.setOption('data', {
				actionName: 'markAllAsConfirmed',
				className: 'wcf\\data\\user\\notification\\UserNotificationAction'
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * @see        WCF.User.Panel.Abstract.resetItems()
		 */
		resetItems: function () {
			this._super();
			
			if (this._markAllAsReadLink) {
				this._markAllAsReadLink.remove();
				this._markAllAsReadLink = null;
			}
		},
		
		/**
		 * @see        WCF.User.Panel.Abstract.updateBadge()
		 */
		updateBadge: function (count) {
			count = parseInt(count) || 0;
			
			// update data attribute
			$('#userNotifications').attr('data-count', count);
			
			if (this._favico !== null) {
				this._favico.badge(count);
			}
			
			this._super(count);
		},
		
		/**
		 * Updates the badge counter and resets the dropdown's item list.
		 *
		 * @param        integer                count
		 */
		updateUserNotificationCount: function (count) {
			if (this._dropdown !== null) {
				this._dropdown.resetItems();
			}
			
			this.updateBadge(count);
		}
	});
	
	/**
	 * User Panel implementation for user menu dropdown.
	 *
	 * @see        WCF.User.Panel.Abstract
	 */
	WCF.User.Panel.UserMenu = WCF.User.Panel.Abstract.extend({
		/**
		 * @see        WCF.User.Panel.Abstract.init()
		 */
		init: function () {
			this._super($('#userMenu'), 'userMenu', {
				pointerOffset: '13px',
				staticDropdown: true
			});
		}
	});
}
else {
	WCF.User.Panel.Abstract = Class.extend({
		_badge: {},
		_dropdown: {},
		_identifier: "",
		_loadData: true,
		_markAllAsReadLink: {},
		_options: {},
		_proxy: {},
		_triggerElement: {},
		init: function() {},
		toggle: function() {},
		_dblClick: function() {},
		_initDropdown: function() {},
		_load: function() {},
		_success: function() {},
		_markAsRead: function() {},
		_markAllAsRead: function() {},
		updateBadge: function() {},
		resetItems: function() {}
	});
	
	WCF.User.Panel.Notification = WCF.User.Panel.Abstract.extend({
		_favico: {},
		init: function() {},
		_initDropdown: function() {},
		_load: function() {},
		_markAsRead: function() {},
		_markAllAsRead: function() {},
		resetItems: function() {},
		updateBadge: function() {},
		updateUserNotificationCount: function() {},
		_badge: {},
		_dropdown: {},
		_identifier: "",
		_loadData: true,
		_markAllAsReadLink: {},
		_options: {},
		_proxy: {},
		_triggerElement: {},
		toggle: function() {},
		_dblClick: function() {},
		_success: function() {}
	});
	
	WCF.User.Panel.UserMenu = WCF.User.Panel.Abstract.extend({
		init: function() {},
		_badge: {},
		_dropdown: {},
		_identifier: "",
		_loadData: true,
		_markAllAsReadLink: {},
		_options: {},
		_proxy: {},
		_triggerElement: {},
		toggle: function() {},
		_dblClick: function() {},
		_initDropdown: function() {},
		_load: function() {},
		_success: function() {},
		_markAsRead: function() {},
		_markAllAsRead: function() {},
		updateBadge: function() {},
		resetItems: function() {}
	});
}

/**
 * Quick login box
 */
WCF.User.QuickLogin = {
	/**
	 * Initializes the quick login box
	 */
	init: function() {
		require(['EventHandler', 'Ui/Dialog'], function(EventHandler, UiDialog) {
			var loginForm = elById('loginForm');
			var loginSection = elBySel('.loginFormLogin', loginForm);
			if (loginSection && !loginSection.nextElementSibling) {
				loginForm.classList.add('loginFormLoginOnly');
			}
			
			var registrationBlock = elBySel('.loginFormRegister', loginForm);
			
			var callbackOpen = function(event) {
				if (event instanceof Event) {
					event.preventDefault();
					event.stopPropagation();
				}
				
				loginForm.style.removeProperty('display');
				
				UiDialog.openStatic('loginForm', null, {
					title: WCF.Language.get('wcf.user.login')
				});
				
				// The registration part should always be on the right
				// but some browser (Firefox and IE) have a really bad
				// support for forcing column breaks, requiring us to
				// work around it by force pushing it to the right.
				if (loginSection !== null && registrationBlock !== null) {
					var loginOffset = loginSection.offsetTop;
					var margin = 0;
					if (loginForm.clientWidth > loginSection.clientWidth * 2) {
						while (loginOffset < (registrationBlock.offsetTop - 50)) {
							// push the registration down by 100 pixel each time
							margin += 100;
							loginSection.style.setProperty('margin-bottom', margin + 'px', '');
						}
					}
				}
			};
			
			var links = document.getElementsByClassName('loginLink');
			for (var i = 0, length = links.length; i < length; i++) {
				links[i].addEventListener(WCF_CLICK_EVENT, callbackOpen);
			}
			
			var input = loginForm.querySelector('#loginForm input[name=url]');
			if (input !== null && !input.value.match(/^https?:\/\//)) {
				input.setAttribute('value', window.location.protocol + '//' + window.location.host + input.getAttribute('value'));
			}
			
			EventHandler.add('com.woltlab.wcf.UserMenuMobile', 'more', function(data) {
				if (data.identifier === 'com.woltlab.wcf.login') {
					data.handler.close(true);
					
					callbackOpen();
				}
			});
		});
	}
};

/**
 * UserProfile namespace
 */
WCF.User.Profile = {};

/**
 * Shows the activity point list for users.
 */
WCF.User.Profile.ActivityPointList = {
	/**
	 * list of cached templates
	 * @var	object
	 */
	_cache: { },
	
	/**
	 * dialog overlay
	 * @var	jQuery
	 */
	_dialog: null,
	
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
		this._dialog = null;
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		this._init();
		
		WCF.DOMNodeInsertedHandler.addCallback('WCF.User.Profile.ActivityPointList', $.proxy(this._init, this));
		
		this._didInit = true;
	},
	
	/**
	 * Initializes display for activity points.
	 */
	_init: function() {
		$('.activityPointsDisplay').removeClass('activityPointsDisplay').click($.proxy(this._click, this));
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
		if (this._dialog === null) {
			this._dialog = $('<div>' + this._cache[userID] + '</div>').hide().appendTo(document.body);
			this._dialog.wcfDialog({
				title: WCF.Language.get('wcf.user.activityPoint')
			});
		}
		else {
			this._dialog.html(this._cache[userID]);
			this._dialog.wcfDialog('open');
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
		this._cache[data.returnValues.userID] = data.returnValues.template;
		this._show(data.returnValues.userID);
	}
};

/**
 * Provides methods to load tab menu content upon request.
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

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * User profile inline editor.
	 *
	 * @param        integer                userID
	 * @param        boolean                editOnInit
	 */
	WCF.User.Profile.Editor = Class.extend({
		/**
		 * current action
		 * @var        string
		 */
		_actionName: '',
		
		_active: false,
		
		/**
		 * list of interface buttons
		 * @var        object
		 */
		_buttons: {},
		
		/**
		 * cached tab content
		 * @var        string
		 */
		_cachedTemplate: '',
		
		/**
		 * action proxy
		 * @var        WCF.Action.Proxy
		 */
		_proxy: null,
		
		/**
		 * tab object
		 * @var        jQuery
		 */
		_tab: null,
		
		/**
		 * target user id
		 * @var        integer
		 */
		_userID: 0,
		
		/**
		 * Initializes the WCF.User.Profile.Editor object.
		 *
		 * @param        integer                userID
		 * @param        boolean                editOnInit
		 */
		init: function (userID, editOnInit) {
			this._actionName = '';
			this._active = false;
			this._cachedTemplate = '';
			this._tab = $('#about');
			this._userID = userID;
			this._proxy = new WCF.Action.Proxy({
				success: $.proxy(this._success, this)
			});
			
			this._initButtons();
			
			// begin editing on page load
			if (editOnInit) {
				this._beginEdit();
			}
		},
		
		/**
		 * Initializes interface buttons.
		 */
		_initButtons: function () {
			// create buttons
			this._buttons = {
				beginEdit: $('.jsButtonEditProfile:eq(0)').click(this._beginEdit.bind(this))
			};
		},
		
		/**
		 * Begins editing.
		 *
		 * @param       {Event?}         event   event object
		 */
		_beginEdit: function (event) {
			if (event) event.preventDefault();
			
			if (this._active) return;
			this._active = true;
			
			this._actionName = 'beginEdit';
			this._buttons.beginEdit.parent().addClass('active');
			$('#profileContent').wcfTabs('select', 'about');
			
			// load form
			this._proxy.setOption('data', {
				actionName: 'beginEdit',
				className: 'wcf\\data\\user\\UserProfileAction',
				objectIDs: [this._userID]
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Saves input values.
		 */
		_save: function () {
			// check if there is an editor and if it is in WYSIWYG mode
			var scrollToEditor = null;
			elBySelAll('.redactor-layer', this._tab[0], function(redactorLayer) {
				var data = {
					api: {
						throwError: elInnerError
					},
					valid: true
				};
				WCF.System.Event.fireEvent('com.woltlab.wcf.redactor2', 'validate_' + elData(redactorLayer, 'element-id'), data);
				
				if (!data.valid && scrollToEditor === null) {
					scrollToEditor = redactorLayer.parentNode;
				}
			});
			
			if (scrollToEditor) {
				scrollToEditor.scrollIntoView({ behavior: 'smooth' });
				return;
			}
			
			this._actionName = 'save';
			
			// collect values
			var $regExp = /values\[([a-zA-Z0-9._-]+)\]/;
			var $values = {};
			this._tab.find('input, textarea, select').each(function (index, element) {
				var $element = $(element);
				var $value = null;
				
				switch ($element.getTagName()) {
					case 'input':
						var $type = $element.attr('type');
						
						if (($type === 'radio' || $type === 'checkbox') && !$element.prop('checked')) {
							return;
						}
						break;
					
					case 'textarea':
						if ($element.data('redactor')) {
							$value = $element.redactor('code.get');
						}
						break;
				}
				
				var $name = $element.attr('name');
				if ($regExp.test($name)) {
					var $fieldName = RegExp.$1;
					if ($value === null) $value = $element.val();
					
					// check for checkboxes
					if ($element.attr('type') === 'checkbox' && /\[\]$/.test($name)) {
						if (!Array.isArray($values[$fieldName])) {
							$values[$fieldName] = [];
						}
						
						$values[$fieldName].push($value);
					}
					else {
						$values[$fieldName] = $value;
					}
				}
			});
			
			this._proxy.setOption('data', {
				actionName: 'save',
				className: 'wcf\\data\\user\\UserProfileAction',
				objectIDs: [this._userID],
				parameters: {
					values: $values
				}
			});
			this._proxy.sendRequest();
		},
		
		/**
		 * Restores back to default view.
		 */
		_restore: function () {
			this._actionName = 'restore';
			this._active = false;
			this._buttons.beginEdit.parent().removeClass('active');
			
			this._destroyEditor();
			
			this._tab.html(this._cachedTemplate).children().css({height: 'auto'});
		},
		
		/**
		 * Handles successful AJAX requests.
		 *
		 * @param        object                data
		 * @param        string                textStatus
		 * @param        jQuery                jqXHR
		 */
		_success: function (data, textStatus, jqXHR) {
			switch (this._actionName) {
				case 'beginEdit':
					this._prepareEdit(data);
					break;
				
				case 'save':
					// save was successful, show parsed template
					if (data.returnValues.success) {
						this._cachedTemplate = data.returnValues.template;
						this._restore();
					}
					else {
						this._prepareEdit(data, true);
					}
					break;
			}
		},
		
		/**
		 * Prepares editing mode.
		 *
		 * @param        object                data
		 * @param        boolean                disableCache
		 */
		_prepareEdit: function (data, disableCache) {
			this._destroyEditor();
			
			// update template
			var self = this;
			this._tab.html(function (index, oldHTML) {
				if (disableCache !== true) {
					self._cachedTemplate = oldHTML;
				}
				
				return data.returnValues.template;
			});
			
			// block autocomplete
			this._tab.find('input[type=text]').attr('autocomplete', 'off');
			
			// bind event listener
			this._tab.find('.formSubmit > button[data-type=save]').click($.proxy(this._save, this));
			this._tab.find('.formSubmit > button[data-type=restore]').click($.proxy(this._restore, this));
			this._tab.find('input').keyup(function (event) {
				if (event.which === $.ui.keyCode.ENTER) {
					self._save();
					
					event.preventDefault();
					return false;
				}
			});
		},
		
		/**
		 * Destroys all editor instances within current tab.
		 */
		_destroyEditor: function () {
			// destroy all editor instances
			this._tab.find('textarea').each(function (index, container) {
				var $container = $(container);
				if ($container.data('redactor')) {
					$container.redactor('core.destroy');
				}
			});
		}
	});
}
else {
	WCF.User.Profile.Editor = Class.extend({
		_actionName: "",
		_active: false,
		_buttons: {},
		_cachedTemplate: "",
		_proxy: {},
		_tab: {},
		_userID: 0,
		init: function() {},
		_initButtons: function() {},
		_beginEdit: function() {},
		_save: function() {},
		_restore: function() {},
		_success: function() {},
		_prepareEdit: function() {},
		_destroyEditor: function() {}
	});
}

/**
 * Namespace for registration functions.
 */
WCF.User.Registration = {};

/**
 * Validates the password.
 * 
 * @param	jQuery		element
 * @param	jQuery		confirmElement
 * @param	object		options
 */
WCF.User.Registration.Validation = Class.extend({
	/**
	 * action name
	 * @var	string
	 */
	_actionName: '',
	
	/**
	 * class name
	 * @var	string
	 */
	_className: '',
	
	/**
	 * confirmation input element
	 * @var	jQuery
	 */
	_confirmElement: null,
	
	/**
	 * input element
	 * @var	jQuery
	 */
	_element: null,
	
	/**
	 * list of error messages
	 * @var	object
	 */
	_errorMessages: { },
	
	/**
	 * list of additional options
	 * @var	object
	 */
	_options: { },
	
	/**
	 * AJAX proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * Initializes the validation.
	 * 
	 * @param	jQuery		element
	 * @param	jQuery		confirmElement
	 * @param	object		options
	 */
	init: function(element, confirmElement, options) {
		this._element = element;
		this._element.blur($.proxy(this._blur, this));
		this._confirmElement = confirmElement || null;
		
		if (this._confirmElement !== null) {
			this._confirmElement.blur($.proxy(this._blurConfirm, this));
		}
		
		options = options || { };
		this._setOptions(options);
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this),
			showLoadingOverlay: false
		});
		
		this._setErrorMessages();
	},
	
	/**
	 * Sets additional options
	 */
	_setOptions: function(options) { },
	
	/**
	 * Sets error messages.
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: '',
			notEqual: ''
		};
	},
	
	/**
	 * Validates once focus on input is lost.
	 * 
	 * @param	object		event
	 */
	_blur: function(event) {
		var $value = this._element.val();
		if (!$value) {
			return this._showError(this._element, WCF.Language.get('wcf.global.form.error.empty'));
		}
		
		if (this._confirmElement !== null) {
			var $confirmValue = this._confirmElement.val();
			if ($confirmValue != '' && $value != $confirmValue) {
				return this._showError(this._confirmElement, this._errorMessages.notEqual);
			}
		}
		
		if (!this._validateOptions()) {
			return;
		}
		
		this._proxy.setOption('data', {
			actionName: this._actionName,
			className: this._className,
			parameters: this._getParameters()
		});
		this._proxy.sendRequest();
	},
	
	/**
	 * Returns a list of parameters.
	 * 
	 * @return	object
	 */
	_getParameters: function() {
		return { };
	},
	
	/**
	 * Validates input by options.
	 * 
	 * @return	boolean
	 */
	_validateOptions: function() {
		return true;
	},
	
	/**
	 * Validates value once confirmation input focus is lost.
	 * 
	 * @param	object		event
	 */
	_blurConfirm: function(event) {
		var $value = this._confirmElement.val();
		if (!$value) {
			return this._showError(this._confirmElement, WCF.Language.get('wcf.global.form.error.empty'));
		}
		
		this._blur(event);
	},
	
	/**
	 * Handles AJAX responses.
	 * 
	 * @param	object		data
	 * @param	string		textStatus
	 * @param	jQuery		jqXHR
	 */
	_success: function(data, textStatus, jqXHR) {
		if (data.returnValues.isValid) {
			this._showSuccess(this._element);
			if (this._confirmElement !== null && this._confirmElement.val()) {
				this._showSuccess(this._confirmElement);
			}
		}
		else {
			this._showError(this._element, WCF.Language.get(this._errorMessages.ajaxError + data.returnValues.error));
		}
	},
	
	/**
	 * Shows an error message.
	 * 
	 * @param	jQuery		element
	 * @param	string		message
	 */
	_showError: function(element, message) {
		element.parent().parent().addClass('formError').removeClass('formSuccess');
		
		var $innerError = element.parent().find('small.innerError');
		if (!$innerError.length) {
			$innerError = $('<small />').addClass('innerError').insertAfter(element);
		}
		
		$innerError.text(message);
	},
	
	/**
	 * Displays a success message.
	 * 
	 * @param	jQuery		element
	 */
	_showSuccess: function(element) {
		element.parent().parent().addClass('formSuccess').removeClass('formError');
		element.next('small.innerError').remove();
	}
});

/**
 * Username validation for registration.
 * 
 * @see	WCF.User.Registration.Validation
 */
WCF.User.Registration.Validation.Username = WCF.User.Registration.Validation.extend({
	/**
	 * @see	WCF.User.Registration.Validation._actionName
	 */
	_actionName: 'validateUsername',
	
	/**
	 * @see	WCF.User.Registration.Validation._className
	 */
	_className: 'wcf\\data\\user\\UserRegistrationAction',
	
	/**
	 * @see	WCF.User.Registration.Validation._setOptions()
	 */
	_setOptions: function(options) {
		this._options = $.extend(true, {
			minlength: 3,
			maxlength: 25
		}, options);
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._setErrorMessages()
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: 'wcf.user.username.error.'
		};
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._validateOptions()
	 */
	_validateOptions: function() {
		var $value = this._element.val();
		if ($value.length < this._options.minlength || $value.length > this._options.maxlength) {
			this._showError(this._element, WCF.Language.get('wcf.user.username.error.invalid'));
			return false;
		}
		
		return true;
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._getParameters()
	 */
	_getParameters: function() {
		return {
			username: this._element.val()
		};
	}
});

/**
 * Email validation for registration.
 * 
 * @see	WCF.User.Registration.Validation
 */
WCF.User.Registration.Validation.EmailAddress = WCF.User.Registration.Validation.extend({
	/**
	 * @see	WCF.User.Registration.Validation._actionName
	 */
	_actionName: 'validateEmailAddress',
	
	/**
	 * @see	WCF.User.Registration.Validation._className
	 */
	_className: 'wcf\\data\\user\\UserRegistrationAction',
	
	/**
	 * @see	WCF.User.Registration.Validation._getParameters()
	 */
	_getParameters: function() {
		return {
			email: this._element.val()
		};
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._setErrorMessages()
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: 'wcf.user.email.error.',
			notEqual: WCF.Language.get('wcf.user.confirmEmail.error.notEqual')
		};
	}
});

/**
 * Password validation for registration.
 * 
 * @see	WCF.User.Registration.Validation
 */
WCF.User.Registration.Validation.Password = WCF.User.Registration.Validation.extend({
	/**
	 * @see	WCF.User.Registration.Validation._actionName
	 */
	_actionName: 'validatePassword',
	
	/**
	 * @see	WCF.User.Registration.Validation._className
	 */
	_className: 'wcf\\data\\user\\UserRegistrationAction',
	
	/**
	 * @see	WCF.User.Registration.Validation._getParameters()
	 */
	_getParameters: function() {
		return {
			password: this._element.val()
		};
	},
	
	/**
	 * @see	WCF.User.Registration.Validation._setErrorMessages()
	 */
	_setErrorMessages: function() {
		this._errorMessages = {
			ajaxError: 'wcf.user.password.error.',
			notEqual: WCF.Language.get('wcf.user.confirmPassword.error.notEqual')
		};
	}
});

/**
 * Toggles input fields for lost password form.
 */
WCF.User.Registration.LostPassword = Class.extend({
	/**
	 * email input
	 * @var	jQuery
	 */
	_email: null,
	
	/**
	 * username input
	 * @var	jQuery
	 */
	_username: null,
	
	/**
	 * Initializes LostPassword-form class.
	 */
	init: function() {
		// bind input fields
		this._email = $('#emailInput');
		this._username = $('#usernameInput');
		
		// bind event listener
		this._email.keyup($.proxy(this._checkEmail, this));
		this._username.keyup($.proxy(this._checkUsername, this));
		
		if ($.browser.mozilla && $.browser.touch) {
			this._email.on('input', $.proxy(this._checkEmail, this));
			this._username.on('input', $.proxy(this._checkUsername, this));
		}
		
		// toggle fields on init
		this._checkEmail();
		this._checkUsername();
	},
	
	/**
	 * Checks for content in email field and toggles username.
	 */
	_checkEmail: function() {
		if (this._email.val() == '') {
			this._username.enable();
			this._username.parents('dl:eq(0)').removeClass('disabled');
		}
		else {
			this._username.disable();
			this._username.parents('dl:eq(0)').addClass('disabled');
			this._username.val('');
		}
	},
	
	/**
	 * Checks for content in username field and toggles email.
	 */
	_checkUsername: function() {
		if (this._username.val() == '') {
			this._email.enable();
			this._email.parents('dl:eq(0)').removeClass('disabled');
		}
		else {
			this._email.disable();
			this._email.parents('dl:eq(0)').addClass('disabled');
			this._email.val('');
		}
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
			$('.contentHeaderNavigation .jsMarkAllAsConfirmed').click(function () {
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
					
					var $markAsConfirmed = $('<a href="#" class="icon icon24 fa-check notificationItemMarkAsConfirmed jsTooltip" title="' + WCF.Language.get('wcf.user.notification.markAsConfirmed') + '" />').appendTo($item);
					$markAsConfirmed.click($.proxy(this._markAsConfirmed, this));
				}
				
				// work-around for legacy notifications
				if (!$item.find('a:not(.notificationItemMarkAsConfirmed)').length) {
					$item.find('.details > p:eq(0)').html(function (index, oldHTML) {
						return '<a href="' + $item.data('link') + '">' + oldHTML + '</a>';
					});
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
			this._loadButton = $('<li class="showMore"><button class="small">' + WCF.Language.get('wcf.user.recentActivity.more') + '</button></li>').appendTo(this._container);
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
 * Loads likes once the user scrolls to the very bottom.
 * 
 * @param	integer		userID
 * @deprecated  since 5.2
 */
WCF.User.LikeLoader = Class.extend({
	/**
	 * container object
	 * @var	jQuery
	 */
	_container: null,
	
	/**
	 * like type
	 * @var	string
	 */
	_likeType: 'received',
	
	/**
	 * like value
	 * @var	integer
	 */
	_likeValue: 1,
	
	/**
	 * button to load next events
	 * @var	jQuery
	 */
	_loadButton: null,
	
	/**
	 * 'no more entries' element
	 * @var	jQuery
	 */
	_noMoreEntries: null,
	
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
	init: function(userID) {
		this._container = $('#likeList');
		this._userID = userID;
		
		if (!this._userID) {
			console.debug("[WCF.User.RecentActivityLoader] Invalid parameter 'userID' given.");
			return;
		}
		
		this._proxy = new WCF.Action.Proxy({
			success: $.proxy(this._success, this)
		});
		
		var $container = $('<li class="likeListMore showMore"><button class="small">' + WCF.Language.get('wcf.like.likes.more') + '</button><small>' + WCF.Language.get('wcf.like.likes.noMoreEntries') + '</small></li>').appendTo(this._container);
		this._loadButton = $container.children('button').click($.proxy(this._click, this));
		this._noMoreEntries = $container.children('small').hide();
		
		if (this._container.find('> li').length == 2) {
			this._loadButton.hide();
			this._noMoreEntries.show();
		}
		
		$('#likeType .button').click($.proxy(this._clickLikeType, this));
		$('#likeValue .button').click($.proxy(this._clickLikeValue, this));
	},
	
	/**
	 * Handles like type change.
	 */
	_clickLikeType: function(event) {
		var $button = $(event.currentTarget);
		if (this._likeType != $button.data('likeType')) {
			this._likeType = $button.data('likeType');
			$('#likeType .button').removeClass('active');
			$button.addClass('active');
			this._reload();
		}
	},
	
	/**
	 * Handles like value change.
	 */
	_clickLikeValue: function(event) {
		var $button = $(event.currentTarget);
		if (this._likeValue != $button.data('likeValue')) {
			this._likeValue = $button.data('likeValue');
			$('#likeValue .button').removeClass('active');
			$button.addClass('active');
			
			// change button labels
			$('#likeType > li:first-child > .button').text(WCF.Language.get('wcf.like.' + (this._likeValue == -1 ? 'dis' : '') + 'likesReceived'));
			$('#likeType > li:last-child > .button').text(WCF.Language.get('wcf.like.' + (this._likeValue == -1 ? 'dis' : '') + 'likesGiven'));
			
			this._container.find('> li.likeListMore button').text(WCF.Language.get('wcf.like.' + (this._likeValue == -1 ? 'dis' : '') + 'likes.more'));
			this._container.find('> li.likeListMore small').text(WCF.Language.get('wcf.like.' + (this._likeValue == -1 ? 'dis' : '') + 'likes.noMoreEntries'));
			
			this._reload();
		}
	},
	
	/**
	 * Handles reload.
	 */
	_reload: function() {
		this._container.find('> li:not(:first-child):not(:last-child)').remove();
		this._container.data('lastLikeTime', 0);
		this._click();
	},
	
	/**
	 * Loads next likes.
	 */
	_click: function() {
		this._loadButton.enable();
		
		var $parameters = {
			lastLikeTime: this._container.data('lastLikeTime'),
			userID: this._userID,
			likeType: this._likeType,
			likeValue: this._likeValue
		};
		
		this._proxy.setOption('data', {
			actionName: 'load',
			className: 'wcf\\data\\like\\LikeAction',
			parameters: $parameters
		});
		this._proxy.sendRequest();
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
			
			this._container.data('lastLikeTime', data.returnValues.lastLikeTime);
			this._noMoreEntries.hide();
			this._loadButton.show().enable();
		}
		else {
			this._noMoreEntries.show();
			this._loadButton.hide();
		}
	}
});

/**
 * Loads user profile previews.
 * 
 * @see	WCF.Popover
 * @deprecated	since 5.3, taken care of by `WoltLabSuite/Core/BootstrapFrontend` via `WoltLabSuite/Core/Controller/Popover`
 */
WCF.User.ProfilePreview = WCF.Popover.extend({
	/**
	 * action proxy
	 * @var	WCF.Action.Proxy
	 */
	_proxy: null,
	
	/**
	 * list of user profiles
	 * @var	object
	 */
	_userProfiles: { },
	
	/**
	 * @see	WCF.Popover.init()
	 */
	init: function() {
		this._super('.userLink');
		
		this._proxy = new WCF.Action.Proxy({
			showLoadingOverlay: false
		});
		
		// register instance
		WCF.System.ObjectStore.add('WCF.User.ProfilePreview', this);
	},
	
	/**
	 * @see	WCF.Popover._loadContent()
	 */
	_loadContent: function() {
		var $element = $('#' + this._activeElementID);
		var $userID = $element.data('userID');
		
		if (this._userProfiles[$userID]) {
			// use cached user profile
			this._insertContent(this._activeElementID, this._userProfiles[$userID], true);
		}
		else {
			this._proxy.setOption('data', {
				actionName: 'getUserProfile',
				className: 'wcf\\data\\user\\UserProfileAction',
				objectIDs: [ $userID ]
			});
			
			var $elementID = this._activeElementID;
			var self = this;
			this._proxy.setOption('success', function(data, textStatus, jqXHR) {
				// cache user profile
				self._userProfiles[$userID] = data.returnValues.template;
				
				// show user profile
				self._insertContent($elementID, data.returnValues.template, true);
			});
			this._proxy.setOption('failure', function(data, jqXHR, textStatus, errorThrown) {
				// cache user profile
				self._userProfiles[$userID] = data.message;
				
				// show user profile
				self._insertContent($elementID, data.message, true);
				
				return false;
			});
			this._proxy.sendRequest();
		}
	},
	
	/**
	 * Purages a cached user profile.
	 * 
	 * @param	integer		userID
	 */
	purge: function(userID) {
		delete this._userProfiles[userID];
		
		// purge content cache
		this._data = { };
	}
});

/**
 * Initializes WCF.User.Action namespace.
 */
WCF.User.Action = {};

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Handles user follow and unfollow links.
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
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.unfollow')).children('.icon').removeClass('fa-plus').addClass('fa-minus');
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.unfollow'));
					}
					else {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.follow')).children('.icon').removeClass('fa-minus').addClass('fa-plus');
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
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.unignore')).children('.icon').removeClass('fa-ban').addClass('fa-circle-o');
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.unignore'));
					}
					else {
						button.attr('data-tooltip', WCF.Language.get('wcf.user.button.ignore')).children('.icon').removeClass('fa-circle-o').addClass('fa-ban');
						button.children('.invisible').text(WCF.Language.get('wcf.user.button.ignore'));
					}
					
					button.data('ignored', data.returnValues.isIgnoredUser);
					
					return false;
				}
			}, this));
			
			var $notification = new WCF.System.Notification();
			$notification.show();
			
			// force rebuilding of popover cache
			var self = this;
			WCF.System.ObjectStore.invoke('WCF.User.ProfilePreview', function (profilePreview) {
				profilePreview.purge(self._userID);
			});
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
 * Namespace for avatar functions.
 */
WCF.User.Avatar = {};

if (COMPILER_TARGET_DEFAULT) {
	/**
	 * Avatar upload function
	 *
	 * @see        WCF.Upload
	 */
	WCF.User.Avatar.Upload = WCF.Upload.extend({
		/**
		 * user id of avatar owner
		 * @var        integer
		 */
		_userID: 0,
		
		/**
		 * Initializes a new WCF.User.Avatar.Upload object.
		 *
		 * @param        integer                        userID
		 */
		init: function (userID) {
			this._super($('#avatarUpload > dd > div'), undefined, 'wcf\\data\\user\\avatar\\UserAvatarAction');
			this._userID = userID || 0;
			
			$('#avatarForm input[type=radio]').change(function () {
				if ($(this).val() == 'custom') {
					$('#avatarUpload > dd > div').show();
				}
				else {
					$('#avatarUpload > dd > div').hide();
				}
			});
			if (!$('#avatarForm input[type=radio][value=custom]:checked').length) {
				$('#avatarUpload > dd > div').hide();
			}
		},
		
		/**
		 * @see        WCF.Upload._initFile()
		 */
		_initFile: function (file) {
			return $('#avatarUpload > dt > img');
		},
		
		/**
		 * @see        WCF.Upload._success()
		 */
		_success: function (uploadID, data) {
			if (data.returnValues.url) {
				this._updateImage(data.returnValues.url);
				
				// hide error
				$('#avatarUpload > dd > .innerError').remove();
				
				// show success message
				var $notification = new WCF.System.Notification(WCF.Language.get('wcf.user.avatar.upload.success'));
				$notification.show();
			}
			else if (data.returnValues.errorType) {
				// show error
				this._getInnerErrorElement().text(WCF.Language.get('wcf.user.avatar.upload.error.' + data.returnValues.errorType));
			}
		},
		
		/**
		 * Updates the displayed avatar image.
		 *
		 * @param        string                url
		 */
		_updateImage: function (url) {
			$('#avatarUpload > dt > img').remove();
			var $image = $('<img src="' + url + '" class="userAvatarImage" alt="" />').css({
				'height': 'auto',
				'max-height': '96px',
				'max-width': '96px',
				'width': 'auto'
			});
			
			$('#avatarUpload > dt').prepend($image);
			
			WCF.DOMNodeInsertedHandler.execute();
		},
		
		/**
		 * Returns the inner error element.
		 *
		 * @return        jQuery
		 */
		_getInnerErrorElement: function () {
			var $span = $('#avatarUpload > dd > .innerError');
			if (!$span.length) {
				$span = $('<small class="innerError"></span>');
				$('#avatarUpload > dd').append($span);
			}
			
			return $span;
		},
		
		/**
		 * @see        WCF.Upload._getParameters()
		 */
		_getParameters: function () {
			return {
				userID: this._userID
			};
		}
	});
}
else {
	WCF.User.Avatar.Upload = WCF.Upload.extend({
		_userID: 0,
		init: function() {},
		_initFile: function() {},
		_success: function() {},
		_updateImage: function() {},
		_getInnerErrorElement: function() {},
		_getParameters: function() {},
		_name: "",
		_buttonSelector: {},
		_fileListSelector: {},
		_fileUpload: {},
		_className: "",
		_iframe: {},
		_internalFileID: 0,
		_options: {},
		_uploadMatrix: {},
		_supportsAJAXUpload: true,
		_overlay: {},
		_createButton: function() {},
		_insertButton: function() {},
		_removeButton: function() {},
		_upload: function() {},
		_createUploadMatrix: function() {},
		_error: function() {},
		_progress: function() {},
		_showOverlay: function() {},
		_evaluateResponse: function() {},
		_getFilename: function() {}
	});
}

/**
 * Generic implementation for grouped user lists.
 * 
 * @param	string		className
 * @param	string		dialogTitle
 * @param	object		additionalParameters
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
				$icon.removeClass('fa-bookmark-o').addClass('fa-bookmark');
				$button.data('isSubscribed', true);
			}
			else {
				if ($button.data('removeOnUnsubscribe')) {
					$button.parent().remove();
				}
				else {
					$icon.removeClass('fa-bookmark').addClass('fa-bookmark-o');
					$button.data('isSubscribed', false);
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

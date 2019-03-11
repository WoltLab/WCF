/**
 * Handles the user trophy dialog.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/User/Trophy/List
 */
define(['Ajax', 'Core', 'Dictionary', 'Dom/Util', 'Ui/Dialog', 'WoltLabSuite/Core/Ui/Pagination', 'Dom/ChangeListener', 'List'], function(Ajax, Core, Dictionary, DomUtil, UiDialog, UiPagination, DomChangeListener, List) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiUserTrophyList() { this.init(); }
	UiUserTrophyList.prototype = {
		/**
		 * Initializes the user trophy list.
		 */
		init: function() {
			this._cache = new Dictionary();
			this._knownElements = new List();
			
			this._options = {
				className: 'wcf\\data\\user\\trophy\\UserTrophyAction',
				parameters: {}
			};
			
			this._rebuild();
			
			DomChangeListener.add('WoltLabSuite/Core/Ui/User/Trophy/List', this._rebuild.bind(this));
		},
		
		/**
		 * Adds event userTrophyOverlayList elements.
		 */
		_rebuild: function() {
			elBySelAll('.userTrophyOverlayList', undefined, (function (element) {
				if (!this._knownElements.has(element)) {
					element.addEventListener(WCF_CLICK_EVENT, this._open.bind(this, elData(element, 'user-id')));
					
					this._knownElements.add(element);
				}
			}).bind(this));
		},
		
		/**
		 * Opens the user trophy list for a specific user.
		 *
		 * @param	{int}		userId
		 * @param       {Event}         event		event object
		 */
		_open: function(userId, event) {
			event.preventDefault();
			
			this._currentPageNo = 1;
			this._currentUser = userId;
			this._showPage();
		},
		
		/**
		 * Shows the current or given page.
		 *
		 * @param	{int=}		pageNo		page number
		 */
		_showPage: function(pageNo) {
			if (pageNo !== undefined) {
				this._currentPageNo = pageNo;
			}
			
			if (this._cache.has(this._currentUser)) {
				// validate pageNo
				if (this._cache.get(this._currentUser).get('pageCount') !== 0 && (this._currentPageNo < 1 || this._currentPageNo > this._cache.get(this._currentUser).get('pageCount'))) {
					throw new RangeError("pageNo must be between 1 and " + this._cache.get(this._currentUser).get('pageCount') + " (" + this._currentPageNo + " given).");
				}
			}
			else {
				// init user page cache
				this._cache.set(this._currentUser, new Dictionary());
			}
			
			if (this._cache.get(this._currentUser).has(this._currentPageNo)) {
				var dialog = UiDialog.open(this, this._cache.get(this._currentUser).get(this._currentPageNo));
				UiDialog.setTitle('userTrophyListOverlay', this._cache.get(this._currentUser).get('title'));
				
				if (this._cache.get(this._currentUser).get('pageCount') > 1) {
					var element = elBySel('.jsPagination', dialog.content);
					if (element !== null) {
						new UiPagination(element, {
							activePage: this._currentPageNo,
							maxPage: this._cache.get(this._currentUser).get('pageCount'),
							callbackSwitch: this._showPage.bind(this)
						});
					}
				}
			}
			else {
				this._options.parameters.pageNo = this._currentPageNo;
				this._options.parameters.userID = this._currentUser;
				
				Ajax.api(this, {
					parameters: this._options.parameters
				});
			}
		},
		
		_ajaxSuccess: function(data) {
			if (data.returnValues.pageCount !== undefined) {
				this._cache.get(this._currentUser).set('pageCount', ~~data.returnValues.pageCount);
			}
			
			this._cache.get(this._currentUser).set(this._currentPageNo, data.returnValues.template);
			this._cache.get(this._currentUser).set('title', data.returnValues.title);
			this._showPage();
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'getGroupedUserTrophyList',
					className: this._options.className
				}
			};
		},
		
		_dialogSetup: function() {
			return {
				id: 'userTrophyListOverlay',
				options: {
					title: ""
				},
				source: null
			};
		}
	};
	
	return UiUserTrophyList;
});

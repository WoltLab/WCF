/**
 * Object-based user list.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/User/List
 */
define(['Ajax', 'Core', 'Dictionary', 'Dom/Util', 'Ui/Dialog', 'WoltLab/WCF/Ui/Pagination'], function(Ajax, Core, Dictionary, DomUtil, UiDialog, UiPagination) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiUserList(options) { this.init(options); };
	UiUserList.prototype = {
		/**
		 * Initializes the user list.
		 * 
		 * @param	{object}	options		list of initialization options
		 */
		init: function(options) {
			this._cache = new Dictionary();
			this._pageCount = 0;
			this._pageNo = 1;
			
			this._options = Core.extend({
				className: '',
				dialogTitle: '',
				parameters: {}
			}, options);
		},
		
		/**
		 * Opens the user list.
		 */
		open: function() {
			this._pageNo = 1;
			this._showPage();
		},
		
		/**
		 * Shows the current or given page.
		 * 
		 * @param	{int=}		pageNo		page number
		 */
		_showPage: function(pageNo) {
			if (typeof pageNo === 'number') {
				this._pageNo = ~~pageNo;
			}
			
			if (this._pageCount !== 0 && (this._pageNo < 1 || this._pageNo > this._pageCount)) {
				throw new RangeError("pageNo must be between 1 and " + this._pageCount + " (" + this._pageNo + " given).");
			}
			
			if (this._cache.has(this._pageNo)) {
				var dialog = UiDialog.open(this, this._cache.get(this._pageNo));
				
				if (this._pageCount > 1) {
					var element = elBySel('.jsPagination', dialog.content);
					if (element !== null) {
						new UiPagination(element, {
							activePage: this._pageNo,
							maxPage: this._pageCount,
							
							callbackSwitch: this._showPage.bind(this)
						});
					}
				}
			}
			else {
				this._options.parameters.pageNo = this._pageNo;
				
				Ajax.api(this, {
					parameters: this._options.parameters
				});
			}
		},
		
		_ajaxSuccess: function(data) {
			if (data.returnValues.pageCount !== undefined) {
				this._pageCount = ~~data.returnValues.pageCount;
			}
			
			this._cache.set(this._pageNo, data.returnValues.template);
			this._showPage();
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					actionName: 'getGroupedUserList',
					className: this._options.className,
					interfaceName: 'wcf\\data\\IGroupedUserListAction'
				}
			};
		},
		
		_dialogSetup: function() {
			return {
				id: DomUtil.getUniqueId(),
				options: {
					title: this._options.dialogTitle
				},
				source: null
			};
		}
	};
	
	return UiUserList;
});

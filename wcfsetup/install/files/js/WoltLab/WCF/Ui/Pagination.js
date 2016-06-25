/**
 * Callback-based pagination.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Pagination
 */
define(['Core', 'Language', 'ObjectMap', 'StringUtil', 'WoltLab/WCF/Ui/Page/JumpTo'], function(Core, Language, ObjectMap, StringUtil, UiPageJumpTo) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function UiPagination(element, options) { this.init(element, options); }
	UiPagination.prototype = {
		/**
		 * maximum number of displayed page links, should match the PHP implementation
		 * @var	{int}
		 */
		SHOW_LINKS: 11,
		
		/**
		 * Initializes the pagination.
		 * 
		 * @param	{Element}	element		container element
		 * @param	{object}	options		list of initilization options
		 */
		init: function(element, options) {
			this._element = element;
			this._options = Core.extend({
				activePage: 1,
				maxPage: 1,
				
				callbackShouldSwitch: null,
				callbackSwitch: null
			}, options);
			
			if (typeof this._options.callbackShouldSwitch !== 'function') this._options.callbackShouldSwitch = null;
			if (typeof this._options.callbackSwitch !== 'function') this._options.callbackSwitch = null;
			
			this._element.classList.add('pagination');
			
			this._rebuild(this._element);
		},
		
		/**
		 * Rebuilds the entire pagination UI.
		 */
		_rebuild: function() {
			var hasHiddenPages = false;
			
			// clear content
			this._element.innerHTML = '';
			
			var list = elCreate('ul'), link;
			
			var listItem = elCreate('li');
			listItem.className = 'skip';
			list.appendChild(listItem);
			
			var iconClassNames = 'icon icon16 fa-chevron-left';
			if (this._options.activePage > 1) {
				link = elCreate('a');
				link.className = iconClassNames + ' jsTooltip';
				link.href = '#';
				link.title = Language.get('wcf.global.page.previous');
				listItem.appendChild(link);
				
				link.addEventListener(WCF_CLICK_EVENT, this.switchPage.bind(this, this._options.activePage - 1));
			}
			else {
				listItem.innerHTML = '<span class="' + iconClassNames + '"></span>';
				listItem.classList.add('disabled');
			}
			
			// add first page
			list.appendChild(this._createLink(1));
			
			// calculate page links
			var maxLinks = this.SHOW_LINKS - 4;
			var linksBefore = this._options.activePage - 2;
			if (linksBefore < 0) linksBefore = 0;
			var linksAfter = this._options.maxPage - (this._options.activePage + 1);
			if (linksAfter < 0) linksAfter = 0;
			if (this._options.activePage > 1 && this._options.activePage < this._options.maxPage) maxLinks--;
			
			var half = maxLinks / 2;
			var left = this._options.activePage;
			var right = this._options.activePage;
			if (left < 1) left = 1;
			if (right < 1) right = 1;
			if (right > this._options.maxPage - 1) right = this._options.maxPage - 1;
			
			if (linksBefore >= half) {
				left -= half;
			}
			else {
				left -= linksBefore;
				right += half - linksBefore;
			}
			
			if (linksAfter >= half) {
				right += half;
			}
			else {
				right += linksAfter;
				left -= half - linksAfter;
			}
			
			right = Math.ceil(right);
			left = Math.ceil(left);
			if (left < 1) left = 1;
			if (right > this._options.maxPage) right = this._options.maxPage;
			
			// left ... links
			var jumpToHtml = '<a class="jsTooltip" title="' + Language.get('wcf.global.page.jumpTo') + '">&hellip;</a>';
			if (left > 1) {
				if (left - 1 < 2) {
					list.appendChild(this._createLink(2));
				}
				else {
					listItem = elCreate('li');
					listItem.className = 'jumpTo';
					listItem.innerHTML = jumpToHtml;
					list.appendChild(listItem);
					
					hasHiddenPages = true;
				}
			}
			
			// visible links
			for (var i = left + 1; i < right; i++) {
				list.appendChild(this._createLink(i));
			}
			
			// right ... links
			if (right < this._options.maxPage) {
				if (this._options.maxPage - right < 2) {
					list.appendChild(this._createLink(this._options.maxPage - 1));
				}
				else {
					listItem = elCreate('li');
					listItem.className = 'jumpTo';
					listItem.innerHTML = jumpToHtml;
					list.appendChild(listItem);
					
					hasHiddenPages = true;
				}
			}
			
			// add last page
			list.appendChild(this._createLink(this._options.maxPage));
			
			// add next button
			listItem = elCreate('li');
			listItem.className = 'skip';
			list.appendChild(listItem);
			
			iconClassNames = 'icon icon16 fa-chevron-right';
			if (this._options.activePage < this._options.maxPage) {
				link = elCreate('a');
				link.className = iconClassNames + ' jsTooltip';
				link.href = '#';
				link.title = Language.get('wcf.global.page.next');
				listItem.appendChild(link);
				
				link.addEventListener(WCF_CLICK_EVENT, this.switchPage.bind(this, this._options.activePage + 1));
			}
			else {
				listItem.innerHTML = '<span class="' + iconClassNames + '"></span>';
				listItem.classList.add('disabled');
			}
			
			if (hasHiddenPages) {
				elData(list, 'pages', this._options.maxPage);
				
				UiPageJumpTo.init(list, this.switchPage.bind(this));
			}
			
			this._element.appendChild(list);
		},
		
		/**
		 * Creates a link to a specific page.
		 * 
		 * @param	{int}		pageNo		page number
		 * @return	{Element}	link element
		 */
		_createLink: function(pageNo) {
			var listItem = elCreate('li');
			if (pageNo !== this._options.activePage) {
				var link = elCreate('a');
				link.textContent = StringUtil.addThousandsSeparator(pageNo);
				link.addEventListener(WCF_CLICK_EVENT, this.switchPage.bind(this, pageNo));
				listItem.appendChild(link);
			}
			else {
				listItem.classList.add('active');
				listItem.innerHTML = '<span>' + StringUtil.addThousandsSeparator(pageNo) + '</span><span class="invisible">' + Language.get('wcf.page.pagePosition', { pageNo: pageNo, pages: this._options.maxPage }) + '</span>';
			}
			
			return listItem;
		},
		
		/**
		 * Switches to given page number.
		 * 
		 * @param	{int}		pageNo		page number
		 * @param	{object}	event		event object
		 */
		switchPage: function(pageNo, event) {
			if (typeof event === 'object') {
				event.preventDefault();
			}
			
			pageNo = ~~pageNo;
			
			if (pageNo > 0 && this._options.activePage !== pageNo && pageNo <= this._options.maxPage) {
				if (this._options.callbackShouldSwitch !== null) {
					if (this._options.callbackShouldSwitch(pageNo) !== true) {
						return;
					}
				}
				
				this._options.activePage = pageNo;
				this._rebuild();
				
				if (this._options.callbackSwitch !== null) {
					this._options.callbackSwitch(pageNo);
				}
			}
		}
	};
	
	return UiPagination;
});

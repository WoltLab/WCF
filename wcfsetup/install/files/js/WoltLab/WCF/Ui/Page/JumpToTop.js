/**
 * Provides a link to scroll to top once the page is scrolled by at least 50% the height of the window.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLab/WCF/Ui/Page/JumpToTop
 */
define(['Environment', 'Language', './Action'], function(Environment, Language, PageAction) {
	"use strict";
	
	/**
	 * @constructor
	 */
	function JumpToTop() { this.init(); }
	JumpToTop.prototype = {
		/**
		 * Initializes the top link for desktop browsers only.
		 */
		init: function() {
			// top link is not available on smartphones and tablets (they have a built-in function to accomplish this)
			if (Environment.platform() !== 'desktop') {
				return;
			}
			
			this._callbackScrollEnd = this._afterScroll.bind(this);
			this._timeoutScroll = null;
			
			var button = elCreate('a');
			button.className = 'jsTooltip';
			button.href = '#';
			elAttr(button, 'title', Language.get('wcf.global.scrollUp'));
			button.innerHTML = '<span class="icon icon16 fa-arrow-up"></span>';
			
			button.addEventListener(WCF_CLICK_EVENT, this._jump.bind(this));
			
			PageAction.add('toTop', button);
			
			window.addEventListener('scroll', this._scroll.bind(this));
			
			// invoke callback on page load
			this._afterScroll();
		},
		
		/**
		 * Handles clicks on the top link.
		 * 
		 * @param       {Event}         event   event object
		 * @protected
		 */
		_jump: function(event) {
			event.preventDefault();
			
			elById('top').scrollIntoView({ behavior: 'smooth' });
		},
		
		/**
		 * Callback executed whenever the window is being scrolled.
		 * 
		 * @protected
		 */
		_scroll: function() {
			if (this._timeoutScroll !== null) {
				window.clearTimeout(this._timeoutScroll);
			}
			
			this._timeoutScroll = window.setTimeout(this._callbackScrollEnd, 100);
		},
		
		/**
		 * Delayed callback executed once the page has not been scrolled for a certain amount of time.
		 * 
		 * @protected
		 */
		_afterScroll: function() {
			this._timeoutScroll = null;
			
			PageAction[(window.scrollY >= window.innerHeight / 2) ? 'show' : 'hide']('toTop');
		}
	};
	
	return JumpToTop;
});

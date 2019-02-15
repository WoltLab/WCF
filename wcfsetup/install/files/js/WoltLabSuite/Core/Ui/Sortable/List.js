/**
 * Sortable lists with optimized handling per device sizes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @module	WoltLabSuite/Core/Ui/Sortable/List
 */
define(['Core', 'Ui/Screen'], function (Core, UiScreen) {
	"use strict";
	
	if (!COMPILER_TARGET_DEFAULT) {
		var Fake = function() {};
		Fake.prototype = {
			init: function() {},
			_enable: function() {},
			_disable: function() {}
		};
		return Fake;
	}
	
	/**
	 * @constructor
	 */
	function UiSortableList(options) { this.init(options); }
	UiSortableList.prototype = {
		/**
		 * Initializes the sortable list controller.
		 * 
		 * @param       {Object}        options         initialization options for `WCF.Sortable.List`
		 */
		init: function (options) {
			this._options = Core.extend({
				containerId: '',
				className: '',
				offset: 0,
				options: {},
				isSimpleSorting: false,
				additionalParameters: {}
			}, options);
			
			UiScreen.on('screen-sm-md', {
				match: this._enable.bind(this, true),
				unmatch: this._disable.bind(this),
				setup: this._enable.bind(this, true)
			});
			
			UiScreen.on('screen-lg', {
				match: this._enable.bind(this, false),
				unmatch: this._disable.bind(this),
				setup: this._enable.bind(this, false)
			});
		},
		
		/**
		 * Enables sorting with an optional sort handle.
		 * 
		 * @param       {boolean}       hasHandle       true if sort can only be started with the sort handle
		 * @protected
		 */
		_enable: function (hasHandle) {
			var options = this._options.options;
			if (hasHandle) options.handle = '.sortableNodeHandle';
			
			new window.WCF.Sortable.List(
				this._options.containerId,
				this._options.className,
				this._options.offset,
				options,
				this._options.isSimpleSorting,
				this._options.additionalParameters
			);
		},
		
		/**
		 * Disables sorting for registered containers.
		 * 
		 * @protected
		 */
		_disable: function () {
			window.jQuery('#' + this._options.containerId + ' .sortableList')[(this._options.isSimpleSorting ? 'sortable' : 'nestedSortable')]('destroy');
		}
	};
	
	return UiSortableList;
});
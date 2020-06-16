"use strict";

/**
 * Like support for WCF
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * 
 * @deprecated	3.0 - please use `WoltLabSuite/Core/Ui/Reaction/Handler` instead
 */
WCF.Like = Class.extend({
	init: function() {
		throw new Error("The `WCF.Like` API is obsolete and therefore no longer supported. Please use the current API `WoltLabSuite/Core/Ui/Reaction/Handler` instead.");
	},
	_domNodeInserted: function() {},
	_initContainers: function() {},
	_getContainers: function() {},
	_getWidgetContainer: function() {},
	_getObjectID: function() {},
	_addWidget: function() {},
	_buildWidget: function() {},
	_createWidget: function() {},
	_showLikeDetails: function() {},
	_click: function() {},
	_sendRequest: function() {},
	_success: function() {},
	_updateBadge: function() {},
	_updateSummary: function() {},
	_setActiveState: function() {}
});

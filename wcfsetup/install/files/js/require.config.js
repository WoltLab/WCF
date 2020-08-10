//noinspection JSUnresolvedVariable
requirejs.config({
	paths: {
		enquire: '3rdParty/enquire',
		favico: '3rdParty/favico',
		'perfect-scrollbar': '3rdParty/perfect-scrollbar',
		'Pica': '3rdParty/pica',
		prism: '3rdParty/prism',
		zxcvbn: '3rdParty/zxcvbn',
	},
	shim: {
		enquire: { exports: 'enquire' },
		favico: { exports: 'Favico' },
		'perfect-scrollbar': { exports: 'PerfectScrollbar' }
	},
	map: {
		'*': {
			'Ajax': 'WoltLabSuite/Core/Ajax',
			'AjaxJsonp': 'WoltLabSuite/Core/Ajax/Jsonp',
			'AjaxRequest': 'WoltLabSuite/Core/Ajax/Request',
			'CallbackList': 'WoltLabSuite/Core/CallbackList',
			'ColorUtil': 'WoltLabSuite/Core/ColorUtil',
			'Core': 'WoltLabSuite/Core/Core',
			'DateUtil': 'WoltLabSuite/Core/Date/Util',
			'Devtools': 'WoltLabSuite/Core/Devtools',
			'Dictionary': 'WoltLabSuite/Core/Dictionary',
			'Dom/ChangeListener': 'WoltLabSuite/Core/Dom/Change/Listener',
			'Dom/Traverse': 'WoltLabSuite/Core/Dom/Traverse',
			'Dom/Util': 'WoltLabSuite/Core/Dom/Util',
			'Environment': 'WoltLabSuite/Core/Environment',
			'EventHandler': 'WoltLabSuite/Core/Event/Handler',
			'EventKey': 'WoltLabSuite/Core/Event/Key',
			'Language': 'WoltLabSuite/Core/Language',
			'List': 'WoltLabSuite/Core/List',
			'ObjectMap': 'WoltLabSuite/Core/ObjectMap',
			'Permission': 'WoltLabSuite/Core/Permission',
			'StringUtil': 'WoltLabSuite/Core/StringUtil',
			'Ui/Alignment': 'WoltLabSuite/Core/Ui/Alignment',
			'Ui/CloseOverlay': 'WoltLabSuite/Core/Ui/CloseOverlay',
			'Ui/Confirmation': 'WoltLabSuite/Core/Ui/Confirmation',
			'Ui/Dialog': 'WoltLabSuite/Core/Ui/Dialog',
			'Ui/Notification': 'WoltLabSuite/Core/Ui/Notification',
			'Ui/ReusableDropdown': 'WoltLabSuite/Core/Ui/Dropdown/Reusable',
			'Ui/Screen': 'WoltLabSuite/Core/Ui/Screen',
			'Ui/Scroll': 'WoltLabSuite/Core/Ui/Scroll',
			'Ui/SimpleDropdown': 'WoltLabSuite/Core/Ui/Dropdown/Simple',
			'Ui/TabMenu': 'WoltLabSuite/Core/Ui/TabMenu',
			'Upload': 'WoltLabSuite/Core/Upload',
			'User': 'WoltLabSuite/Core/User'
		}
	},
	waitSeconds: 0
});

/* Define jQuery shim. We cannot use the shim object in the configuration above,
   because it tries to load the file, even if the exported global already exists.
   This shim is needed for jQuery plugins supporting an AMD loaded jQuery, because
   we break the AMD support of jQuery for BC reasons.
*/
define('jquery', function() {
	return window.jQuery;
});


requirejs.config({
	paths: {
		enquire: '3rdParty/enquire',
		favico: '3rdParty/favico',
		'perfect-scrollbar': '3rdParty/perfect-scrollbar'
	},
	shim: {
		enquire: { exports: 'enquire' },
		favico: { exports: 'Favico' },
		'perfect-scrollbar': { exports: 'PerfectScrollbar' }
	},
	map: {
		'*': {
			'Ajax': 'WoltLab/WCF/Ajax',
			'AjaxJsonp': 'WoltLab/WCF/Ajax/Jsonp',
			'AjaxRequest': 'WoltLab/WCF/Ajax/Request',
			'CallbackList': 'WoltLab/WCF/CallbackList',
			'Core': 'WoltLab/WCF/Core',
			'DateUtil': 'WoltLab/WCF/Date/Util',
			'Dictionary': 'WoltLab/WCF/Dictionary',
			'Dom/ChangeListener': 'WoltLab/WCF/Dom/Change/Listener',
			'Dom/Traverse': 'WoltLab/WCF/Dom/Traverse',
			'Dom/Util': 'WoltLab/WCF/Dom/Util',
			'Environment': 'WoltLab/WCF/Environment',
			'EventHandler': 'WoltLab/WCF/Event/Handler',
			'Language': 'WoltLab/WCF/Language',
			'List': 'WoltLab/WCF/List',
			'ObjectMap': 'WoltLab/WCF/ObjectMap',
			'Permission': 'WoltLab/WCF/Permission',
			'StringUtil': 'WoltLab/WCF/StringUtil',
			'Ui/Alignment': 'WoltLab/WCF/Ui/Alignment',
			'Ui/CloseOverlay': 'WoltLab/WCF/Ui/CloseOverlay',
			'Ui/Confirmation': 'WoltLab/WCF/Ui/Confirmation',
			'Ui/Dialog': 'WoltLab/WCF/Ui/Dialog',
			'Ui/Notification': 'WoltLab/WCF/Ui/Notification',
			'Ui/ReusableDropdown': 'WoltLab/WCF/Ui/Dropdown/Reusable',
			'Ui/Screen': 'WoltLab/WCF/Ui/Screen',
			'Ui/SimpleDropdown': 'WoltLab/WCF/Ui/Dropdown/Simple',
			'Ui/TabMenu': 'WoltLab/WCF/Ui/TabMenu',
			'Upload': 'WoltLab/WCF/Upload'
		}
	}
});

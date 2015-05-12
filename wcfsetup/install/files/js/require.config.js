requirejs.config({
	paths: {
		enquire: '3rdParty/enquire',
		favico: '3rdParty/favico'
	},
	map: {
		'*': {
			'CallbackList': 'WoltLab/WCF/CallbackList',
			'Core': 'WoltLab/WCF/Core',
			'Dictionary': 'WoltLab/WCF/Dictionary',
			'DOM/Traverse': 'WoltLab/WCF/DOM/Traverse',
			'DOM/Util': 'WoltLab/WCF/DOM/Util',
			'EventHandler': 'WoltLab/WCF/Event/Handler',
			'UI/Alignment': 'WoltLab/WCF/UI/Alignment',
			'UI/Dialog': 'WoltLab/WCF/UI/Dialog',
			'UI/SimpleDropdown': 'WoltLab/WCF/UI/Dropdown/Simple',
			'UI/TabMenu': 'WoltLab/WCF/UI/TabMenu'
		}
	}
});

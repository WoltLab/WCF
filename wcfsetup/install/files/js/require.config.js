//noinspection JSUnresolvedVariable
requirejs.config({
	paths: {
		"focus-trap": "3rdParty/focus-trap/focus-trap.umd.min",
		"perfect-scrollbar": "3rdParty/perfect-scrollbar",
		Pica: "3rdParty/pica",
		pica: "3rdParty/pica",
		prism: "3rdParty/prism",
		prismjs: "3rdParty/prism/prism",
		"qr-creator": "3rdParty/qr-creator.min",
		"reflect-metadata": "3rdParty/reflect-metadata",
		tabbable: "3rdParty/focus-trap/index.umd.min",
		tslib: "3rdParty/tslib",
		zxcvbn: "3rdParty/zxcvbn",
		"@googlemaps/markerclusterer": "3rdParty/googlemaps/markerclusterer/index.umd",
		"@ckeditor/ckeditor5-inspector": "3rdParty/ckeditor/ckeditor5-inspector/inspector",
		"@woltlab/editor": "3rdParty/ckeditor/ckeditor5.bundle",
		"ckeditor5-translation": "3rdParty/ckeditor/translations",
		"diff-match-patch": "3rdParty/diff-match-patch/diff_match_patch.min",
		"emoji-picker-element": "3rdParty/emoji-picker-element.min",
	},
	packages: [
		{
			name: "codemirror",
			location: "3rdParty/codemirror",
			main: "lib/codemirror",
		},
		{
			name: "@woltlab/visual-dom-diff",
			location: "3rdParty/visual-dom-diff",
			main: "index",
		},
	],
	shim: {
		"perfect-scrollbar": { exports: "PerfectScrollbar" },
		"qr-creator": { exports: "QrCreator" },
	},
	map: {
		"*": {
			Ajax: "WoltLabSuite/Core/Ajax",
			AjaxJsonp: "WoltLabSuite/Core/Ajax/Jsonp",
			AjaxRequest: "WoltLabSuite/Core/Ajax/Request",
			CallbackList: "WoltLabSuite/Core/CallbackList",
			ColorUtil: "WoltLabSuite/Core/ColorUtil",
			Core: "WoltLabSuite/Core/Core",
			DateUtil: "WoltLabSuite/Core/Date/Util",
			Devtools: "WoltLabSuite/Core/Devtools",
			Dictionary: "WoltLabSuite/Core/Dictionary",
			"Dom/ChangeListener": "WoltLabSuite/Core/Dom/Change/Listener",
			"Dom/Traverse": "WoltLabSuite/Core/Dom/Traverse",
			"Dom/Util": "WoltLabSuite/Core/Dom/Util",
			Environment: "WoltLabSuite/Core/Environment",
			EventHandler: "WoltLabSuite/Core/Event/Handler",
			EventKey: "WoltLabSuite/Core/Event/Key",
			Language: "WoltLabSuite/Core/Language",
			List: "WoltLabSuite/Core/List",
			ObjectMap: "WoltLabSuite/Core/ObjectMap",
			Permission: "WoltLabSuite/Core/Permission",
			StringUtil: "WoltLabSuite/Core/StringUtil",
			"Ui/Alignment": "WoltLabSuite/Core/Ui/Alignment",
			"Ui/CloseOverlay": "WoltLabSuite/Core/Ui/CloseOverlay",
			"Ui/Confirmation": "WoltLabSuite/Core/Ui/Confirmation",
			"Ui/Dialog": "WoltLabSuite/Core/Ui/Dialog",
			"Ui/Notification": "WoltLabSuite/Core/Ui/Notification",
			"Ui/ReusableDropdown": "WoltLabSuite/Core/Ui/Dropdown/Reusable",
			"Ui/Screen": "WoltLabSuite/Core/Ui/Screen",
			"Ui/Scroll": "WoltLabSuite/Core/Ui/Scroll",
			"Ui/SimpleDropdown": "WoltLabSuite/Core/Ui/Dropdown/Simple",
			"Ui/TabMenu": "WoltLabSuite/Core/Ui/TabMenu",
			Upload: "WoltLabSuite/Core/Upload",
			User: "WoltLabSuite/Core/User",
		},
	},
	waitSeconds: 0,
});

/* Define jQuery shim. We cannot use the shim object in the configuration above,
   because it tries to load the file, even if the exported global already exists.
   This shim is needed for jQuery plugins supporting an AMD loaded jQuery, because
   we break the AMD support of jQuery for BC reasons.
*/
define('jquery', function() {
	return window.jQuery;
});


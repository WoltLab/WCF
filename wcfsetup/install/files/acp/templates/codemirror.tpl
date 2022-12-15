{event name='javascriptIncludes'}

<script data-relocate="true">
	require([
		'codemirror',
		{if $codemirrorMode|isset}
			{if $codemirrorMode === 'smartymixed'}
				'codemirror/mode/smarty/smarty',
			{elseif $codemirrorMode === 'text/x-less'}
				{* deprecated, legacy support *}
				'codemirror/mode/css/css',
			{elseif $codemirrorMode === 'text/x-scss'}
				'codemirror/mode/css/css',
			{else}
				'codemirror/mode/{@$codemirrorMode}/{@$codemirrorMode}',
			{/if}
		{/if}
		'codemirror/addon/search/search',
		'EventHandler',
		'Dom/Traverse',
		'Dom/Util'
	], (
		CodeMirror,
		{if $codemirrorMode|isset}
			CodeMirrorMode,
		{/if}
		CodeMirrorSearchAddon,
		EventHandler,
		DomTraverse,
		DomUtil,
	) => {
		const isDarkMode = window.getComputedStyle(document.documentElement).colorScheme === "dark";

		const codemirrorCss = document.head.querySelector('link[href$="codemirror.css"]');
		if (codemirrorCss === null) {
			function addStylesheet(name) {
				const link = document.createElement('link');
				link.rel = 'stylesheet';
				link.href = `{@$__wcf->getPath()}js/3rdParty/codemirror/${ name }.css`;
				document.head.append(link);
			}

			addStylesheet("codemirror");
			addStylesheet("addon/dialog/dialog");

			if (isDarkMode) {
				addStylesheet("theme/material-darker");
			}
		}
		
		var config = {
			{if $codemirrorMode|isset}
				{if $codemirrorMode === 'smartymixed'}
					mode: {
						name: 'smarty',
						baseMode: 'text/html',
						version: 3
					},
				{else}
					mode: '{@$codemirrorMode|encodeJS}',
				{/if}
			{/if}
			lineWrapping: true,
			indentWithTabs: true,
			lineNumbers: true,
			indentUnit: 4,
			readOnly: {if !$editable|isset || $editable}false{else}true{/if}
		};

		if (isDarkMode) {
			config.theme = "material-darker";
		}
		
		document.querySelectorAll('{@$codemirrorSelector|encodeJS}').forEach((element) => {
			{event name='javascriptInit'}
			
			if (element.codemirror) {
				for (var key in config) {
					if (config.hasOwnProperty(key)) {
						element.codemirror.setOption(key, config[key]);
					}
				}
			}
			else {
				element.codemirror = CodeMirror.fromTextArea(element, config);
				var oldToTextArea = element.codemirror.toTextArea;
				element.codemirror.toTextArea = function () {
					oldToTextArea();
					element.codemirror = null;
				};
			}
			
			setTimeout(function () {
				element.codemirror.refresh();
			}, 250);
			setTimeout(function () {
				element.codemirror.refresh();
			}, 1000);
			
			var tab = DomTraverse.parentByClass(element, 'tabMenuContent');
			if (tab !== null) {
				var name = elData(tab, 'name');
				var tabMenu = DomTraverse.parentByClass(tab, 'tabMenuContainer');
				var scrollPosition = null;
				
				EventHandler.add('com.woltlab.wcf.simpleTabMenu_' + DomUtil.identify(tabMenu), 'select', function(data) {
					if (data.activeName === name) {
						element.codemirror.refresh();
						if (scrollPosition !== null) element.codemirror.scrollTo(null, scrollPosition);
					}
				});
				
				EventHandler.add('com.woltlab.wcf.simpleTabMenu_' + DomUtil.identify(tabMenu), 'beforeSelect', function(data) {
					if (data.tabName === name) {
						scrollPosition = element.codemirror.getScrollInfo().top;
					}
				});

				const parentTabMenu = tabMenu.closest(".tabMenuContainer")
				if (parentTabMenu) {
					EventHandler.add("com.woltlab.wcf.simpleTabMenu_" + parentTabMenu.id, "select", (data) => {
						if (data.activeName === tabMenu.dataset.name) {
							element.codemirror.refresh();
						}
					});
				}
			}
			
			var scrollOffsetStorage = element;
			do {
				scrollOffsetStorage = scrollOffsetStorage.nextElementSibling;
			} while (scrollOffsetStorage && !scrollOffsetStorage.classList.contains('codeMirrorScrollOffset'));
			if (scrollOffsetStorage) {
				const offset = scrollOffsetStorage.value
				element.codemirror.on('scroll', function (cm) {
					scrollOffsetStorage.value = cm.getScrollInfo().top;
				});

				// Delay the scrolling to the next event cycle to allow
				// CodeMirror to fully initialize itself.
				setTimeout(() => {
					element.codemirror.scrollTo(null, offset)
				}, 0);
			}
		});
	});
</script>

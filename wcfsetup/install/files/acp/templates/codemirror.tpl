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
		'EventHandler',
		'Dom/Traverse',
		'Dom/Util'
	], (
		CodeMirror,
		{if $codemirrorMode|isset}
			CoreMirrorMode,
		{/if}
		EventHandler,
		DomTraverse,
		DomUtil,
	) => {
		const codemirrorCss = document.head.querySelector('link[href$="codemirror.css"]');
		if (codemirrorCss === null) {
			const link = document.createElement('link');
			link.rel = 'stylesheet';
			link.href = '{@$__wcf->getPath()}js/3rdParty/codemirror/codemirror.css';
			document.head.appendChild(link);
		}
		
		var elements = document.querySelectorAll('{@$codemirrorSelector|encodeJS}');
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
		
		[].forEach.call(elements, function (element) {
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
			}
			
			var scrollOffsetStorage = element;
			do {
				scrollOffsetStorage = scrollOffsetStorage.nextElementSibling;
			} while (scrollOffsetStorage && !scrollOffsetStorage.classList.contains('codeMirrorScrollOffset'));
			if (scrollOffsetStorage) {
				element.codemirror.scrollTo(null, scrollOffsetStorage.value);
				element.codemirror.on('scroll', function (cm) {
					scrollOffsetStorage.value = cm.getScrollInfo().top;
				});
			}
		});
	});
</script>

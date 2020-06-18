{if !$codemirrorLoaded|isset}
	<script data-relocate="true">window.define.amd = undefined;</script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/codemirror.js"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/addon/dialog/dialog.js"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/addon/search/searchcursor.js"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/addon/search/search.js"></script>
	<script data-relocate="true">window.define.amd = window.__require_define_amd;</script>
{/if}
{if $codemirrorMode|isset}
	<script data-relocate="true">window.define.amd = undefined;</script>
	{if $codemirrorMode != 'smartymixed'}
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/mode/{if $codemirrorMode == 'text/x-less'}css/css{else}{$codemirrorMode}/{$codemirrorMode}{/if}.js"></script>
	{/if}
	
	{if $codemirrorMode == 'htmlmixed' || $codemirrorMode == 'smartymixed' || $codemirrorMode == 'php'}
		{if $codemirrorMode == 'smartymixed'}
			<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/mode/htmlmixed/htmlmixed.js"></script>
			<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/mode/smarty/smarty.js"></script>
		{elseif $codemirrorMode == 'php'}
			<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/mode/htmlmixed/htmlmixed.js"></script>
			<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/mode/clike/clike.js"></script>
		{/if}
		
		<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/mode/css/css.js"></script>
		<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/mode/javascript/javascript.js"></script>
		<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/mode/xml/xml.js"></script>
	{/if}
	
	<script data-relocate="true">window.define.amd = window.__require_define_amd;</script>
{/if}
{event name='javascriptIncludes'}

<script data-relocate="true">
	{if !$codemirrorLoaded|isset}
		['{@$__wcf->getPath()}js/3rdParty/codemirror/codemirror.css', '{@$__wcf->getPath()}js/3rdParty/codemirror/addon/dialog/dialog.css'].forEach(function(href) {
			var link = document.createElement('link');
			link.rel = 'stylesheet';
			link.href = href;
			document.head.appendChild(link);
		});
	{/if}
	
	require(['EventHandler', 'Dom/Traverse', 'Dom/Util'], function(EventHandler, DomTraverse, DomUtil) {
		var elements = document.querySelectorAll('{@$codemirrorSelector|encodeJS}');
		var config = {
			{if $codemirrorMode|isset}
				{if $codemirrorMode == 'smartymixed'}
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
{assign var='codemirrorLoaded' value=true}

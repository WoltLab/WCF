{if !$codemirrorLoaded|isset}
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/codemirror.js"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/addon/dialog/dialog.js"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/addon/search/searchcursor.js"></script>
	<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/addon/search/search.js"></script>
{/if}
{if $codemirrorMode|isset}<script data-relocate="true" src="{@$__wcf->getPath()}js/3rdParty/codemirror/mode/{if $codemirrorMode == 'text/x-less'}css/css{else}{$codemirrorMode}/{$codemirrorMode}{/if}.js"></script>{/if}
{event name='javascriptIncludes'}

<script data-relocate="true">
//<![CDATA[
	{if !$codemirrorLoaded|isset}
		$('<link rel="stylesheet" href="{@$__wcf->getPath()}js/3rdParty/codemirror/codemirror.css" />').appendTo('head');
		$('<link rel="stylesheet" href="{@$__wcf->getPath()}js/3rdParty/codemirror/addon/dialog/dialog.css" />').appendTo('head');
	{/if}
	
	$(function() {
		var $elements = $('{@$codemirrorSelector|encodeJS}');
		var $config = {
			{if $codemirrorMode|isset}mode: '{@$codemirrorMode|encodeJS}',{/if}
			lineWrapping: true,
			indentWithTabs: true,
			lineNumbers: true,
			indentUnit: 4
		};
		
		for (var $i = 0; $i < $elements.length; $i++) {
			(function () {
				var $element = $elements[$i];
				
				{event name='javascriptInit'}
				
				if ($element.codemirror) {
					for (var name in $config) {
						if (!$config.hasOwnProperty($name)) continue;
						
						$element.codemirror.setOption($name, $config[$name]);
					}
				}
				else {
					$element.codemirror = CodeMirror.fromTextArea($element, $config);
					var oldToTextArea = $element.codemirror.toTextArea;
					$element.codemirror.toTextArea = function() {
						oldToTextArea();
						$element.codemirror = null;
					};
				}
				
				setTimeout(function () {
					$element.codemirror.refresh();
				}, 250);
				setTimeout(function () {
					$element.codemirror.refresh();
				}, 1000);
			})();
		}
	});
//]]>
</script>
{assign var='codemirrorLoaded' value=true}

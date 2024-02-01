<div class="codeBox collapsibleBbcode jsCollapsibleBbcode {if $lines > 10} collapsed{/if}">
	<div class="codeBoxHeader">
		<div class="codeBoxHeadline">{$title}{if $filename}: {$filename}{/if}</div>
		
		{if $lines > 10}
			<span class="toggleButton jsTooltip pointer" title="{lang}wcf.bbcode.button.showAll{/lang}" data-title-collapse="{lang}wcf.bbcode.button.collapse{/lang}" data-title-expand="{lang}wcf.bbcode.button.showAll{/lang}" role="button" tabindex="0">
				{icon size=24 name='up-right-and-down-left-from-center'}
			</span>
		{/if}
	</div>
	
	{assign var='lineNumber' value=$startLineNumber}
	<pre class="codeBoxCode collapsibleBbcodeOverflow"><code{if $language} class="language-{$language}"{/if}>{foreach from=$content item=line}{*
		*}<span class="codeBoxLine" id="#codeLine_{$lineNumber}_{$codeID}"><a href="#codeLine_{$lineNumber}_{$codeID|rawurlencode}" class="lineAnchor" title="{@$lineNumber}" tabindex="-1" aria-hidden="true"></a><span>{$line}</span></span>{*
		*}{assign var='lineNumber' value=$lineNumber+1}{*
	*}{/foreach}</code></pre>
	
	{if $lines > 10}
		<span class="toggleButton" data-title-collapse="{lang}wcf.bbcode.button.collapse{/lang}" data-title-expand="{lang}wcf.bbcode.button.showAll{/lang}">{lang}wcf.bbcode.button.showAll{/lang}</span>
	{/if}
</div>
<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Bbcode/Collapsible', 'WoltLabSuite/Core/Bbcode/Code'], function (Language, BbcodeCollapsible, BbcodeCode) {
		Language.addObject({
			'wcf.message.bbcode.code.copy': '{jslang}wcf.message.bbcode.code.copy{/jslang}',
			'wcf.message.bbcode.code.copy.success': '{jslang}wcf.message.bbcode.code.copy.success{/jslang}'
		});
		BbcodeCollapsible.observe();
		BbcodeCode.processAll();
	});
</script>

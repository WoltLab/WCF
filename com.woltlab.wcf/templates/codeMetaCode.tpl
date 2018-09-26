<div class="codeBox collapsibleBbcode jsCollapsibleBbcode {if $lines > 10} collapsed{/if}">
	<div>
		<div class="codeBoxHeader">
			<div class="codeBoxHeadline">{$title}{if $filename}: {$filename}{/if}</div>
		</div>
		
		{assign var='lineNumber' value=$startLineNumber}
		<pre class="codeBoxCode"><code{if $language} class="language-{$language}"{/if}>{foreach from=$content item=line}{*
			*}{assign var='codeLineID' value='codeLine_'|concat:$lineNumber:'_':$codeID}{*
			*}<div class="codeBoxLine" id="{$codeLineID}"><a href="{@$__wcf->getAnchor($codeLineID)}" class="lineAnchor" title="{@$lineNumber}"></a><span>{$line}</span></div>{*
			*}{assign var='lineNumber' value=$lineNumber+1}{*
		*}{/foreach}</code></pre>
	</div>
	{if $lines > 10}
		<span class="toggleButton" data-title-collapse="{lang}wcf.bbcode.button.collapse{/lang}" data-title-expand="{lang}wcf.bbcode.button.showAll{/lang}">{lang}wcf.bbcode.button.showAll{/lang}</span>
	{/if}
</div>
{if !$__wcfCodeBBCodeJavaScript|isset}
	{assign var='__wcfCodeBBCodeJavaScript' value=true}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Bbcode/Collapsible', 'WoltLabSuite/Core/Bbcode/Code'], function (BbcodeCollapsible, BbcodeCode) {
			BbcodeCollapsible.observe();
			BbcodeCode.highlightAll();
		});
	</script>
{/if}

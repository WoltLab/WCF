<div class="codeBox collapsibleBbcode jsCollapsibleBbcode {$highlighter|get_class|substr:30|lcfirst}{if $lines > 10} collapsed{/if}">
	<div>
		<div class="codeBoxHeader">
			<div class="codeBoxHeadline">{@$highlighter->getTitle()}{if $filename}: {$filename}{/if}</div>
		</div>
		
		<ol start="{$startLineNumber}">
			{assign var='lineNumber' value=$startLineNumber}
			{foreach from=$content item=line}
				{if $lineNumbers[$lineNumber]|isset}
					<li id="{@$lineNumbers[$lineNumber]}"><a href="{@$__wcf->getAnchor($lineNumbers[$lineNumber])}" class="lineAnchor"></a>{@$line}</li>
				{else}
					<li>{@$line}</li>
				{/if}
				
				{assign var='lineNumber' value=$lineNumber+1}
			{/foreach}
		</ol>
	</div>
	
	{if $lines > 10}
		<span class="toggleButton" data-title-collapse="{lang}wcf.bbcode.button.collapse{/lang}" data-title-expand="{lang}wcf.bbcode.button.showAll{/lang}">{lang}wcf.bbcode.button.showAll{/lang}</span>
		
		{if !$__overlongCodeBoxSeen|isset}
			{assign var='__overlongCodeBoxSeen' value=true}
			<script data-relocate="true">
				require(['WoltLab/WCF/Bbcode/Collapsible'], function(BbcodeCollapsible) {
					BbcodeCollapsible.observe();
				});
			</script>
		{/if}
	{/if}
</div>

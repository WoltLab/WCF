<div class="codeBox collapsibleBbcode jsCollapsibleBbcode {$highlighter|get_class|substr:30|lcfirst}{if $lines > 10} collapsed{/if}">
	<div>
		<div class="codeBoxHeader">
			<div class="codeBoxHeadline">{@$highlighter->getTitle()}{if $filename}: {$filename}{/if}</div>
		</div>
		
		<ol start="{$startLineNumber}">
			{assign var='lineNumber' value=$startLineNumber}
			{foreach from=$content item=line}
				{if $lineNumbers[$lineNumber]|isset}
					<li><span id="{@$lineNumbers[$lineNumber]}" class="codeBoxJumpAnchor"></span><a href="{@$__wcf->getAnchor($lineNumbers[$lineNumber])}" class="lineAnchor"></a><span>{@$line}</span></li>
				{else}
					<li><span>{@$line}</span></li>
				{/if}
				
				{assign var='lineNumber' value=$lineNumber+1}
			{/foreach}
		</ol>
	</div>
	
	{if $lines > 10}
		<span class="toggleButton" data-title-collapse="{lang}wcf.bbcode.button.collapse{/lang}" data-title-expand="{lang}wcf.bbcode.button.showAll{/lang}">{lang}wcf.bbcode.button.showAll{/lang}</span>
		
		{if !$__overlongBBCodeBoxSeen|isset}
			{assign var='__overlongBBCodeBoxSeen' value=true}
			<script data-relocate="true">
				require(['WoltLabSuite/Core/Bbcode/Collapsible'], function(BbcodeCollapsible) {
					BbcodeCollapsible.observe();
				});
			</script>
		{/if}
	{/if}
</div>

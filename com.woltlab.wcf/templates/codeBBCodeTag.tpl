<div class="container codeBox {$highlighter|get_class|substr:30|lcfirst}{if $lines > 10} minimized{/if}">
	<div>
		<div>
			<h3>{@$highlighter->getTitle()}{if $filename}: {$filename}{/if}</h3>
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
		<span class="codeBoxExpand jsButtonCodeBoxExpand">{lang}wcf.bbcode.button.showAll{/lang}</span>
		<script data-relocate="true">
			$(function() {
				$('.jsButtonCodeBoxExpand').removeClass('jsButtonCodeBoxExpand').click(function() {
					$(this).parent().removeClass('minimized').end().remove();
				});
			});
		</script>
	{/if}
</div>

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
		
		{if !$__overlongCodeBoxSeen|isset}
			{assign var='__overlongCodeBoxSeen' value=true}
			<script data-relocate="true">
				$(function() {
					$('.jsButtonCodeBoxExpand').removeClass('jsButtonCodeBoxExpand').click(function() {
						$(this).parent().removeClass('minimized').end().remove();
					});
					
					// searching in a page causes Google Chrome to scroll
					// the code box if something inside it matches
					// 
					// expand the box in this case, to:
					// a) Improve UX
					// b) Hide an ugly misplaced "show all" button
					$('.codeBox').on('scroll', function() {
						$(this).find('.codeBoxExpand').click();
					});
					
					// expand code boxes that are initially scrolled this
					// may happen due to someone linking to a specific line
					$('.codeBox').each(function() {
						if ($(this).scrollTop() != 0) {
							$(this).find('.codeBoxExpand').click();
						}
					});
				});
			</script>
		{/if}
	{/if}
</div>

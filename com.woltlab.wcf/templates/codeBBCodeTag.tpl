<div class="container codeBox {$highlighter|get_class|substr:30|lcfirst}">
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
</div>

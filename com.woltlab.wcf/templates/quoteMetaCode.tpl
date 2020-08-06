<blockquote class="quoteBox collapsibleBbcode jsCollapsibleBbcode{if $collapseQuote} collapsed{/if}{if !$quoteAuthorObject} quoteBoxSimple{/if}"{if $quoteLink} cite="{$quoteLink}"{/if}>
	<div class="quoteBoxIcon">
		{if $quoteAuthorObject}
			<a href="{$quoteAuthorObject->getLink()}" class="userLink" data-object-id="{@$quoteAuthorObject->userID}" aria-hidden="true">{@$quoteAuthorObject->getAvatar()->getImageTag(64)}</a>
		{else}
			<span class="quoteBoxQuoteSymbol"></span>
		{/if}
	</div>
	
	<div class="quoteBoxTitle">
		<span class="quoteBoxTitle">
			{if $quoteAuthor}
				{if $quoteLink}
					<a {anchorAttributes url=$quoteLink isUgc=true}>{lang}wcf.bbcode.quote.title{/lang}</a>
				{else}
					{lang}wcf.bbcode.quote.title{/lang}
				{/if}
			{else}
				{lang}wcf.bbcode.quote{/lang}
			{/if}
		</span>
	</div>
	
	<div class="quoteBoxContent">
		<!-- META_CODE_INNER_CONTENT -->
	</div>
	
	{if $collapseQuote}
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
</blockquote>

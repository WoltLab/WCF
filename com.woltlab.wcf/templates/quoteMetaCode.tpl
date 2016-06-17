<blockquote class="quoteBox{if !$quoteAuthorObject} quoteBoxSimple{/if}"{if $quoteLink} cite="{$quoteLink}"{/if}>
	<div class="quoteBoxIcon">
		{if $quoteAuthorObject}
			<a href="{link controller='User' object=$quoteAuthorObject}{/link}" class="userLink" data-user-id="{@$quoteAuthorObject->userID}">{@$quoteAuthorObject->getAvatar()->getImageTag(64)}</a>
		{else}
			<span class="quoteBoxQuoteSymbol"></span>
		{/if}
	</div>
	
	<div class="quoteBoxTitle">
		<span class="quoteBoxTitle">
			{if $quoteAuthor}
				{if $quoteLink}
					<a href="{@$quoteLink}"{if $isExternalQuoteLink} class="externalURL"{if EXTERNAL_LINK_REL_NOFOLLOW} rel="nofollow"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}{/if}>{lang}wcf.bbcode.quote.title{/lang}</a>
				{else}
					{lang}wcf.bbcode.quote.title{/lang}
				{/if}
			{else}
				{lang}wcf.bbcode.quote{/lang}
			{/if}
		</span>
	</div>
	
	<div>
		<!-- META_CODE_INNER_CONTENT -->
	</div>
</blockquote>

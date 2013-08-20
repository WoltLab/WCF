<blockquote class="container containerPadding quoteBox"{if $quoteLink} cite="{$quoteLink}"{/if}>
	{if $quoteAuthor}
		<header>
			<h3>
				{if $quoteLink}
					<a href="{@$quoteLink}"{if $isExternalQuoteLink} class="externalURL"{if EXTERNAL_LINK_REL_NOFOLLOW} rel="nofollow"{/if}{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}{/if}>{lang}wcf.bbcode.quote.title{/lang}</a>
				{else}
					{lang}wcf.bbcode.quote.title{/lang}
				{/if}
			</h3>
		</header>
	{/if}
	
	<div>
		{@$content}
	</div>
</blockquote>
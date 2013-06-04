<blockquote class="container containerPadding quoteBox"{if $quoteLink} cite="{$quoteLink}"{/if}>
	{if $quoteAuthor}
		<header>
			<h3>
				{if $quoteLink}
					<a href="{@$quoteLink}">{lang}wcf.bbcode.quote.title{/lang}</a>
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
{if $boxPosition == 'sidebarLeft' || $boxPosition == 'sidebarRight'}
	<ul class="sidebarItemList">
		{foreach from=$boxArticleList item=boxArticle}
			<li>
				<a href="{$boxArticle->getLink()}" class="box64">
					<span>{if $boxArticle->getTeaserImage()}{@$boxArticle->getTeaserImage()->getElementTag(64)}{/if}</span>
					
					<div>
						<h3>{$boxArticle->getTitle()}</h3>
						<small>
							{if $boxSortField == 'time'}
								{@$boxArticle->time|time}
							{elseif $boxSortField == 'views'}
								{lang article=$boxArticle}wcf.article.articleViews{/lang}
							{elseif $boxSortField == 'comments'}
								{$boxArticle->getDiscussionProvider()->getDiscussionCountPhrase()}
							{/if}
						</small>
					</div>
				</a>
			</li>
		{/foreach}
	</ul>
{elseif $boxPosition == 'footerBoxes'}
	<ul class="articleList">
		{foreach from=$boxArticleList item=boxArticle}
			<li>
				<a href="{$boxArticle->getLink()}">
					{if $boxArticle->getTeaserImage() && $boxArticle->getTeaserImage()->hasThumbnail('small')}
						<div class="articleListImage">{@$boxArticle->getTeaserImage()->getThumbnailTag('small')}</div>
					{/if}
					
					<h3 class="articleListTitle">{$boxArticle->getTitle()}</h3>
					<ul class="inlineList articleListMetaData">
						<li>
							<span class="icon icon16 fa-clock-o"></span>
							{@$boxArticle->time|time}
						</li>
						
						<li>
							<span class="icon icon16 fa-comments"></span>
							{$boxArticle->getDiscussionProvider()->getDiscussionCountPhrase()}
						</li>
					</ul>
				</a>
			</li>
		{/foreach}
	</ul>
{else}
	{include file='articleListItems' objects=$boxArticleList disableAds=true}
{/if}

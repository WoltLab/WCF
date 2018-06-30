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
							{elseif $boxSortField == 'cumulativeLikes'}
								{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && ($boxArticle->likes || $boxArticle->dislikes)}
									<span class="wcfLikeCounter{if $boxArticle->cumulativeLikes > 0} likeCounterLiked{elseif $boxArticle->cumulativeLikes < 0}likeCounterDisliked{/if}">
										<span class="icon icon16 fa-thumbs-o-{if $boxArticle->cumulativeLikes < 0}down{else}up{/if} jsTooltip" title="{lang likes=$boxArticle->likes dislikes=$boxArticle->dislikes}wcf.like.tooltip{/lang}"></span>{if $boxArticle->cumulativeLikes > 0}+{elseif $boxArticle->cumulativeLikes == 0}&plusmn;{/if}{#$boxArticle->cumulativeLikes}
									</span>
								{/if}
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
	{include file='articleListItems' objects=$boxArticleList}
{/if}

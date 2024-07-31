{if $boxPosition == 'sidebarLeft' || $boxPosition == 'sidebarRight'}
	<ul class="sidebarItemList">
		{foreach from=$boxArticleList item=boxArticle}
			<li class="box24 sidebarItem">
				<a href="{$boxArticle->getLink()}" aria-hidden="true" tabindex="-1">{unsafe:$boxArticle->getUserProfile()->getAvatar()->getImageTag(24)}</a>
				
				<div class="sidebarItemTitle">
					<h3><a href="{$boxArticle->getLink()}">{$boxArticle->getTitle()}</a></h3>
					
					<small>
						{if $boxSortField == 'time'}
							{user object=$boxArticle->getUserProfile() tabindex='-1'}
							<span class="separatorLeft">{time time=$boxArticle->time}</span>
						{elseif $boxSortField == 'views'}
							{lang article=$boxArticle}wcf.article.articleViews{/lang}
						{elseif $boxSortField == 'comments'}
							{$boxArticle->getDiscussionProvider()->getDiscussionCountPhrase()}
						{elseif $boxSortField == 'cumulativeLikes'}
							{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $boxArticle->cachedReactions}
								{include file='shared_topReaction' cachedReactions=$boxArticle->cachedReactions render='full'}
							{/if}
						{/if}
					</small>
				</div>
			</li>
		{/foreach}
	</ul>
{elseif $boxPosition == 'footerBoxes'}
	<ul class="articleList">
		{foreach from=$boxArticleList item=boxArticle}
			<li>
				<a href="{$boxArticle->getLink()}">
					{if $boxArticle->getTeaserImage() && $boxArticle->getTeaserImage()->hasThumbnail('small')}
						<div class="articleListImage">{unsafe:$boxArticle->getTeaserImage()->getThumbnailTag('small')}</div>
					{else}
						<div class="articleListImage">
							<img src="{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoURL()}" alt=""
								style="height: {$__wcf->getStyleHandler()->getStyle()->getCoverPhotoHeight()}px; width: {$__wcf->getStyleHandler()->getStyle()->getCoverPhotoWidth()}px">
						</div>
					{/if}
					
					<h3 class="articleListTitle">{$boxArticle->getTitle()}</h3>
					<ul class="inlineList articleListMetaData">
						<li>
							{icon name='clock'}
							{time time=$boxArticle->time}
						</li>
						
						<li>
							{icon name='comments'}
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

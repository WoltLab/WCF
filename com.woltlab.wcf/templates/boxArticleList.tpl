{if $boxPosition == 'sidebarLeft' || $boxPosition == 'sidebarRight'}
	<ul class="sidebarList">
		{foreach from=$boxArticleList item=boxArticle}
			<li class="sidebarListItem">
				<div class="sidebarListItem__avatar">
					{user object=$boxArticle->getUserProfile() type='avatar32' ariaHidden='true' tabindex='-1'}
				</div>
				
				<div class="sidebarListItem__content">
					<h3 class="sidebarListItem__title">
						{anchor object=$boxArticle class='articleLink sidebarListItem__link' title=$boxArticle->getTitle()}
					</h3>
				</div>

				<div class="sidebarListItem__meta">
					{if $boxSortField == 'time'}
						<div class="sidebarListItem__meta__author">
							{user object=$boxArticle->getUserProfile() tabindex='-1'}
						</div>
						<div class="sidebarListItem__meta__time">
							{time time=$boxArticle->time}
						</div>
					{elseif $boxSortField == 'views'}
						<div class="sidebarListItem__meta__views">
							{lang article=$boxArticle}wcf.article.articleViews{/lang}
						</div>
					{elseif $boxSortField == 'comments'}
						<div class="sidebarListItem__meta__comments">
							{$boxArticle->getDiscussionProvider()->getDiscussionCountPhrase()}
						</div>
					{elseif $boxSortField == 'cumulativeLikes'}
						<div class="sidebarListItem__meta__reactions">
							{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $boxArticle->cachedReactions}
								{include file='shared_topReaction' cachedReactions=$boxArticle->cachedReactions render='full'}
							{/if}
						</div>
					{/if}
				</div>
			</li>
		{/foreach}
	</ul>
{else}
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
{/if}
